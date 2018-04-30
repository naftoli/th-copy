<?php
ini_set('display_errors',1);
require '../../db.php';

$admins = array();
$sql = "select a.* from admins a 
        join admin_auths aa using (admin_id) 
        join users u on u.user_id = aa.id 
        where aa.auth = 'user' 
        and u.user_registered > 0 
        group by aa.admin_id";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $email = $row['admin_email'];
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) $admins[$email][] = $row;
}
echo "<pre>"; print_r($admins); echo "</pre>";
foreach ($admins as $email => $rows) {
    if (count($rows) > 1) {
        echo "multiple accounts with same email found: " . $email . "<br />";
    }
}