<?php

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "--- ROLES ---\n";
$roles = Role::all();
foreach ($roles as $role) {
    echo "ID: {$role->id}, Name: {$role->role_name}\n";
}

echo "\n--- USERS ---\n";
$users = User::with('role')->get();
foreach ($users as $user) {
    echo "ID: {$user->id}, Username: {$user->username}, Role: " . ($user->role ? $user->role->role_name : 'No Role') . " (Role ID: {$user->role_id})\n";
}
