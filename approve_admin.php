<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::table('users')
    ->where('email', 'admin@filrouge.com')
    ->update(['status' => 'approved']);

echo "✅ Admin approuvé avec succès!\n";
