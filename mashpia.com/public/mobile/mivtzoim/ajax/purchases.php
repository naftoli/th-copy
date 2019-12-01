<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mivtzoim_purchases/classes/MivtzoimPurchases.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

$item = $_POST['item'];
$m = new MivtzoimPurchases();
$purchases = $m->getPurchases( $year, $item );

$children = [];
foreach ( $purchases as $row ) {
    if ( strpos($row['users'], ',') !== false ) {
        $users = explode(',', $row['users']);
        foreach ( $users as $id ) {
            $children[] = intval( $id );
        }
    } else {
        $children[] = intval( $row['users'] );
    }
}

echo count( $children );