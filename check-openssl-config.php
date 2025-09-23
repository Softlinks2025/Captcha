<?php

// Check if OpenSSL extension is loaded
$opensslLoaded = extension_loaded('openssl');
$opensslVersion = $opensslLoaded ? OPENSSL_VERSION_TEXT : 'Not available';

// Check PHP version
$phpVersion = phpversion();

// Check if we're in CLI or web mode
$isCli = (php_sapi_name() === 'cli');
$contentType = $isCli ? 'text/plain' : 'application/json';

// Check JWT secret
$jwtSecret = getenv('JWT_SECRET');
$hasJwtSecret = !empty($jwtSecret);

// Check if we can generate a JWT token
try {
    $token = null;
    if ($opensslLoaded && $hasJwtSecret) {
        $payload = [
            'sub' => 1,
            'iat' => time(),
            'exp' => time() + 3600
        ];
        
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $jwtSecret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        $token = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
} catch (Exception $e) {
    $token = 'Error: ' . $e->getMessage();
}

// Check if we can verify the token
try {
    $tokenVerified = false;
    if ($token && !is_string($token) || strpos($token, 'Error:') !== 0) {
        list($header, $payload, $signature) = explode('.', $token);
        $valid = hash_hmac('sha256', "$header.$payload", $jwtSecret, true);
        $decodedSignature = base64_decode(str_replace(['-', '_'], ['+', '/'], $signature));
        $tokenVerified = hash_equals($valid, $decodedSignature);
    }
} catch (Exception $e) {
    $tokenVerified = 'Error: ' . $e->getMessage();
}

// Check if we can use OpenSSL functions
$canEncrypt = false;
$canDecrypt = false;
$testData = 'test';
$testKey = 'test_key';
$testIv = '1234567890123456';

if ($opensslLoaded) {
    $canEncrypt = function_exists('openssl_encrypt');
    if ($canEncrypt) {
        $encrypted = openssl_encrypt($testData, 'AES-256-CBC', $testKey, 0, $testIv);
        $canDecrypt = $encrypted && function_exists('openssl_decrypt');
        if ($canDecrypt) {
            $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $testKey, 0, $testIv);
            $canDecrypt = ($decrypted === $testData);
        }
    }
}

// Check PHP configuration
$config = [
    'php_version' => $phpVersion,
    'openssl' => [
        'loaded' => $opensslLoaded,
        'version' => $opensslVersion,
        'can_encrypt' => $canEncrypt,
        'can_decrypt' => $canDecrypt,
        'config_file' => php_ini_loaded_file(),
        'config_scan_dir' => php_ini_scanned_files() ?: 'None',
        'openssl_conf' => ini_get('openssl.openssl_conf'),
        'config' => ini_get('openssl.config'),
        'config_args' => ini_get('openssl.config_args')
    ],
    'jwt' => [
        'secret_set' => $hasJwtSecret,
        'secret_length' => $hasJwtSecret ? strlen($jwtSecret) : 0,
        'token_generated' => !empty($token) && !is_string($token) || strpos($token, 'Error:') !== 0,
        'token_verified' => $tokenVerified
    ],
    'environment' => [
        'is_cli' => $isCli,
        'sapi_name' => php_sapi_name(),
        'laravel_env' => getenv('APP_ENV') ?: 'Not set',
        'laravel_debug' => getenv('APP_DEBUG') ?: 'Not set',
        'laravel_url' => getenv('APP_URL') ?: 'Not set'
    ]
];

// Output the results
if ($isCli) {
    echo "PHP Version: {$config['php_version']}\n";
    echo "OpenSSL Loaded: " . ($config['openssl']['loaded'] ? 'Yes' : 'No') . "\n";
    echo "OpenSSL Version: {$config['openssl']['version']}\n";
    echo "Can Encrypt: " . ($config['openssl']['can_encrypt'] ? 'Yes' : 'No') . "\n";
    echo "Can Decrypt: " . ($config['openssl']['can_decrypt'] ? 'Yes' : 'No') . "\n";
    echo "PHP Config File: {$config['openssl']['config_file']}\n";
    echo "JWT Secret Set: " . ($config['jwt']['secret_set'] ? 'Yes' : 'No') . "\n";
    echo "JWT Secret Length: {$config['jwt']['secret_length']}\n";
    echo "JWT Token Generated: " . ($config['jwt']['token_generated'] ? 'Yes' : 'No') . "\n";
    echo "JWT Token Verified: " . (is_bool($config['jwt']['token_verified']) ? ($config['jwt']['token_verified'] ? 'Yes' : 'No') : $config['jwt']['token_verified']) . "\n";
} else {
    header('Content-Type: application/json');
    echo json_encode($config, JSON_PRETTY_PRINT);
}

// Additional debug info if needed
if (isset($_GET['debug'])) {
    echo "\n\nAdditional Debug Info:\n";
    echo "Loaded Extensions: " . implode(", ", get_loaded_extensions()) . "\n";
    echo "PHP Info: " . phpversion('openssl') . "\n";
    if (function_exists('openssl_get_cert_locations')) {
        echo "OpenSSL Cert Locations: " . print_r(openssl_get_cert_locations(), true) . "\n";
    }
}
?>
