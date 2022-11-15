<?php
$admin_auth = ['school'];
require 'header.php';

$users = [];
$sql = "SELECT 
            user_id,
            user_serial, 
            first, 
            last, 
            non_th_school, 
            non_th_school_id, 
            non_th_city, 
            non_th_state, 
            school_name, 
            c.class_grade, 
            c.class_sub 
        FROM
            users u
                LEFT JOIN
            non_th_schools USING (non_th_school_id)
                JOIN
            classes c ON c.class_id = u.class_id
        WHERE
            (non_th_school_id > 0
                OR non_th_school > 0)
                AND u.school_id = 269 
        ORDER BY c.class_grade , c.class_sub , last , first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
    $users[$grade][] = $row;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <link href="admin_styles.css" rel="stylesheet" type="text/css">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Miles Report</title>
    <style type='text/css'>
      @media print {
        table {
          page-break-after: always;
        }
        .no-print {
          display: none;
        }
      }
      @media all {
        th, td {
          border-bottom: 1px solid grey;
          padding: 10px;
          font-size: 12px;
        }
      }
    </style>
</head>

<body>
<? include('admin_header.php');?>
<h1 class="no-print">Non TH Schools Report</h1>
<table>
    <tr>
        <th>Grade</th><th>Student</th><th>Non TH School</th>
    </tr>
    <?php
    foreach ($users as $grade => $kids) {
        foreach ($kids as $child) {
            $name = $child['first'] . ' ' . $child['last'];
            echo "<tr><td>" . $grade . "</td><td>" . $name . "</td><td>";
            if (isset($child['school_name'])) echo $child['school_name'];
            else echo $child['non_th_school'] . ' ' . $child['non_th_city'] . ', ' . $child['non_th_state'];
            echo "</td></tr>";
        }
    }
    ?>
</table>