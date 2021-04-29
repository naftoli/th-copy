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
        join th_chidon tc on tc.user_id = u.user_id 
        where u.school_id in (
        " . implode(',', array_keys($schools)) . ") 
        and tc.year = 5781 
        and (tc.shabbaton_maven = 1 or tc.shabbaton_pro = 1 or tc.shabbaton_expert = 1 or tc.shabbaton_trophy = 1)
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
                <th>Email</th>
                <th>Confirmed Prizes</th>
                <th>Confirmed Sweaters</th>
                <th>Registration Balance</th>
            </tr>
            <?php
            foreach ($admins as $admin) {
                // get registration balance
                $sql = "select SUM(balance) as total from th_chidon_zelda where admin_id = " . $admin['admin_id'];
                $result = mysql_query($sql);
                $row = mysql_fetch_assoc($result);
                echo "<tr><td>" . $admin['admin_id'] . "</td><td>" . $admin['first'] . ' ' . $admin['last'] . "</td><td>" .
                    $admin['admin_email'] . "</td><td>" . convertBool($admin['confirmed_prizes']) . "</td><td>" .
                    convertBool($admin['confirmed_sweaters']) . "</td><td>" . $row['total'] . "</td></tr>";
//                    "</td><td>" . convertBool($admin['confirmed_reg']) .
            }
            ?>
        </table>
    </body>
</html>
