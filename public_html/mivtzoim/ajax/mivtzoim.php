<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$id = $_POST['id'];
$sth = $MASHPIA_DB->prepare("select * from mivtzoim where mivtzoim_id = :id");
$sth->execute( [':id' => $id] );
$rows = $sth->fetchAll();
if ( $rows ) echo json_encode( $rows );
else echo json_encode( $sth->errorInfo() );