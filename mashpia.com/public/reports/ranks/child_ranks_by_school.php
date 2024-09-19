<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    die('Access Denied');
}

function getRanks() {
    $sql = "select * from ranks";
    $result = mysql_query($sql);
    if (!$result) {
        die('Invalid query: ' . mysql_error());
    }
    while ($row = mysql_fetch_assoc($result)) {
        $ranks[$row['rank_ord']] = $row['rank_name'];
    }
    return $ranks;
}

$sql = "SELECT 
            s.school_name,
            c.class_grade,
            c.class_sub,
            u.first,
            u.last,
            MAX(rm.rank_ord) AS rank_ord
        FROM
            users u
                JOIN
            schools s USING (school_id)
                JOIN
            rank_marks rm USING (user_id)
                JOIN
            classes c ON u.class_id = c.class_id
        WHERE
            u.user_registered > 0
        GROUP BY u.user_id
        ORDER BY school_name , u.last , u.first";
$result = mysql_query($sql);
if (!$result) {
    die('Invalid query: ' . mysql_error());
}

$ranks = getRanks();

$info = [];
while ($row = mysql_fetch_assoc($result)) {
    $school = $row['school_name'];
    $grade = $row['class_grade'];
    $sub = $row['class_sub'];
    $name = $row['first'] . ' ' . $row['last'];
    $rank_ord = $row['rank_ord'];
    $info[$school][$grade][$sub][$name] = $rank_ord;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ranks by School</title>
    <style>
      tr, th, td {
        font-family: "Arial", sans-serif;
        font-size: 14px;
        padding: 10px;
        border-bottom: 1px solid #f2f2f2;
      }
      th {
        background-color: #f2f2f2;
      }
    </style>
</head>
<body>
<h1>Ranks by School</h1>
<table>
    <tr>
        <th>School</th>
        <th>Grade</th>
        <th>Sub</th>
        <th>Name</th>
        <th>Ranks</th>
    </tr>
    <?php foreach ($info as $school => $grades) { ?>
        <?php foreach ($grades as $grade => $subs) { ?>
            <?php foreach ($subs as $sub => $students) { ?>
                <?php foreach ($students as $name => $rank) { ?>
                    <tr>
                        <td><?php echo $school; ?></td>
                        <td><?php echo $grade; ?></td>
                        <td><?php echo $sub; ?></td>
                        <td><?php echo $name; ?></td>
                        <td>
                            <?php
                            // show all rank up to and including current rank based off rank ord
                            for ($i = 1; $i <= $rank; $i++) {
                                echo $ranks[$i] . ', ';
                            }
                            ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    <?php } ?>
</table>
</body>
</html>