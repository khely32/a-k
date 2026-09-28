<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PruneDatabase extends Command
{
    protected $signature = 'maintenance:prune
                            {--cache-hours=24 : Delete cache rows that expired more than this many hours ago}
                            {--sessions-hours=48 : Delete session rows inactive for longer than this}';

    protected $description = 'Delete expired cache rows and stale sessions (keeps the database from filling up on a free-tier plan)';

    public function handle(): int
    {
        $cacheCutoff = time() - ((int) $this->option('cache-hours') * 3600);
        $sessionCutoff = time() - ((int) $this->option('sessions-hours') * 3600);

        $freed = 0;

        // The database cache store never deletes expired rows on its own, and
        // the database session driver never deletes inactive rows. On a
        // free-tier database with a hard storage cap that eventually fills the
        // disk, and writes start failing.
        if (Schema::hasTable('cache')) {
            $deleted = DB::table('cache')->where('expiration', '<', $cacheCutoff)->delete();
            $freed += $deleted;
            $this->line("cache:    removed {$deleted} expired row(s)");
        }

        if (Schema::hasTable('sessions')) {
            $deleted = DB::table('sessions')->where('last_activity', '<', $sessionCutoff)->delete();
            $freed += $deleted;
            $this->line("sessions: removed {$deleted} stale row(s)");
        }

        if (Schema::hasTable('failed_jobs')) {
            $deleted = DB::table('failed_jobs')->where('failed_at', '<', date('Y-m-d H:i:s', $cacheCutoff))->delete();
            $freed += $deleted;
            $this->line("failed_jobs: removed {$deleted} old row(s)");
        }

        $this->info("Pruned {$freed} row(s) in total.");

        return Command::SUCCESS;
    }
}
