<?php
// Simple check for OpenSSL and JWT requirements

echo "PHP Version: " . PHP_VERSION . "\n\n";

// Basic extension checks
$extensions = [
    'openssl' => 'Required for JWT token generation',
    'json' => 'Required for JSON operations',
    'mbstring' => 'Required for string operations',
    'random' => 'Required for secure random bytes',
    'date' => 'Required for token expiration',
];

echo "Extension Check:\n";
foreach ($extensions as $ext => $purpose) {
    $loaded = extension_loaded($ext);
    echo "- $ext: " . ($loaded ? '✓' : '✗') . " - $purpose\n";
}

// Check OpenSSL functions
if (extension_loaded('openssl')) {
    echo "\nOpenSSL Functions:\n";
    $functions = [
        'openssl_pkey_new',
        'openssl_pkey_get_private',
        'openssl_pkey_get_public',
        'openssl_sign',
        'openssl_verify',
        'openssl_encrypt',
        'openssl_decrypt',
        'hash_hmac',
        'hash_hmac_algos'
    ];
    
    foreach ($functions as $func) {
        echo "- $func: " . (function_exists($func) ? '✓' : '✗') . "\n";
    }
}

// Check PHP configuration
echo "\nPHP Configuration:\n";
$settings = [
    'allow_url_fopen',
    'date.timezone',
    'default_socket_timeout',
    'display_errors',
    'error_reporting',
    'file_uploads',
    'max_execution_time',
    'memory_limit',
    'post_max_size',
    'session.save_handler',
    'session.save_path',
    'session.use_cookies',
    'session.use_only_cookies',
    'session.cookie_httponly',
    'session.cookie_secure',
    'upload_max_filesize',
    'zlib.output_compression'
];

foreach ($settings as $setting) {
    $value = ini_get($setting);
    echo "- $setting = " . var_export($value, true) . "\n";
}

echo "\nTest completed.\n";
