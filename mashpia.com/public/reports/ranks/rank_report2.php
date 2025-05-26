<?php
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

$start_date = 2460550; // beg of year 5785
$sql = "SELECT * FROM users u 
    JOIN rank_marks rm USING (user_id) 
    JOIN ranks r USING (rank_ord) 
    JOIN schools s USING (school_id) 
    JOIN classes c ON c.class_id = u.class_id 
    WHERE rm.date_promoted >= $start_date 
    ORDER BY s.school_name, c.class_grade, c.class_sub, u.last, u.first, rm.rank_ord 
";

$result = mysql_query($sql);

if (!$result) {
    die("Database query failed: " . mysql_error());
}

while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Rank Promotion Report | Tzivos Hashem</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
        <style>
            tr, th, td { 
                border-bottom: 1px solid #000;
                padding: 10px;
                font-size: 14px;
                font-family: Arial, sans-serif;
            }
            table { border-collapse: collapse; }
        </style>
    </head>
    <body>
        <?php include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
        <h1>Rank Promotion Report</h1>
        <div id="report">
            <table>
                <tr>
                    <th>School</th>
                    <th>Class</th>
                    <th>Serial</th>
                    <th>Student</th>
                    <th>Rank</th>
                    <th>Date Promoted</th>
                </tr>
                <?php foreach ($users as $user) { ?>
                <tr>
                    <td><?php echo $user['school_name']; ?></td>
                    <td><?php echo $user['class_grade'] . ' ' . $user['class_sub']; ?></td>
                    <td><?php echo $user['user_serial']; ?></td>
                    <td><?php echo $user['first'] . ' ' . $user['last']; ?></td>
                    <td><?php echo $user['rank_name']; ?></td>
                    <td><?php echo jdtogregorian($user['date_promoted']); ?></td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </body>
</html>