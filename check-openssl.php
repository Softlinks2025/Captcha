<?php
// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "PHP Version: " . PHP_VERSION . "\n\n";

// Check if OpenSSL extension is loaded
$opensslLoaded = extension_loaded('openssl');
echo "OpenSSL Extension Loaded: " . ($opensslLoaded ? 'Yes' : 'No') . "\n";

if ($opensslLoaded) {
    echo "\nOpenSSL Version: " . OPENSSL_VERSION_TEXT . "\n";
    
    // Check OpenSSL configuration
    $config = @openssl_get_cert_locations();
    echo "\nOpenSSL Configuration:\n";
    print_r($config);
    
    // Check available ciphers
    $ciphers = openssl_get_cipher_methods(false);
    echo "\nAvailable Ciphers (first 10):\n";
    print_r(array_slice($ciphers, 0, 10));
    
    // Test JWT HS256 signature (simple test)
    echo "\nTesting JWT HS256 Signature:\n";
    try {
        $key = 'test_key';
        $data = 'test_data';
        $signature = hash_hmac('sha256', $data, $key);
        echo "HMAC-SHA256 Test: " . ($signature ? 'Success' : 'Failed') . "\n";
        echo "Signature: " . substr($signature, 0, 20) . "...\n";
    } catch (\Exception $e) {
        echo "Error testing HMAC: " . $e->getMessage() . "\n";
    }
} else {
    echo "\nOpenSSL extension is not loaded. This is required for JWT token generation.\n";
    echo "Please enable the OpenSSL extension in your php.ini file.\n";
}

// Check PHP configuration files
$inifile = php_ini_loaded_file();
echo "\nLoaded php.ini: " . ($inifile ? $inifile : 'None') . "\n";

// Check JWT requirements
echo "\nJWT Requirements Check:\n";
$requirements = [
    'PHP version >= 7.4' => version_compare(PHP_VERSION, '7.4.0', '>='),
    'OpenSSL extension' => extension_loaded('openssl'),
    'JSON extension' => extension_loaded('json'),
    'MBString extension' => extension_loaded('mbstring'),
    'Random extension' => extension_loaded('random') || function_exists('random_bytes'),
    'DateTime extension' => extension_loaded('date'),
];

foreach ($requirements as $req => $status) {
    echo "- $req: " . ($status ? '✓' : '✗') . "\n";
}

echo "\nTest completed.\n";
