<?php
require '../../../db.php';
$user = mysql_real_escape_string($_POST['user']);

$siblings = array();
$sql = "select aa.id, u.first, u.last from admin_auths aa 
        join users u on u.user_id = aa.id 
        where admin_id in (
            select admin_id from admin_auths
            where id = " . $user . "
            and role_id = 1
        ) and aa.id != " . $user;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $siblings[$row['id']] = $row['first'] . ' ' . $row['last'];
}

echo json_encode($siblings);
?>