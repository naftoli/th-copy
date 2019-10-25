<?php
require '../db.php';

$users = [];
$sql = "select * from users where dob > 0 and dob_he = '' and user_registered > 0";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $users[] = $row['user_id'];
}

require '../class.heDob.php';
foreach ( $users as $user ) {
    $h = new HeDob( $user );
    $h->setHeDob();
}
echo "done.";