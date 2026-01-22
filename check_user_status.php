<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('username', 'superadmin')->first();

if ($user) {
    echo "User Found:\n";
    echo "ID: " . $user->id . "\n";
    echo "Username: " . $user->username . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Password Hash: " . substr($user->password, 0, 10) . "...\n";
    
    if (str_starts_with($user->password, '$2y$')) {
        echo "Password Status: HASHED (Correct)\n";
    } else {
        echo "Password Status: PLAINTEXT (Incorrect)\n";
    }
} else {
    echo "User 'superadmin' NOT FOUND.\n";
}
