<?php
$admin_auth = array('school'); 
require('header.php');

require_once 'class.adminSchools.php';      
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$info = array();
foreach ($schools as $id => $school) {
    $sql = "select count(*) as total from admins 
            where admin_id in (
                select admin_id from admin_auths where id in (
                    select user_id from users where school_id = " . $id . "   
                )
                and auth = 'user' )";
    //echo $sql . "<br />";
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $info[$id] = $row['total'];
}
echo "<pre>";
//print_r($info);
echo "</pre>";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            tr, th, td {
                font-size: 12px;
                font-family: sans-serif;
                padding: 5px;
            }
        </style>
    </head>
    
    <body>
        <table>
            <tr>
                <th>School</th>
                <th>Families (Based on Number of Admin Accounts)</th>
            </tr>
            <?php
            foreach ($schools as $id => $school) {
                if (isset($info[$id])) {
                    echo "<tr><td>" . $school . "</td><td>" . $info[$id] . "</td></tr>";
                }
            }
            ?>
        </table>
    </body>
</html>