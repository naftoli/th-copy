<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mivtzoim/classes/mivtzoim.php';

$info = [
    'data'  =>  [], 
    'error' =>  false
];

$id = $_POST['id'];
try {
    $m = new Mivtzoim( $id );
    $names = $m->getShortNames();
    $info['data'] = $names;
} catch ( \Exception $e ) {
    $info['data'] = $e->getMessage();
    $info['error'] = true;
}
echo json_encode( $info );