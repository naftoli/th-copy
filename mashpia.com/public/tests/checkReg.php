<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$s = \Soldier::find( [ 8273 ] );
for ($i = 0; $i < 2; $i++)
    echo $s->registrationCharge('chayolei', 50);
