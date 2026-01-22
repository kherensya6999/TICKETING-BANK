<?php

use App\Models\User;
use App\Models\UserSession;
use App\Services\AuthService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "1. Checking user_sessions table...\n";
if (Schema::hasTable('user_sessions')) {
    echo "   [OK] Table 'user_sessions' exists.\n";
} else {
    echo "   [FAIL] Table 'user_sessions' DOES NOT exist.\n";
    exit(1);
}

echo "\n2. Generating Token for superadmin...\n";
$user = User::where('username', 'superadmin')->first();
if (!$user) {
    echo "   [FAIL] User 'superadmin' not found.\n";
    exit(1);
}

$authService = new AuthService();
$token = $authService->generateToken($user);
$length = strlen($token);
echo "   Token Length: $length characters\n";

if ($length > 500) {
    echo "   [WARNING] Token length exceeds 500 characters! This will cause DB insertion failure.\n";
} else {
    echo "   [OK] Token length is safe (< 500).\n";
}

echo "\n3. Testing Session Insertion...\n";
try {
    // Delete existing sessions for clean test
    UserSession::where('user_id', $user->id)->delete();
    
    $session = UserSession::create([
        'user_id' => $user->id,
        'session_token' => $token,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'DebugScript',
        'expires_at' => now()->addDay(),
        'last_activity_at' => now(),
    ]);
    echo "   [OK] Session created successfully. ID: " . $session->id . "\n";
} catch (\Exception $e) {
    echo "   [FAIL] Session creation failed: " . $e->getMessage() . "\n";
}

echo "\n4. Verifying Session Retrieval (Simulation of JWTAuth)...\n";
$retrieved = UserSession::where('session_token', $token)->first();
if ($retrieved) {
    echo "   [OK] Session retrieved successfully.\n";
} else {
    echo "   [FAIL] Session could not be retrieved.\n";
}
