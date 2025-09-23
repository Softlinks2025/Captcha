<?php
// Test JWT token generation within Laravel with detailed error reporting

// Load Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;
use Tymon\JWTAuth\Manager;
use Tymon\JWTAuth\Http\Parser\Parser;
use Tymon\JWTAuth\Claims\Factory as ClaimFactory;
use Tymon\JWTAuth\Validators\PayloadValidator;
use App\Models\User;

// Function to display output in a readable format
function display($message, $data = null) {
    echo "\n=== " . $message . " ===\n";
    if ($data !== null) {
        if (is_string($data)) {
            echo $data . "\n";
        } else {
            print_r($data);
        }
    }
    echo "\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "LARAVEL JWT DEEP TEST\n";
echo str_repeat("=", 80) . "\n\n";

try {
    // 1. Check JWT configuration
    $jwtConfig = config('jwt');
    
    // Hide sensitive data
    if (isset($jwtConfig['secret'])) {
        $jwtConfig['secret'] = substr($jwtConfig['secret'], 0, 5) . '...';
    }
    
    display("JWT Configuration", $jwtConfig);
    
    // 2. Get a test user
    $user = User::first();
    
    if (!$user) {
        die("No users found in the database. Please create a user first.\n");
    }
    
    display("Test User", [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'phone' => $user->phone,
        'is_verified' => $user->is_verified,
        'phone_verified_at' => $user->phone_verified_at
    ]);
    
    // 3. Test JWT token generation using different methods
    $methods = [
        'JWTAuth::fromUser()' => function() use ($user) {
            return JWTAuth::fromUser($user);
        },
        'auth(\'api\')->login()' => function() use ($user) {
            return auth('api')->login($user);
        },
        'JWTAuth::customClaims()->fromUser()' => function() use ($user) {
            return JWTAuth::customClaims(['test' => 'value'])->fromUser($user);
        },
        'JWT::encode()' => function() use ($user) {
            $payload = [
                'sub' => $user->getJWTIdentifier(),
                'iat' => time(),
                'exp' => time() + 3600,
                'test' => 'value'
            ];
            return JWTAuth::encode($payload)->get();
        }
    ];
    
    foreach ($methods as $method => $callback) {
        display("Testing Token Generation: $method");
        
        try {
            $start = microtime(true);
            $token = $callback();
            $time = round((microtime(true) - $start) * 1000, 2) . 'ms';
            
            if ($token) {
                display("✓ Success ($time)", [
                    'token' => substr($token, 0, 20) . '...',
                    'length' => strlen($token),
                    'time' => $time
                ]);
                
                // Test token verification
                try {
                    $payload = JWTAuth::setToken($token)->getPayload();
                    display("  ✓ Token verified", [
                        'subject' => $payload->get('sub'),
                        'expires_at' => date('Y-m-d H:i:s', $payload->get('exp')),
                        'issued_at' => date('Y-m-d H:i:s', $payload->get('iat')),
                        'custom_claims' => $payload->get('test', 'none')
                    ]);
                } catch (Exception $e) {
                    display("  ✗ Token verification failed", $e->getMessage());
                }
            } else {
                display("✗ Failed ($time)", 'No token was returned');
            }
        } catch (Exception $e) {
            display("✗ Error", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }
    
    // 4. Test JWT Manager directly
    display("Testing JWT Manager Directly");
    
    try {
        $manager = app('tymon.jwt.manager');
        $payloadFactory = app('tymon.jwt.payload.factory');
        
        $customClaims = ['test' => 'direct'];
        $payload = $payloadFactory->make(array_merge(
            ['sub' => $user->getJWTIdentifier()],
            $customClaims
        ));
        
        $token = $manager->encode($payload)->get();
        
        display("✓ JWT Manager Token Generated", [
            'token' => substr($token, 0, 20) . '...',
            'length' => strlen($token)
        ]);
        
    } catch (Exception $e) {
        display("✗ JWT Manager Error", [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
    
    // 5. Check JWT Provider
    display("Checking JWT Provider");
    
    try {
        $provider = app('tymon.jwt.provider.jwt');
        $reflection = new ReflectionClass($provider);
        $secret = $reflection->getProperty('secret');
        $secret->setAccessible(true);
        $secretValue = $secret->getValue($provider);
        
        display("JWT Provider Configuration", [
            'class' => get_class($provider),
            'secret' => $secretValue ? substr($secretValue, 0, 5) . '...' : 'Not set',
            'algo' => $provider->getAlgo(),
            'keys' => array_keys($provider->getKeys())
        ]);
        
    } catch (Exception $e) {
        display("✗ JWT Provider Error", $e->getMessage());
    }
    
    // 6. Test JWT Payload Factory
    display("Testing JWT Payload Factory");
    
    try {
        $factory = app('tymon.jwt.payload.factory');
        $payload = $factory->make(['sub' => $user->getJWTIdentifier()]);
        
        display("✓ Payload Created", [
            'subject' => $payload->get('sub'),
            'expires' => date('Y-m-d H:i:s', $payload->get('exp')),
            'issued_at' => date('Y-m-d H:i:s', $payload->get('iat')),
            'jti' => $payload->get('jti')
        ]);
        
    } catch (Exception $e) {
        display("✗ Payload Factory Error", [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
    
} catch (Exception $e) {
    display("✗ An error occurred", [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}

display("Test completed", "Please review the results above for any issues.");
echo "\n" . str_repeat("=", 80) . "\n\n";
