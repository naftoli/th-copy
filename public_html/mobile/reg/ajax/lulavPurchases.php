<?php
require '../../../db.php'; 

$children = [];
$query = mysql_query("SELECT users FROM lulav_purchases");
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
