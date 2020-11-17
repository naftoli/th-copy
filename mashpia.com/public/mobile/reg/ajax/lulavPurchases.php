<?php
require '../../../db.php'; 
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

$children = [];
$query = mysql_query("SELECT users FROM lulav_purchases WHERE year = " . $year);
while ( $row = mysql_fetch_assoc( $query ) ) {
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
