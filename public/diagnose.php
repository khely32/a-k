<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function diag(string $label, callable $fn): void
{
    echo "<b>[diag] {$label}:</b><br>";
    try {
        $out = $fn();
        echo empty($out) || is_null($out) ? 'ok' : (is_scalar($out) ? (string) $out : json_encode($out));
    } catch (Throwable $e) {
        echo 'EXCEPTION ' . get_class($e) . ': ' . e($e->getMessage()) . '<br><pre>' . e($e->getTraceAsString()) . '</pre>';
    }
    echo '<br><hr>';
}

$conn = config('database.connections.pgsql');
diag('config', function () use ($conn) {
    $conn['password'] = $conn['password'] ? '***' : '';
    return $conn;
});

diag('env DB_HOST', fn () => getenv('DB_HOST') . ' port=' . getenv('DB_PORT') . ' db=' . getenv('DB_DATABASE'));

diag('pdo connect + version', function () {
    return \Illuminate\Support\Facades\DB::selectOne('select version()')->version;
});

diag('users count', fn () => \App\Models\User::count());

diag('cache write/read', function () {
    \Illuminate\Support\Facades\Cache::put('diag', 'works', 10);
    return \Illuminate\Support\Facades\Cache::get('diag');
});

diag('session table write', function () {
    \Illuminate\Support\Facades\DB::table('sessions')->insert([
        'id' => 'diag-session-' . uniqid(), 'user_id' => null, 'ip_address' => '127.0.0.1',
        'user_agent' => 'diag', 'payload' => base64_encode('diag'), 'last_activity' => time(),
    ]);
    return 'inserted';
});

diag('auth attempt', function () {
    $ok = \Illuminate\Support\Facades\Auth::attempt(['email' => 'admin', 'password' => 'admin123456789']);
    return $ok ? 'authenticated' : 'AUTH FAILED';
});

$logFile = storage_path('logs/laravel.log');
echo "<h3>POST /login via HTTP kernel (real CSRF)</h3><pre>";
try {
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    session_start();

    // GET to seed CSRF token into session
    $get = Illuminate\Http\Request::create('http://localhost/login', 'GET');
    $kernel->handle($get);

    $token = csrf_token();
    $post = Illuminate\Http\Request::create('http://localhost/login', 'POST', [
        '_token' => $token, 'email' => 'admin', 'password' => 'admin123456789',
    ]);
    $post->setSession(app('session.store'));
    $res = $kernel->handle($post);
    echo 'STATUS: ' . $res->getStatusCode() . "\n";
    if ($res->getStatusCode() === 302) {
        echo 'LOCATION: ' . $res->headers->get('Location') . "\n";
    }
} catch (Throwable $e) {
    echo 'EXCEPTION: ' . get_class($e) . "\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
echo '</pre>';

echo "<h3>laravel.log (last 120 lines)</h3><pre>";
if (file_exists($logFile)) {
    $lines = array_slice(file($logFile), -120);
    echo e(implode('', $lines));
} else {
    echo 'no log file';
}
echo '</pre>';

echo "<br>done<br>";