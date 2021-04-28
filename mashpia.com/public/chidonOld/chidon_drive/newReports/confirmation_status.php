<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$admins = [];
$sql = "select a.* from admins a 
        join admin_auths aa using (admin_id) 
        join users u on aa.id = u.user_id 
        where u.school_id in (
        " . implode(',', array_keys($schools)) . ")
        group by a.admin_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $admins[] = $row;
}

function convertBool($val) {
    return intval($val) == 1 ? 'yes' : 'no';
}
?>
<!DOCTYPE html>
<html>
    <head>
        <style>
            tr, th, td {
                font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
                font-size: 14px;
                padding: 5px;
                border: 1px solid darkcyan;
            }
        </style>
    </head>
    <body>
        <table>
            <tr>
                <th>Admin ID</th>
                <th>Name</th>
                <th>Confirmed Prizes</th>
                <th>Confirmed Sweaters</th>
<!--                <th>Confirmed Registration</th>-->
            </tr>
            <?php
            foreach ($admins as $admin) {
                echo "<tr><td>" . $admin['admin_id'] . "</td><td>" . $admin['first'] . ' ' . $admin['last'] . "</td><td>" .
                    convertBool($admin['confirmed_prizes']) . "</td><td>" . convertBool($admin['confirmed_sweaters']) . "</td></tr>";
//                    "</td><td>" . convertBool($admin['confirmed_reg']) .
            }
            ?>
        </table>
    </body>
</html>
