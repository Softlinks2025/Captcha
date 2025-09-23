<?php
// Script to find and display PHP configuration information

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
echo "PHP CONFIGURATION INFORMATION\n";
echo str_repeat("=", 80) . "\n\n";

// 1. Basic PHP information
display("PHP Version", PHP_VERSION);
display("PHP Binary", PHP_BINARY);
display("PHP SAPI", PHP_SAPI);
display("PHP OS", PHP_OS);

// 2. Find php.ini files
$loadedIni = php_ini_loaded_file();
$scannedInis = php_ini_scanned_files();

display("Loaded php.ini", $loadedIni ?: 'None');
display("Additional .ini files parsed", $scannedInis ?: 'None');

// 3. Check OpenSSL extension
$opensslLoaded = extension_loaded('openssl');
display("OpenSSL Extension", $opensslLoaded ? 'Loaded' : 'Not loaded');

if ($opensslLoaded) {
    // 4. Check OpenSSL version
    if (defined('OPENSSL_VERSION_TEXT')) {
        display("OpenSSL Version", OPENSSL_VERSION_TEXT);
    } else {
        display("OpenSSL Version", 'Not available (OPENSSL_VERSION_TEXT not defined)');
    }
    
    // 5. Check OpenSSL configuration
    $config = @openssl_get_cert_locations();
    display("OpenSSL Configuration", $config);
    
    // 6. Test basic OpenSSL functions
    $testData = 'test_data';
    $key = 'test_key';
    $signature = @hash_hmac('sha256', $testData, $key);
    
    display("OpenSSL Function Test", [
        'hash_hmac' => $signature ? 'Working' : 'Failed',
        'openssl_random_pseudo_bytes' => function_exists('openssl_random_pseudo_bytes') ? 'Available' : 'Not available',
        'openssl_encrypt' => function_exists('openssl_encrypt') ? 'Available' : 'Not available',
        'openssl_decrypt' => function_exists('openssl_decrypt') ? 'Available' : 'Not available'
    ]);
}

// 7. Check PHP configuration paths
$paths = [
    'PHP_INI_SCAN_DIR' => getenv('PHP_INI_SCAN_DIR'),
    'PHPRC' => getenv('PHPRC'),
    'PATH' => getenv('PATH')
];

display("Environment Variables", $paths);

// 8. Check for disabled functions
$disabled = ini_get('disable_functions');
if ($disabled) {
    display("Disabled Functions", $disabled);
}

// 9. Check for disabled classes
$disabled = ini_get('disable_classes');
if ($disabled) {
    display("Disabled Classes", $disabled);
}

// 10. Check for additional configuration files
$additionalConfigs = [
    'C:\Windows\php.ini',
    'C:\Windows\php.ini-development',
    'C:\Windows\php.ini-production',
    'C:\php\php.ini',
    'C:\php\php.ini-development',
    'C:\php\php.ini-production',
    'C:\xampp\php\php.ini',
    'C:\wamp64\bin\php\php*\php.ini',
    'C:\laragon\etc\php\php.ini',
    'C:\Program Files\PHP\php.ini',
    'C:\Program Files (x86)\PHP\php.ini'
];

$foundConfigs = [];
foreach ($additionalConfigs as $config) {
    if (file_exists($config)) {
        $foundConfigs[] = $config;
    }
}

if (!empty($foundConfigs)) {
    display("Found Additional PHP Configuration Files", $foundConfigs);
}

// 11. Check PHP modules directory
$extDir = ini_get('extension_dir');
if ($extDir && is_dir($extDir)) {
    $extensions = [];
    $files = glob($extDir . '/*.{dll,so}', GLOB_BRACE);
    
    foreach ($files as $file) {
        $extensions[] = basename($file);
    }
    
    display("Extensions in PHP Extension Directory ($extDir)", $extensions);
}

display("Diagnostic completed", "Please review the information above to identify any configuration issues.");
echo "\n" . str_repeat("=", 80) . "\n\n";
