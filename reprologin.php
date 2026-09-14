<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$uri = 'http://localhost/login';
try {
    $req = Illuminate\Http\Request::create($uri, 'POST', [
        '_token' => 'x',
        'email' => 'admin',
        'password' => 'admin123456789',
    ]);
    $res = $kernel->handle($req);
    echo 'STATUS: ' . $res->getStatusCode() . "\n";
} catch (Throwable $e) {
    echo 'EXCEPTION: ' . get_class($e) . "\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}