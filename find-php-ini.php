<?php
// Display all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to find PHP INI files
function findPhpIniFiles($dir, &$results = []) {
    $files = scandir($dir);
    
    foreach ($files as $file) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $file);
        if (!is_dir($path)) {
            if (strtolower($file) === 'php.ini' || 
                strtolower(substr($file, -8)) === '.ini' ||
                strtolower($file) === 'php.ini-development' ||
                strtolower($file) === 'php.ini-production') {
                $results[] = $path;
            }
        } else if ($file != "." && $file != "..") {
            findPhpIniFiles($path, $results);
        }
    }
    return $results;
}

// Get PHP configuration information
$phpIniPath = php_ini_loaded_file();
$phpIniScanned = php_ini_scanned_files();
$extensionDir = ini_get('extension_dir');
$loadedIniFiles = [];

// Find all PHP INI files in common locations
$commonPaths = [
    'C:\\',
    'C:\\Program Files',
    'C:\\Program Files (x86)',
    'C:\\xampp',
    'C:\\wamp',
    'C:\\laragon',
    'C:\\Users',
    'C:\\ProgramData',
    dirname(PHP_BINARY),
    dirname(php_ini_loaded_file())
];

// Remove duplicates and non-existent paths
$commonPaths = array_unique(array_filter($commonPaths, 'file_exists'));

// Find all PHP INI files
$allIniFiles = [];
foreach ($commonPaths as $path) {
    $found = findPhpIniFiles($path);
    $allIniFiles = array_merge($allIniFiles, $found);
}

// Get loaded extensions
$loadedExtensions = get_loaded_extensions();
sort($loadedExtensions);

// Output results
echo "=== PHP Configuration ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "PHP SAPI: " . php_sapi_name() . "\n";
echo "PHP Binary: " . PHP_BINARY . "\n";

echo "\n=== PHP.ini Information ===\n";
echo "Loaded Configuration File: " . ($phpIniPath ?: 'None') . "\n";
echo "Scan for additional .ini files: " . ($phpIniScanned ?: 'None') . "\n";
echo "Additional .ini files parsed: " . (count($allIniFiles) > 1 ? implode("\n- ", $allIniFiles) : 'None found') . "\n";

echo "\n=== Extension Directory ===\n";
echo "extension_dir: " . $extensionDir . "\n";

// Check for OpenSSL in loaded extensions
echo "\n=== OpenSSL Status ===\n";
$opensslLoaded = extension_loaded('openssl');
echo "OpenSSL Loaded: " . ($opensslLoaded ? 'Yes' : 'No') . "\n";

if ($opensslLoaded) {
    echo "OpenSSL Version: " . OPENSSL_VERSION_TEXT . "\n";
    
    // Check for duplicate loading
    $opensslCount = 0;
    foreach ($allIniFiles as $iniFile) {
        $content = file_get_contents($iniFile);
        if (preg_match('/extension\s*=\s*openssl\.dll/i', $content) || 
            preg_match('/extension\s*=\s*php_openssl\.dll/i', $content) ||
            preg_match('/extension\s*=\s*openssl\.so/i', $content)) {
            echo "Found OpenSSL reference in: $iniFile\n";
            $opensslCount++;
            
            // Show the relevant lines
            $lines = file($iniFile);
            foreach ($lines as $line) {
                if (preg_match('/extension\s*=\s*(openssl|php_openssl)/i', $line)) {
                    echo "  - " . trim($line) . "\n";
                }
            }
        }
    }
    
    if ($opensslCount > 1) {
        echo "\nWARNING: OpenSSL extension is referenced in multiple INI files. This may cause conflicts.\n";
    }
}

// Check for duplicate extensions
echo "\n=== Checking for Duplicate Extensions ===\n";
$extensions = [];
foreach ($allIniFiles as $iniFile) {
    $content = file_get_contents($iniFile);
    if (preg_match_all('/extension\s*=\s*([^\s;]+)/i', $content, $matches)) {
        foreach ($matches[1] as $ext) {
            $ext = strtolower(trim($ext, '\'" '));
            if (!isset($extensions[$ext])) {
                $extensions[$ext] = [];
            }
            $extensions[$ext][] = $iniFile;
        }
    }
}

$hasDuplicates = false;
foreach ($extensions as $ext => $files) {
    if (count($files) > 1) {
        echo "Extension '$ext' is loaded from multiple files:\n";
        foreach ($files as $file) {
            echo "  - $file\n";
        }
        $hasDuplicates = true;
    }
}

if (!$hasDuplicates) {
    echo "No duplicate extensions found.\n";
}

// List all loaded extensions
echo "\n=== Loaded Extensions ===\n";
echo implode(", ", $loadedExtensions) . "\n";

echo "\n=== Test Complete ===\n";
?>
