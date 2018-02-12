<?php
ini_set('display_errors',1);
$admin_auth = array('school'); 
require('header.php');

$admin_id = $admin_user['admin_id'];
$auth = $admin_auth[0];

require 'class.adminSchools.php';
$a = new AdminSchools( $admin_id, $auth );
$schools = $a->getSchools();

$grades = array();
$sql = "select school_id, class_id, class_grade, class_sub from classes where class_era = 0 and school_id in (" . implode(',', array_keys($schools)) . ") order by class_grade, class_sub";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $grades[] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <LINK href="admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            tr, td {
                font-size: 12px;
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <?include('admin_header.php');?>
        <h1>Master Report Generator</h1>
        <p>
            Choose the criteria that you would like for your report.
        </p>
        <form action="generate_report.php" method="post">
            <table>
                <tr>
                    <td>Grade</td>
                    <td>
                        <select name="grade">
                            <?php
                            foreach ($grades as $row) {
                                $grade = $row['class_grade'] . (!empty($row['class_sub']) ? '-' . $row['class_sub'] : '');
                                echo "<option value='" . $row['class_id'] . "'>" . $grade . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type='checkbox' name='email' />
                    </td>
                    <td>
                        Email Address
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="radio" name="reg" value='1' />
                    </td>
                    <td>
                        Only Registered students
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="radio" name="reg" value='0' />
                    </td>
                    <td>
                        Only Unregistered students
                    </td>
                </tr>
                <tr>
                    <td colspan='2' align="center">
                        <input type='submit' name='submit' value='submit' />
                    </td>
                </tr>
            </table>
        </form>
    </body>
</html>
