<?php
header('Content-Type: application/json');

require dirname(__FILE__) . '/../../db.php';

$data = json_decode( file_get_contents('php://input'), true );
if ( is_array( $data ) ) {
    $_POST = $data;
}