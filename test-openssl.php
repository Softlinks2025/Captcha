<?php
// Simple script to test OpenSSL extension

echo "PHP Version: " . PHP_VERSION . "\n";
echo "OS: " . PHP_OS . "\n";
echo "PHP Binary: " . PHP_BINARY . "\n";
echo "PHP SAPI: " . PHP_SAPI . "\n\n";

// Test if OpenSSL extension is loaded
$opensslLoaded = extension_loaded('openssl');
echo "OpenSSL Extension Loaded: " . ($opensslLoaded ? 'Yes' : 'No') . "\n";

if ($opensslLoaded) {
    // Test basic OpenSSL functions
    echo "\nTesting OpenSSL Functions:\n";
    
    // Test hash_hmac
    $testHmac = @hash_hmac('sha256', 'test', 'key');
    echo "- hash_hmac: " . ($testHmac ? '✓' : '✗') . "\n";
    
    // Test openssl_random_pseudo_bytes
    $testRandom = @openssl_random_pseudo_bytes(16, $strong);
    echo "- openssl_random_pseudo_bytes: " . ($testRandom ? '✓' : '✗') . ($strong ? ' (strong)' : ' (weak)') . "\n";
    
    // Test openssl_encrypt/decrypt
    $data = "test data";
    $method = 'AES-128-CBC';
    $key = 'testkey123456789';
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
    
    $encrypted = @openssl_encrypt($data, $method, $key, 0, $iv);
    $decrypted = @openssl_decrypt($encrypted, $method, $key, 0, $iv);
    
    echo "- openssl_encrypt/decrypt: " . ($decrypted === $data ? '✓' : '✗') . "\n";
    
    // Check OpenSSL version
    echo "\nOpenSSL Version: " . OPENSSL_VERSION_TEXT . "\n";
    
    // List available ciphers
    $ciphers = @openssl_get_cipher_methods(false);
    echo "\nAvailable Ciphers (first 5): " . implode(', ', array_slice($ciphers, 0, 5)) . "...\n";
}

// Check PHP configuration
echo "\nPHP Configuration:\n";
$settings = [
    'allow_url_fopen',
    'disable_functions',
    'disable_classes',
    'open_basedir',
    'suhosin.executor.func.blacklist',
    'suhosin.executor.disable_eval',
    'suhosin.simulation'
];

foreach ($settings as $setting) {
    $value = ini_get($setting);
    if (!empty($value)) {
        echo "- $setting = " . var_export($value, true) . "\n";
    }
}

// Check for disabled functions
$disabled = ini_get('disable_functions');
if (!empty($disabled)) {
    echo "\nDisabled Functions: " . $disabled . "\n";
}

// Check for disabled classes
$disabled = ini_get('disable_classes');
if (!empty($disabled)) {
    echo "Disabled Classes: " . $disabled . "\n";
}

// Check PHP error log
echo "\nPHP Error Log: " . (ini_get('error_log') ?: 'Not set') . "\n";

// Check loaded modules
$modules = get_loaded_extensions();
sort($modules);
echo "\nLoaded Extensions (" . count($modules) . "): " . implode(', ', $modules) . "\n";

// Check for Suhosin
if (extension_loaded('suhosin')) {
    echo "\nSuhosin Extension is loaded. This may interfere with some OpenSSL functions.\n";
}

echo "\nTest completed.\n";
