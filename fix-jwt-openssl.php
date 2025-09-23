<?php

/**
 * JWT and OpenSSL Configuration Fix Script
 * 
 * This script will:
 * 1. Check and generate JWT_SECRET if not set
 * 2. Fix OpenSSL configuration issues
 * 3. Verify JWT token generation
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/storage/logs/php-errors.log');

// Check if running from CLI
$isCli = (php_sapi_name() === 'cli');

// Function to output messages
function message($msg, $type = 'info') {
    global $isCli;
    if ($isCli) {
        $colors = [
            'success' => "\033[0;32m",
            'error' => "\033[0;31m",
            'warning' => "\033[1;33m",
            'info' => "\033[0;36m",
            'reset' => "\033[0m"
        ];
        echo "[{$colors[$type]}{$type}{$colors['reset']}] {$msg}\n";
    } else {
        $colors = [
            'success' => 'color: green;',
            'error' => 'color: red;',
            'warning' => 'color: orange;',
            'info' => 'color: blue;',
            'reset' => 'color: black;'
        ];
        echo "<p style='{$colors[$type]}'>{$msg}</p>";
    }
}

// Function to update .env file
function updateEnv($key, $value) {
    $envFile = __DIR__ . '/.env';
    $envContent = file_exists($envFile) ? file_get_contents($envFile) : '';
    
    // If key exists, update it, otherwise append it
    if (preg_match("/^{$key}=/m", $envContent)) {
        $envContent = preg_replace(
            "/^{$key}=.*/m",
            "{$key}={$value}",
            $envContent
        );
    } else {
        $envContent .= "\n{$key}={$value}\n";
    }
    
    // Write back to .env file
    if (file_put_contents($envFile, $envContent) !== false) {
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        return true;
    }
    
    return false;
}

// Main function
function main() {
    message("Starting JWT and OpenSSL Configuration Fix Script...", 'info');
    
    // 1. Check PHP version
    $phpVersion = phpversion();
    message("PHP Version: {$phpVersion}", 'info');
    
    // 2. Check OpenSSL extension
    $opensslLoaded = extension_loaded('openssl');
    $opensslVersion = $opensslLoaded ? OPENSSL_VERSION_TEXT : 'Not available';
    message("OpenSSL Loaded: " . ($opensslLoaded ? 'Yes' : 'No'), 
           $opensslLoaded ? 'success' : 'error');
    message("OpenSSL Version: {$opensslVersion}", 'info');
    
    if (!$opensslLoaded) {
        message("OpenSSL extension is required but not loaded. Please enable it in your php.ini file.", 'error');
        return;
    }
    
    // 3. Check if we can use OpenSSL functions
    $testData = 'test data';
    $method = 'AES-256-CBC';
    $key = 'test-key-12345678';
    $iv = '1234567890123456';
    
    $canEncrypt = function_exists('openssl_encrypt');
    $canDecrypt = function_exists('openssl_decrypt');
    
    if ($canEncrypt) {
        $encrypted = @openssl_encrypt($testData, $method, $key, 0, $iv);
        $canEncrypt = ($encrypted !== false);
        
        if ($canEncrypt && $canDecrypt) {
            $decrypted = @openssl_decrypt($encrypted, $method, $key, 0, $iv);
            $canDecrypt = ($decrypted === $testData);
        }
    }
    
    message("Can Encrypt: " . ($canEncrypt ? 'Yes' : 'No'), $canEncrypt ? 'success' : 'error');
    message("Can Decrypt: " . ($canDecrypt ? 'Yes' : 'No'), $canDecrypt ? 'success' : 'error');
    
    if (!$canEncrypt || !$canDecrypt) {
        message("OpenSSL functions are not working properly. Please check your PHP configuration.", 'error');
        return;
    }
    
    // 4. Check JWT_SECRET
    $jwtSecret = getenv('JWT_SECRET');
    $hasJwtSecret = !empty($jwtSecret);
    
    if (!$hasJwtSecret) {
        message("JWT_SECRET is not set. Generating a new one...", 'warning');
        $newSecret = bin2hex(random_bytes(32));
        
        if (updateEnv('JWT_SECRET', $newSecret)) {
            message("JWT_SECRET has been generated and added to .env file", 'success');
            $jwtSecret = $newSecret;
            $hasJwtSecret = true;
        } else {
            message("Failed to update .env file. Please add this line manually:\nJWT_SECRET={$newSecret}", 'error');
            return;
        }
    } else {
        message("JWT_SECRET is already set", 'success');
    }
    
    // 5. Test JWT token generation
    if ($hasJwtSecret) {
        message("Testing JWT token generation...", 'info');
        
        try {
            $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
            $payload = json_encode(['sub' => 1, 'iat' => time(), 'exp' => time() + 3600]);
            
            $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
            $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
            
            $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $jwtSecret, true);
            $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
            
            $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
            
            message("JWT Token Generated: " . substr($jwt, 0, 30) . "...", 'success');
            
            // Verify the token
            list($header, $payload, $signature) = explode('.', $jwt);
            $valid = hash_hmac('sha256', "$header.$payload", $jwtSecret, true);
            $decodedSignature = base64_decode(str_replace(['-', '_'], ['+', '/'], $signature));
            $tokenVerified = hash_equals($valid, $decodedSignature);
            
            message("JWT Token Verified: " . ($tokenVerified ? 'Yes' : 'No'), 
                   $tokenVerified ? 'success' : 'error');
            
            if (!$tokenVerified) {
                message("JWT token verification failed. Please check your JWT_SECRET and OpenSSL configuration.", 'error');
            }
        } catch (Exception $e) {
            message("JWT Generation Failed: " . $e->getMessage(), 'error');
        }
    }
    
    // 6. Check ForceJsonResponse middleware
    $middlewareFile = __DIR__ . '/app/Http/Middleware/ForceJsonResponse.php';
    if (!file_exists($middlewareFile)) {
        message("ForceJsonResponse middleware not found. Creating...", 'warning');
        
        // Create directory if it doesn't exist
        if (!is_dir(dirname($middlewareFile))) {
            mkdir(dirname($middlewareFile), 0755, true);
        }
        
        $middlewareContent = '<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next)
    {
        // Set Accept header to application/json
        $request->headers->set("Accept", "application/json");
        
        // Get the response
        $response = $next($request);
        
        // Force JSON response
        if (!$response instanceof JsonResponse) {
            $content = $response->getContent();
            $status = $response->status();
            $headers = $response->headers->all();
            
            $response = new JsonResponse(
                ["message" => $content],
                $status,
                $headers,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            );
        }
        
        // Set headers
        return $response
            ->header("Content-Type", "application/json")
            ->header("Access-Control-Allow-Origin", "*")
            ->header("Access-Control-Allow-Methods", "GET, POST, PUT, PATCH, DELETE, OPTIONS")
            ->header("Access-Control-Allow-Headers", "Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN");
    }
}';
        
        if (file_put_contents($middlewareFile, $middlewareContent) !== false) {
            message("ForceJsonResponse middleware has been created", 'success');
        } else {
            message("Failed to create ForceJsonResponse middleware", 'error');
        }
    } else {
        message("ForceJsonResponse middleware exists", 'success');
    }
    
    // 7. Clear caches
    message("Clearing application caches...", 'info');
    $commands = [
        'cache:clear',
        'config:clear',
        'route:clear',
        'view:clear'
    ];
    
    foreach ($commands as $cmd) {
        $output = [];
        $return_var = 0;
        exec("php artisan {$cmd}", $output, $return_var);
        
        if ($return_var === 0) {
            message("Successfully ran: php artisan {$cmd}", 'success');
        } else {
            message("Failed to run: php artisan {$cmd}", 'error');
        }
    }
    
    message("\nFix script completed. Please restart your web server for changes to take effect.", 'info');
}

// Run the main function
main();

// If running in browser, add some styling
if (!$isCli) {
    echo '<style>body{font-family: monospace; line-height: 1.6; margin: 20px;}</style>';
}
?>
