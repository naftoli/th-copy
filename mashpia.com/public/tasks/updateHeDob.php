<?php
require '../db.php';

$users = [];
$sql = "select * from users where user_registered > 0";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $heDob = $row['dob_he'];
    if ( !empty( $heDob ) && !preg_match('/[א-ת]/', $heDob) ) {
        $users[] = $row['user_id'];
        echo $heDob . "<br />";
    }
}

require '../class.heDob.php';
foreach ( $users as $user ) {
    $h = new HeDob( $user );
    $h->setHeDob();
}
echo "done.";