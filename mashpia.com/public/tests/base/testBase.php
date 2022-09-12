<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once '../../api/header/header.php';

$soldier = \Soldier::find([ 4556 ]);
//echo json_encode( $soldier );

$base = \School::find([ 9 ]);
echo json_encode(json_decode($base->to_json()));
