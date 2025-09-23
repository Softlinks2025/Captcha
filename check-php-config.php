<?php
// Display all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== PHP Configuration ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "PHP SAPI: " . php_sapi_name() . "\n";
echo "PHP Config File: " . (php_ini_loaded_file() ?: 'None') . "\n";

// Get all loaded extensions
$loadedExtensions = get_loaded_extensions();
sort($loadedExtensions);

echo "\n=== Loaded Extensions ===\n";
foreach ($loadedExtensions as $ext) {
    echo "- $ext\n";}

// Check OpenSSL specifically
echo "\n=== OpenSSL Check ===\n";
$opensslLoaded = extension_loaded('openssl');
echo "OpenSSL Loaded: " . ($opensslLoaded ? 'Yes' : 'No') . "\n";

if ($opensslLoaded) {
    echo "OpenSSL Version: " . OPENSSL_VERSION_TEXT . "\n";
    
    // Check if we can use OpenSSL functions
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
    
    echo "Can Encrypt: " . ($canEncrypt ? 'Yes' : 'No') . "\n";
    echo "Can Decrypt: " . ($canDecrypt ? 'Yes' : 'No') . "\n";
}

// Check JWT_SECRET
echo "\n=== JWT Configuration ===\n";
$jwtSecret = getenv('JWT_SECRET');
echo "JWT_SECRET Set: " . ($jwtSecret ? 'Yes' : 'No') . "\n";
if ($jwtSecret) {
    echo "JWT_SECRET Length: " . strlen($jwtSecret) . "\n";
}

// Check PHP.ini settings
echo "\n=== PHP.ini Settings ===\n";
$settings = [
    'display_errors',
    'error_reporting',
    'log_errors',
    'error_log',
    'extension_dir',
    'openssl.openssl_conf',
    'openssl.config',
    'openssl.config_args'
];

foreach ($settings as $setting) {
    echo "$setting: " . ini_get($setting) . "\n";
}

echo "\n=== Test Complete ===\n";
