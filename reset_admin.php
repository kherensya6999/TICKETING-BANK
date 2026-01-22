<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$user = User::where('username', 'superadmin')->first();
if ($user) {
    $user->password = Hash::make('password');
    $user->is_active = true;
    $user->failed_login_attempts = 0;
    $user->locked_until = null;
    $user->save();
    echo "Password for 'superadmin' reset to 'password'. User activated.\n";
} else {
    echo "User 'superadmin' not found.\n";
}
