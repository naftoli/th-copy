<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once '../../api/header/header.php';

$base = \School::find([ 9 ]);
//echo json_encode(json_decode($base->to_json()));
echo json_encode($base);