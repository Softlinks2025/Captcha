<?php

// Test OpenSSL configuration
function testOpenSSL() {
    echo "=== OpenSSL Test ===\n\n";
    
    // Check if OpenSSL is loaded
    if (!extension_loaded('openssl')) {
        die("❌ OpenSSL extension is not loaded\n");
    }
    echo "✅ OpenSSL extension is loaded\n";
    
    // Get OpenSSL version
    echo "OpenSSL Version: " . OPENSSL_VERSION_TEXT . "\n";
    
    // Check if we can create a new private key
    $config = [
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ];
    
    $res = openssl_pkey_new($config);
    if ($res === false) {
        echo "❌ Failed to generate private key. Error: " . openssl_error_string() . "\n";
    } else {
        echo "✅ Successfully generated private key\n";
    }
    
    // Check common SSL/TLS functions
    $functions = [
        'openssl_encrypt',
        'openssl_decrypt',
        'openssl_sign',
        'openssl_verify',
        'openssl_pkey_get_public',
        'openssl_pkey_get_private',
    ];
    
    echo "\n=== Checking Required Functions ===\n";
    foreach ($functions as $func) {
        if (function_exists($func)) {
            echo "✅ $func exists\n";
        } else {
            echo "❌ $func is missing\n";
        }
    }
    
    // Check for common SSL issues
    echo "\n=== Checking Common SSL Issues ===\n";
    
    // Check if we can open a secure connection
    $url = 'https://www.howsmyssl.com/a/check';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    $response = curl_exec($ch);
    
    if ($response === false) {
        echo "❌ Failed to make HTTPS request: " . curl_error($ch) . "\n";
    } else {
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "✅ Successfully made HTTPS request to howsmyssl.com\n";
            echo "SSL Version: " . ($data['tls_version'] ?? 'Unknown') . "\n";
            echo "Cipher: " . ($data['cipher_suite'] ?? 'Unknown') . "\n";
        } else {
            echo "❌ Invalid JSON response from howsmyssl.com\n";
        }
    }
    curl_close($ch);
}

// Run the tests
testOpenSSL();

// Test server configuration
echo "\n=== Server Configuration ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "\n";

// Check for common configuration issues
$warnings = [];

// Check if allow_url_fopen is enabled
if (!ini_get('allow_url_fopen')) {
    $warnings[] = "allow_url_fopen is disabled - may affect some functionality";
}

// Check if cURL is available
if (!function_exists('curl_init')) {
    $warnings[] = "cURL extension is not available";
}

// Check if JSON extension is available
if (!function_exists('json_encode')) {
    $warnings[] = "JSON extension is not available";
}

// Display warnings if any
if (!empty($warnings)) {
    echo "\n⚠️ Configuration Warnings:\n";
    foreach ($warnings as $warning) {
        echo "- $warning\n";
    }
} else {
    echo "\n✅ No configuration issues detected\n";
}

echo "\nTest completed.\n";
