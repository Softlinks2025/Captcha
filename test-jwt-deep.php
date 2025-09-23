<?php
// Test JWT token generation with detailed error reporting

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

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
echo "DEEP JWT TOKEN GENERATION TEST\n";
echo str_repeat("=", 80) . "\n\n";

// 1. Check PHP version and environment
display("PHP Environment", [
    'PHP Version' => PHP_VERSION,
    'PHP Binary' => PHP_BINARY,
    'PHP SAPI' => PHP_SAPI,
    'PHP OS' => PHP_OS,
    'Loaded php.ini' => php_ini_loaded_file() ?: 'None',
    'Additional .ini files' => php_ini_scanned_files() ?: 'None'
]);

// 2. Check OpenSSL extension
$opensslLoaded = extension_loaded('openssl');
display("OpenSSL Extension", [
    'Loaded' => $opensslLoaded ? 'Yes' : 'No',
    'Version' => defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : 'Not available',
    'SSL Version' => defined('OPENSSL_VERSION_NUMBER') ? 
        sprintf('0x%x', OPENSSL_VERSION_NUMBER) : 'Not available'
]);

if ($opensslLoaded) {
    // Check OpenSSL functions
    $functions = [
        'openssl_pkey_new',
        'openssl_pkey_get_private',
        'openssl_pkey_get_public',
        'openssl_sign',
        'openssl_verify',
        'openssl_encrypt',
        'openssl_decrypt',
        'hash_hmac',
        'hash_hmac_algos',
        'openssl_random_pseudo_bytes',
        'openssl_cipher_iv_length',
        'openssl_get_cipher_methods',
        'openssl_get_cert_locations'
    ];
    
    $availableFunctions = [];
    foreach ($functions as $func) {
        $availableFunctions[$func] = function_exists($func) ? 'Available' : 'Not available';
    }
    
    display("OpenSSL Functions", $availableFunctions);
    
    // Test basic OpenSSL operations
    try {
        // Test hash_hmac
        $testHmac = @hash_hmac('sha256', 'test', 'key');
        
        // Test random bytes
        $randomBytes = @openssl_random_pseudo_bytes(16, $strong);
        
        // Test encryption/decryption
        $method = 'AES-128-CBC';
        $key = 'testkey123456789';
        $iv = @openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
        $encrypted = @openssl_encrypt('test', $method, $key, 0, $iv);
        $decrypted = @openssl_decrypt($encrypted, $method, $key, 0, $iv);
        
        display("OpenSSL Operations Test", [
            'hash_hmac' => $testHmac ? 'Success' : 'Failed',
            'random_bytes' => $randomBytes ? 'Success' . ($strong ? ' (strong)' : ' (weak)') : 'Failed',
            'encrypt/decrypt' => ($decrypted === 'test') ? 'Success' : 'Failed',
            'available_ciphers' => count(openssl_get_cipher_methods()) . ' ciphers available'
        ]);
        
    } catch (Exception $e) {
        display("OpenSSL Test Error", $e->getMessage());
    }
}

// 3. Check JWT library
$jwtFiles = [
    'vendor/tymon/jwt-auth/src/Providers/JWTAuthServiceProvider.php',
    'vendor/tymon/jwt-auth/src/Providers/LaravelServiceProvider.php',
    'vendor/tymon/jwt-auth/src/JWT.php',
    'vendor/tymon/jwt-auth/src/Manager.php',
    'vendor/tymon/jwt-auth/src/Validators/PayloadValidator.php'
];

$jwtFilesExist = [];
foreach ($jwtFiles as $file) {
    $jwtFilesExist[$file] = file_exists(__DIR__ . '/' . $file) ? 'Exists' : 'Not found';
}

display("JWT Library Files", $jwtFilesExist);

// 4. Try to load JWT library directly
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    
    // Test JWT token generation directly
    try {
        display("Testing JWT Token Generation Directly");
        
        // Simple JWT implementation for testing
        function generateJwt($payload, $secret) {
            $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
            $payload = json_encode($payload);
            
            $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
            $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
            
            $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $secret, true);
            $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
            
            return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
        }
        
        $testPayload = [
            'sub' => '1234567890',
            'name' => 'Test User',
            'iat' => time(),
            'exp' => time() + 3600
        ];
        
        $secret = 'test-secret-key';
        $token = generateJwt($testPayload, $secret);
        
        display("JWT Token Generation", [
            'status' => 'Success',
            'token' => $token,
            'token_parts' => explode('.', $token),
            'note' => 'This confirms basic JWT functionality works with PHP/OpenSSL'
        ]);
        
    } catch (Exception $e) {
        display("JWT Generation Error", [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}

// 5. Check PHP configuration
display("PHP Configuration", [
    'disable_functions' => ini_get('disable_functions') ?: 'None',
    'disable_classes' => ini_get('disable_classes') ?: 'None',
    'open_basedir' => ini_get('open_basedir') ?: 'None',
    'suhosin.executor.func.blacklist' => ini_get('suhosin.executor.func.blacklist') ?: 'None',
    'suhosin.executor.disable_eval' => ini_get('suhosin.executor.disable_eval') ?: 'None',
    'suhosin.simulation' => ini_get('suhosin.simulation') ?: 'None'
]);

// 6. Check loaded extensions
$extensions = get_loaded_extensions();
sort($extensions);
display("Loaded Extensions (" . count($extensions) . ")", $extensions);

// 7. Check for Suhosin
if (extension_loaded('suhosin')) {
    display("Suhosin Extension", [
        'suhosin.get.max_value_length' => ini_get('suhosin.get.max_value_length'),
        'suhosin.post.max_value_length' => ini_get('suhosin.post.max_value_length'),
        'suhosin.request.max_vars' => ini_get('suhosin.request.max_vars'),
        'suhosin.post.max_vars' => ini_get('suhosin.post.max_vars'),
        'suhosin.request.max_array_index_length' => ini_get('suhosin.request.max_array_index_length'),
        'suhosin.post.max_array_index_length' => ini_get('suhosin.post.max_array_index_length')
    ]);
}

display("Test completed", "Please review the output above for any issues.");
echo "\n" . str_repeat("=", 80) . "\n\n";
