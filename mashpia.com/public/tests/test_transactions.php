<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$admin_auth = ['school'];
require_once '../header.php';
require_once '../class.points.php';

if ($admin_user['auth'] != 'super') {
    die('Access denied');
}

$p = new Points(63699);
$transactions = $p->getHistory('2020-01-01', '2025-01-01');
echo "<pre>"; print_r($transactions); echo "</pre>";