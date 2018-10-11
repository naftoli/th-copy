<?php
require '../db.php';

$info = array();
$sql = "SELECT * FROM mashpia_production.permissions_backup where institution_id = 360";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $info[] = $row;
}

$fields = ['permission_id', 'template_style', 'registration_expiration', 'registration_date', 'user_id', 'institution_id', 'permission', 'auth_hash', 'default_permission', 
    'registration_location', 'email_notification', 'created', 'modified', 'created_by'];
foreach ( $info as $row ) {
    $qry = "insert ignore into mashpia_production.permissions set ";
    foreach ( $fields as $field ) {
        $qry .= $field . " = '" . $row[$field] . "', ";
    }
    $qry = substr( $qry, 0, -2 );
    mysql_query( $qry ) or die( mysql_error() );
}
echo "done";