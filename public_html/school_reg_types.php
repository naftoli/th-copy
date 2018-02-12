<?php
$admin_auth = array('school'); 
require('header.php'); 

$info = array();
$sql = "select school_id, school_name, reg_type, school_era from schools where chayolei = 1 order by school_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            tr, th, td {
                font-size: 12px;
                padding: 5px;
            }
            th:not(:first-child) {
                text-align: center;
            }
            td:not(:first-child) {
                width: auto;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <? include('admin_header.php');?>
        <h1>School Registration Types</h1>
        <table>
            <tr>
                <th>School</th>
                <th>Registered</th>
                <th>School pays for registration</th>
                <th>Parents Pay reduced fee & then <br />School pays for all non-registered children</th>
                <th>Parents pay full fee</th>
            </tr>
            <?php
            foreach ($info as $row) {
                $name = 'reg_type' . $row['school_id'];
                if ($row['reg_type'] == 1) {
                    echo "<tr><td>" . $row['school_name'] . "</td><td>";
                    if ($row['school_era'] > 0) echo "no";
                    else echo "yes";
                    echo "</td><td><input type='radio' name='$name' checked='checked' /></td><td><input type='radio' name='$name' /></td><td><input type='radio' name='$name' /></td></tr>";
                } else if ($row['reg_type'] == 2) {
                    echo "<tr><td>" . $row['school_name'] . "</td><td>";
                    if ($row['school_era'] > 0) echo "no";
                    else echo "yes";
                    echo "</td><td><input type='radio' name='$name' /></td><td><input type='radio' name='$name' checked='checked' /></td><td><input type='radio' name='$name' /></td></tr>";
                } else if ($row['reg_type'] == 3) {
                    echo "<tr><td>" . $row['school_name'] . "</td><td>";
                    if ($row['school_era'] > 0) echo "no";
                    else echo "yes";
                    echo "</td><td><input type='radio' name='$name' /></td><td><input type='radio' name='$name' /></td><td><input type='radio' name='$name' checked='checked' /></td></tr>";
                } else {
                    echo "<tr><td>" . $row['school_name'] . "</td><td>";
                    if ($row['school_era'] > 0) echo "no";
                    else echo "yes";
                    echo "</td><td><input type='radio' name='$name' /></td><td><input type='radio' name='$name' /></td><td><input type='radio' name='$name' /></td></tr>";
                }
            }
            ?>
        </table>
    </body>
</html> 