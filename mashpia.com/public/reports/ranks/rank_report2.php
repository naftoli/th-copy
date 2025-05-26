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
            input[type="date"] {
                border: none; background: none;
                border-bottom: 1px solid #000; font-size: 18px;
            }
            hr {display: block;}
            .name.general { font-weight: bold; }
            span.school.general { text-decoration: underline }
            span.rank { color: red; margin-bottom: 4px; display: inline-block;}
            span.school {font-style: italic; margin-bottom: 4px; display: inline-block;}
            span.school.top { margin-top: 4px; margin-bottom: 0px;}
            a.btn.button { display: inline-block; margin-bottom: 15px; }
            img.profile { height: 35px; width: 35px; float: left; margin-right: 10px; }
            .clearfix { clear: both; margin-bottom: 3px; }
            div#totals, div#breakdown { background: #fff; padding: 15px; }
            a.name {color: #000;font-weight: normal;}
        </style>
    </head>
    <body>
        <?php include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
        <h1>Rank Promotion Report</h1>
        <form id="options">
            <label for="from">From</label>
            <input type="date" placehoder="YYYY-MM-DD" id="from" name="from" required />

            <label for="from">To</label>
            <input type="date" placehoder="YYYY-MM-DD" id="to" name="to" required />

            <input type="submit" />
        </form>
        <hr>
        <div id="report">
            <table>
                <tr>
                    <th>School</th>
                    <th>Class</th>
                    <th>Student</th>
                    <th>Rank</th>
                    <th>Date Promoted</th>
                </tr>
                <?php foreach ($users as $user) { ?>
                <tr>
                    <td><?php echo $user['school_name']; ?></td>
                    <td><?php echo $user['class_grade'] . ' ' . $user['class_sub']; ?></td>
                    <td><?php echo $user['first'] . ' ' . $user['last']; ?></td>
                    <td><?php echo $user['rank_name']; ?></td>
                    <td><?php echo $user['date_promoted']; ?></td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </body>
</html>