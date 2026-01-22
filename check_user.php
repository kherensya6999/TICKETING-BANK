<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('username', 'superadmin')->first();

if ($user) {
    echo "User found: " . $user->username . "\n";
    $user->password = \Illuminate\Support\Facades\Hash::make('password');
    $user->save();
    echo "Password updated to 'password'\n";
} else {
    echo "User 'superadmin' not found.\n";
}
