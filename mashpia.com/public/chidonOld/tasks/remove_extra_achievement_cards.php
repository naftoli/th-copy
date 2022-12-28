<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';

$sql = "SELECT 
            first,
            last,
            class_grade,
            class_sub,
            user_point_id,
            achievement_card_id,
            user_id,
            institution_id,
            points
        FROM
            pointsDB.user_points p
                JOIN
            mashpiadb.users u USING (user_id)
                JOIN
            mashpiadb.classes c ON c.class_id = u.class_id
        WHERE
            institution_id = 255 
                AND resource_name = 'specific achievement card'
                AND created > '2022-12-13'
        ORDER BY class_grade, class_sub, last, first";
$res = $mysqli->query($sql);
$rows = $res->fetch_all(MYSQLI_ASSOC);

$users = [];
$cards = [];
foreach ($rows as $row) {
    $cards[$row['user_id']][$row['achievement_card_id']][] = $row;
    $users[$row['user_id']] = [
        'name'  => $row['first'] . ' ' . $row['last'],
        'class' => $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub'])
    ];
}

$points = [];
$duplicates = [];
foreach ($cards as $user => $more) {
    foreach ($more as $card_id => $details) {
        foreach ($details as $idx => $row) {
            if ($idx > 0) {
                $duplicates[] = $row['user_point_id'];
                $points[$row['user_id']][$card_id][] = $row['points'];
            }
        }
    }
}

$totals = [];
foreach ($points as $user => $more) {
    $totals[$user] = 0;
    foreach ($more as $card_id => $details) {
        foreach ($details as $point) {
            $totals[$user] += $point;
        }
    }
}

//echo "<pre>"; print_r($points); echo "</pre>";
//echo "<pre>"; print_r($totals); echo "</pre>";

$deleted = 0;
$success = true;
$mysqli->begin_transaction();
foreach ($duplicates as $point_id) {
    if (! $mysqli->query("delete from pointsDB.user_points where user_point_id = " . $point_id)) {
        $success = false;
        break;
    }
    else $deleted++;
}
if ($success) {
    $mysqli->commit();
    echo "Deleted " . $deleted . " rows.";
} else {
    $mysqli->rollback();
    echo "Error deleting rows.";
}
?>
<!--<!DOCTYPE html>-->
<!--<html>-->
<!--    <head>-->
<!--        <meta charset="utf8" />-->
<!--        <title>Extra Points</title>-->
<!--        <style>-->
<!--          tr, th, td {-->
<!--            font-family: Arial, Helvetica, sans-serif;-->
<!--            padding: 6px;-->
<!--            font-size: 12px;-->
<!--            border-bottom: 1px solid grey;-->
<!--          }-->
<!--        </style>-->
<!--    </head>-->
<!--    <body>-->
<!--        <table>-->
<!--            <tr>-->
<!--                <th>Class</th>-->
<!--                <th>Student</th>-->
<!--                <th>Points being removed</th>-->
<!--            </tr>-->
<!--            --><?php
//            foreach ($totals as $user => $total) {
//                echo "<tr><td>" . $users[$user]['class'] . "</td><td>" . $users[$user]['name'] . "</td><td>" . $total . "</td></tr>";
//            }
//            ?>
<!--        </table>-->
<!--    </body>-->
<!--</html>-->
