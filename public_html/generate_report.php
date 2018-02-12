<?php
ini_set('display_errors',1);
$admin_auth = array('school'); 
require('header.php');

$admin_id = $admin_user['admin_id'];
$auth = $admin_auth[0];

require 'class.adminSchools.php';
$a = new AdminSchools( $admin_id, $auth );
$schools = $a->getSchools();

//print_r($_POST);

$emails = array();
if (isset($_POST['email'])) {
    $grade = mysql_real_escape_string($_POST['grade']);
    $sql = "select a.admin_email, c.class_grade, c.class_sub from admins a
            join admin_auths aa using (admin_id)
            join users u on u.user_id = aa.id
            join classes c on c.class_id = u.class_id 
            where u.class_id = " . $grade . "
            and aa.auth = 'user'";
    if (isset($_POST['reg'])) {
        if (intval($_POST['reg']) == 1) {
            $sql .= " and u.user_registered > 0";
        } else if (intval($_POST['reg']) == 2) {
            $sql .= " and (u.user_registered is null or u.user_registered = 0)";
        }
    }
    $sql .= " group by admin_email";
    //echo $sql;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $class = $row['class_grade'] . (empty($row['class_sub']) ? '-' . $row['class_sub'] : '');
        $emails[$class][] = $row['admin_email'];
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <LINK href="admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            tr, th, td {
                font-size: 12px;
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <?include('admin_header.php');?>
        <h1>Master Report Generator</h1>
        <table>
            <tr>
                <th>Class</th>
                <th>Email Address</th>
            </tr>
            <?php
            foreach ($emails as $grade => $info) {
                echo "<tr><td>" . $grade . "</td><td>";
                foreach ($info as $email) {
                    echo $email . "<br />";
                }
                echo "</td></tr>";
            }
            ?>
        </table>
    </body>
</html>
