<?php
require '../../db.php';

$info = array();
$sql = 'select th_chidon_id, user_id from th_chidon where year = 5778';
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $info[$row['th_chidon_id']] = $row['user_id'];
}

foreach ($info as $chidon_id => $user_id) {
    $sql = "select admin_id from admin_auths where auth = 'user' and role_id = 1 and id = " . $user_id;
    $result = mysql_query( $sql );
    $row = mysql_fetch_assoc( $result );
    $qry = "update th_chidon set parent_id = " . $row['admin_id'] . " where th_chidon_id = " . $chidon_id;
    //echo $qry . "<br />";
    mysql_query( $qry );
}
echo "done.";