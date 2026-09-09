<?php

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;

$_ENV['APP_DEBUG'] = 'true';
putenv('APP_DEBUG=true');
$_ENV['APP_ENV'] = 'local';
putenv('APP_ENV=local');

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(HttpKernel::class);

try {
    $response = $kernel->handle(
        $request = Request::create('/login', 'GET')
    );
    echo '[diag] /login status=' . $response->getStatusCode() . PHP_EOL;
    $body = $response->getContent();
    echo '[diag] ===== BODY (first 8000 chars) =====' . PHP_EOL;
    echo substr($body, 0, 8000) . PHP_EOL;
    echo '[diag] ===== END =====' . PHP_EOL;
    $kernel->terminate($request, $response);
} catch (Throwable $e) {
    echo '[diag] EXCEPTION: ' . get_class($e) . PHP_EOL;
    echo '[diag] MESSAGE: ' . $e->getMessage() . PHP_EOL;
    echo '[diag] FILE: ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
    echo '[diag] TRACE:' . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}