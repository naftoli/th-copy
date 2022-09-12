<?php
require_once '../api/header/header.php';

$base = \School::find([ 39 ]);
//echo "<pre>"; print_r($base); echo "</pre>";
echo json_encode([
    'base'  => $base
]);
echo json_last_error();
