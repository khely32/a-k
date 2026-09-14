<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$compiler = app('blade.compiler');
foreach (['edit','create','index'] as $v) {
    $path = __DIR__."/resources/views/products/{$v}.blade.php";
    try {
        $compiler->compileString(file_get_contents($path));
        echo "OK  products/{$v}.blade.php\n";
    } catch (\Throwable $e) {
        echo "FAIL products/{$v}.blade.php: ".$e->getMessage()."\n";
    }
}
// also top-level templates
try {
    View::getFinder()->flush();
    $app->make(Illuminate\View\Compilers\BladeCompiler::class)->getCompiledPath('products/edit');
    echo "getCompiledPath OK\n";
} catch (\Throwable $e) {
    echo "COMPILE MISS: ".$e->getMessage()."\n";
}
echo "DONE\n";
