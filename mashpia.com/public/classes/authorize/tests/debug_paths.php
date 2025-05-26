<?php
/**
 * Debug script to verify paths
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Debugging Paths</h2>";

// Get the current directory
echo "<p><strong>Current directory:</strong> " . __DIR__ . "</p>";

// Get the parent directories
$parent_dir = dirname(__DIR__);
echo "<p><strong>Parent directory:</strong> " . $parent_dir . "</p>";

$grandparent_dir = dirname($parent_dir);
echo "<p><strong>Grandparent directory:</strong> " . $grandparent_dir . "</p>";

$great_grandparent_dir = dirname($grandparent_dir);
echo "<p><strong>Great-grandparent directory:</strong> " . $great_grandparent_dir . "</p>";

$mashpia_root = dirname($great_grandparent_dir);
echo "<p><strong>Mashpia root directory:</strong> " . $mashpia_root . "</p>";

// Check if vendor directory exists
$vendor_dir = $mashpia_root . '/vendor';
echo "<p><strong>Vendor directory:</strong> " . $vendor_dir . "</p>";
echo "<p><strong>Vendor directory exists:</strong> " . (is_dir($vendor_dir) ? "Yes" : "No") . "</p>";

// Check if autoload.php exists
$autoload_file = $vendor_dir . '/autoload.php';
echo "<p><strong>Autoload file:</strong> " . $autoload_file . "</p>";
echo "<p><strong>Autoload file exists:</strong> " . (file_exists($autoload_file) ? "Yes" : "No") . "</p>";

// Check if constants file exists
$constants_file = $mashpia_root . '/includes/authorize_constants.php';
echo "<p><strong>Constants file:</strong> " . $constants_file . "</p>";
echo "<p><strong>Constants file exists:</strong> " . (file_exists($constants_file) ? "Yes" : "No") . "</p>";

// Try to include the autoload file
echo "<p><strong>Attempting to include autoload.php:</strong></p>";
try {
    require_once $autoload_file;
    echo "<p style='color: green;'>Successfully included autoload.php</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Failed to include autoload.php: " . $e->getMessage() . "</p>";
}

// Try to include the constants file
echo "<p><strong>Attempting to include authorize_constants.php:</strong></p>";
try {
    require_once $constants_file;
    echo "<p style='color: green;'>Successfully included authorize_constants.php</p>";
    
    // Check if the constants class is available
    echo "<p><strong>Checking if Constants class is available:</strong></p>";
    if (class_exists('includes\\authorize\\AuthorizeConstants')) {
        echo "<p style='color: green;'>Constants class exists</p>";
        echo "<p><strong>Sandbox Login ID:</strong> " . includes\authorize\AuthorizeConstants::GetMerchantLoginID(true) . "</p>";
    } else {
        echo "<p style='color: red;'>Constants class does not exist</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Failed to include authorize_constants.php: " . $e->getMessage() . "</p>";
}
?>
