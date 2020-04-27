<?php
ini_set('display_errors', 1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$base = new \School();
echo "<pre>"; print_r( $base ); echo "</pre>";