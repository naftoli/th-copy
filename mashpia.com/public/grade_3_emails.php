<?php
require_once 'db.php';

$sql = "select admin_email from admins 
        where admin_id in (
        select admin_id from admin_auths aa 
        join users u on aa.id = u.user_id 
        join classes c on c.class_id = u.class_id 
        where u.user_registered > 0 
        and c.class_grade = '3') 
        and admin_email != '' 
        and admin_email is not null";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  echo $row['admin_email'] . "<br >";
}