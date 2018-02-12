<?php
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Barcode Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Class Report</h1>
        <? 
        require_once 'class.adminSchools.php';
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        
        $info = array();
        foreach ($schools as $id => $name) {
            $sql = "select * from classes where class_era = 5776 and school_id = " . $id;
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                $info[] = $row;
            }
        }
        ?>
        
        <table>
            <tr>
                <th>Grade</th>
                <th>Teacher</th>
                <th>Email</th>
                <th>Cell Phone</th>
            </tr>
            <?
            foreach ($info as $row) {
                echo "<tr><td>" . $row['class_grade'] . '-' . $row['class_sub'] . "</td><td>" . $row['class_teacher'] . "</td><td>" .
                    $row['email'] . "</td><td>" . $row['cell'] . "</td></tr>";
            }
            ?>
        </table>
    </BODY>
</HTML>