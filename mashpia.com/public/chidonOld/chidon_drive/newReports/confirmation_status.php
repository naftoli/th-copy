<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$info = [];
$sql = "select * from th_chidon tc 
        join users u on tc.user_id = u.user_id 
        join classes c on c.class_id = u.class_id 
        join schools s on s.school_id = u.school_id 
        join th_chidon_zelda tcz on tc.parent_id = tcz.admin_id 
        where u.school_id in (
        " . implode(',', array_keys($schools)) . ") 
        and tc.year = 5781 
        and (tc.shabbaton_maven = 1 or tc.shabbaton_pro = 1 or tc.shabbaton_expert = 1 or tc.shabbaton_trophy = 1) 
        group by u.user_id 
        order by u.school_id, class_grade, class_sub, u.last, u.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[$row['school_id']][] = $row;
}

function convertBool($val) {
    return intval($val) == 1 ? 'yes' : 'no';
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <link href="../../../admin_styles.css" rel="stylesheet" type="text/css"/>
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
    <?php
    include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
    echo "<h1>Confirmation Status</h1>";
    foreach ($schools as $school_id => $school_name) {
        if (isset($info[$school_id])) {
            echo "<h2>" . $school_name . "</h2>";
            ?>
            <table>
                <tr>
                    <th>Grade</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Confirmed Prizes</th>
                    <th>Confirmed Sweaters</th>
                    <th>Registration Balance</th>
                </tr>
                <?php
                foreach ($info[$school_id] as $row) {
                    $sql = "select confirmed_prizes, confirmed_sweaters from admins where admin_id = " . $row['parent_id'];
                    $result = mysql_query($sql);
                    $row2 = mysql_fetch_assoc($result);
                    $grade = $row['class_grade'] . ($row['class_sub'] ? '-' . $row['class_sub'] : '');
                    echo "<tr><td>" . $grade . "</td><td>" . $row['first'] . "</td><td>" . $row['last'] . "</td><td>" .
                        convertBool($row2['confirmed_prizes']) . "</td><td>" . convertBool($row2['confirmed_sweaters']) .
                        "</td><td>" . $row['balance'] . "</td></tr>";
                }
                ?>
            </table>
            <?php
        }
    }
    ?>
    </body>
</html>
