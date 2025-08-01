<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once '../header.php';

if ($admin_user['auth'] != 'super') {
    echo "Not authorized";
    exit;
}

echo "<pre>"; print_r($_POST); echo "</pre>";