<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class HealthController extends Controller
{
    /**
     * Tables the app cannot boot without. Reported individually so a failed
     * migration loop is distinguishable from a database that is simply down.
     */
    private const REQUIRED_TABLES = [
        'migrations',
        'users',
        'branches',
        'products',
        'inventories',
        'sales',
        'sale_items',
        'stock_transfers',
        'sessions',
    ];

    /**
     * Registered OUTSIDE the "web" middleware group, so this endpoint has no
     * session, no CSRF and no database middleware. That is the whole point:
     * while the database is down, every page in the "web" group returns 500,
     * and this endpoint still answers so the cause is visible.
     *
     * Returns 200 when healthy, 503 when a dependency is broken.
     */
    public function check(): JsonResponse
    {
        $checks = [];
        $healthy = true;

        // A malformed APP_KEY (anything but a base64-encoded 32-byte value)
        // makes every request that touches encryption fail. Checked by actually
        // round-tripping the encrypter rather than parsing the string.
        try {
            $probe = 'healthz';
            if (app('encrypter')->decrypt(app('encrypter')->encrypt($probe)) !== $probe) {
                throw new \RuntimeException('encrypt/decrypt round-trip failed');
            }
            $checks['app_key'] = 'ok';
        } catch (Throwable $e) {
            $checks['app_key'] = 'INVALID: ' . $e->getMessage();
            $healthy = false;
        }

        $databaseUp = false;

        try {
            DB::connection()->getPdo();
            $databaseUp = true;
            $checks['database'] = 'connected (' . DB::connection()->getDriverName() . ')';
        } catch (Throwable $e) {
            $checks['database'] = 'UNREACHABLE: ' . $e->getMessage();
            $healthy = false;
        }

        if ($databaseUp) {
            $missing = array_values(array_filter(
                self::REQUIRED_TABLES,
                fn (string $table) => !Schema::hasTable($table)
            ));

            if ($missing !== []) {
                $checks['tables'] = 'MISSING: ' . implode(', ', $missing) . ' (run php artisan migrate)';
                $healthy = false;
            } else {
                $checks['tables'] = 'ok';
            }

            try {
                $migrator = app('migrator');

                if (! $migrator->repositoryExists()) {
                    $checks['migrations'] = 'NEVER RUN (migrations table missing)';
                    $healthy = false;
                } else {
                    $files   = $migrator->getMigrationFiles($migrator->paths());
                    $ran     = $migrator->getRepository()->getRan();
                    $pending = array_diff(array_keys($files), $ran);

                    if ($pending === []) {
                        $checks['migrations'] = 'up to date (' . count($ran) . ' applied)';
                    } else {
                        $checks['migrations'] = count($pending) . ' PENDING: ' . implode(', ', array_slice($pending, 0, 5));
                        $healthy = false;
                    }
                }
            } catch (Throwable $e) {
                $checks['migrations'] = 'UNKNOWN: ' . $e->getMessage();
            }

            // Forensics for the duplicate-cleanup rollout: whether the merge
            // migration reached the deployed filesystem, and what state the
            // products/merge-log tables are in - so "migrated but catalog not
            // shrunk" is distinguishable from "migration never shipped".
            $mergeMigration = database_path('migrations/2026_10_09_000001_merge_live_duplicate_products.php');
            $checks['migration_file'] = 'present';
            if (! file_exists($mergeMigration)) {
                $checks['migration_file'] = 'MISSING';
                $healthy = false;
            }

            if (Schema::hasTable('products')) {
                $checks['products'] = 'count ' . DB::table('products')->count();
            }

            if (Schema::hasTable('product_merge_logs')) {
                $checks['merge_logs'] = 'count ' . DB::table('product_merge_logs')->count();
            }

            // A full database is still perfectly reachable, so nothing else
            // detects it: every write simply starts failing and the app 500s
            // with a confusing "no space left on device". Compare against the
            // provider's cap so this failure mode is reported explicitly.
            $limit = (int) env('DB_STORAGE_LIMIT_BYTES', 0);

            if ($limit > 0) {
                $size = (int) DB::selectOne('SELECT pg_database_size(current_database()) AS s')->s;
                $usedMb  = round($size / 1048576, 1);
                $limitMb = round($limit / 1048576, 1);

                if ($size >= $limit) {
                    $checks['storage'] = "FULL: {$usedMb} MB used of {$limitMb} MB limit - writes are failing, "
                        . 'run maintenance:prune';
                    $healthy = false;
                } elseif ($size >= $limit * 0.9) {
                    $checks['storage'] = "WARNING: {$usedMb} MB used of {$limitMb} MB limit (over 90%)";
                } else {
                    $checks['storage'] = "{$usedMb} MB used of {$limitMb} MB limit";
                }
            }
        }

        return response()->json([
            'status'   => $healthy ? 'ok' : 'unhealthy',
            'app'      => config('app.name'),
            'env'      => app()->environment(),
            'commit'   => env('RENDER_GIT_COMMIT') ?: 'unknown',
            'checks'   => $checks,
            'time'     => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    }
}
