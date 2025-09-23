<?php

/**
 * Fix PHP OpenSSL and JSON Response Issues
 * 
 * This script will:
 * 1. Fix OpenSSL module loading issues
 * 2. Ensure proper JSON responses for API
 * 3. Fix JWT secret generation if needed
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
            'info' => 'color: blue;'
        ];
        echo "<p style='{$colors[$type]}'>{$msg}</p>";
    }
}

// Function to run shell commands
function run_command($cmd) {
    $output = [];
    $return_var = 0;
    $last_line = exec($cmd, $output, $return_var);
    return [
        'output' => $output,
        'return_var' => $return_var,
        'success' => $return_var === 0
    ];
}

// Main function
function main() {
    message("Starting OpenSSL and JSON Response Fix Script...", 'info');
    
    // 1. Check PHP version
    $phpVersion = phpversion();
    message("PHP Version: {$phpVersion}", 'info');
    
    // 2. Check OpenSSL extension
    $opensslLoaded = extension_loaded('openssl');
    $opensslVersion = $opensslLoaded ? OPENSSL_VERSION_TEXT : 'Not available';
    message("OpenSSL Loaded: " . ($opensslLoaded ? 'Yes' : 'No'), 
           $opensslLoaded ? 'success' : 'error');
    message("OpenSSL Version: {$opensslVersion}", 'info');
    
    // 3. Check php.ini file
    $phpIniFile = php_ini_loaded_file();
    $phpIniDir = dirname($phpIniFile);
    $phpModulesDir = dirname(ini_get('extension_dir'));
    message("PHP Config File: {$phpIniFile}", 'info');
    
    // 4. Check for OpenSSL in php.ini
    $phpIniContent = file_get_contents($phpIniFile);
    $opensslLine = 'extension=openssl';
    $opensslEnabled = strpos($phpIniContent, $opensslLine) !== false;
    
    if (!$opensslEnabled) {
        message("OpenSSL extension not enabled in php.ini. Enabling...", 'warning');
        $phpIniContent = str_replace(";{$opensslLine}", $opensslLine, $phpIniContent);
        if (file_put_contents($phpIniFile, $phpIniContent) !== false) {
            message("OpenSSL extension enabled in php.ini. Please restart your web server.", 'success');
        } else {
            message("Failed to enable OpenSSL extension. Please enable it manually in php.ini", 'error');
        }
    } else {
        message("OpenSSL extension is already enabled in php.ini", 'success');
    }
    
    // 5. Check JWT secret in .env
    $envFile = __DIR__ . '/.env';
    $jwtSecret = null;
    $envContent = file_exists($envFile) ? file_get_contents($envFile) : '';
    
    if (preg_match('/JWT_SECRET=(.*)/', $envContent, $matches)) {
        $jwtSecret = trim($matches[1], '"\'');
        message("JWT_SECRET is set in .env", 'success');
    } else {
        message("JWT_SECRET is not set in .env. Generating a new one...", 'warning');
        $newSecret = bin2hex(random_bytes(32));
        $envContent .= "\nJWT_SECRET={$newSecret}\n";
        if (file_put_contents($envFile, $envContent) !== false) {
            message("New JWT_SECRET has been generated in .env", 'success');
            $jwtSecret = $newSecret;
        } else {
            message("Failed to write JWT_SECRET to .env. Please set it manually.", 'error');
        }
    }
    
    // 6. Check ForceJsonResponse middleware
    $middlewareFile = __DIR__ . '/app/Http/Middleware/ForceJsonResponse.php';
    if (file_exists($middlewareFile)) {
        $middlewareContent = file_get_contents($middlewareFile);
        $requiredMiddlewareCode = "header('Content-Type: application/json')";
        
        if (strpos($middlewareContent, $requiredMiddlewareCode) === false) {
            message("ForceJsonResponse middleware needs to be updated", 'warning');
            $newMiddlewareContent = '<?php

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
            
            if (file_put_contents($middlewareFile, $newMiddlewareContent) !== false) {
                message("ForceJsonResponse middleware has been updated", 'success');
            } else {
                message("Failed to update ForceJsonResponse middleware", 'error');
            }
        } else {
            message("ForceJsonResponse middleware is already properly configured", 'success');
        }
    } else {
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
    }
    
    // 7. Check if middleware is registered in Kernel.php
    $kernelFile = __DIR__ . '/app/Http/Kernel.php';
    if (file_exists($kernelFile)) {
        $kernelContent = file_get_contents($kernelFile);
        $middlewareClass = '\App\Http\Middleware\ForceJsonResponse::class';
        
        if (strpos($kernelContent, $middlewareClass) === false) {
            message("ForceJsonResponse middleware not registered in Kernel.php. Registering...", 'warning');
            $kernelContent = str_replace(
                "'api' => [",
                "'api' => [\n            \\App\\Http\\Middleware\\ForceJsonResponse::class,",
                $kernelContent
            );
            
            if (file_put_contents($kernelFile, $kernelContent) !== false) {
                message("ForceJsonResponse middleware has been registered in Kernel.php", 'success');
            } else {
                message("Failed to register ForceJsonResponse middleware in Kernel.php", 'error');
            }
        } else {
            message("ForceJsonResponse middleware is already registered in Kernel.php", 'success');
        }
    } else {
        message("Kernel.php not found. Cannot register middleware.", 'error');
    }
    
    // 8. Clear caches
    message("Clearing application caches...", 'info');
    $commands = [
        'cache:clear',
        'config:clear',
        'route:clear',
        'view:clear'
    ];
    
    foreach ($commands as $cmd) {
        $result = run_command("php artisan {$cmd}");
        if ($result['success']) {
            message("Successfully ran: php artisan {$cmd}", 'success');
        } else {
            message("Failed to run: php artisan {$cmd}", 'error');
        }
    }
    
    message("Fix script completed. Please restart your web server for changes to take effect.", 'info');
}

// Run the main function
main();

// If running in browser, add some styling
if (!$isCli) {
    echo '<style>body{font-family: monospace; line-height: 1.6; margin: 20px;}</style>';
}
?>
