<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/utils/utils.php';
checkAuth();
$schools = getSchools();

$rows = [];
$sql = "SELECT 
            first,
            last,
            u.school_id,
            class_grade,
            class_sub,
            user_point_id,
            user_id,
            achievement_card_id,
            card_points,
            COUNT(*) AS total
        FROM
            pointsDB.user_points up
                JOIN
            pointsDB.achievement_cards USING (achievement_card_id)
                JOIN
            users u USING (user_id)
                JOIN
            classes c ON c.class_id = u.class_id
        WHERE
            up.created > '2023-06-01'
                AND achievement_card_id > 0
                AND up.institution_id in (" . implode(',', array_keys($schools)) . ")
        GROUP BY achievement_card_id
        HAVING total > 1 
        ORDER BY class_grade, class_sub, last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $rows[$row['school_id']][] = $row;
}

//$toDelete = [];
//foreach ($rows as $row) {
//    $sql = "SELECT
//                user_point_id
//            FROM
//                pointsDB.user_points
//            WHERE
//                achievement_card_id = " . $row['achievement_card_id'] . "
//                    AND user_id = " . $row['user_id'];
//    $result = mysql_query($sql);
//    $i = 0;
//    while ($r = mysql_fetch_assoc($result)) {
//        if (++$i > 1) {
//            $toDelete[] = $r['user_point_id'];
//        }
//    }
//}
//
//$qrys = [];
//foreach ($toDelete as $user_point_id) {
//    $qry = "delete from pointsDB.user_points where user_point_id = " . $user_point_id;
//    $qrys[] = $qry;
//}
//
//$deleted = 0;
//foreach ($qrys as $qry) {
//    echo $qry . "<br />";
////    if (mysql_query($qry)) $deleted++;
//}
//
//echo "Deleted: " . $deleted;
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Points</title>
        <style>
            table {
                border-collapse: collapse;
            }
            td, th {
                border: 1px solid black;
                padding: 5px;
            }
        </style>
    </head>
    <body>
    <?php foreach ($rows as $school_id => $more) { ?>
        <h2><?= $schools[$school_id] ?></h2>
        <table>
            <tr>
                <th>Grade</th>
                <th>Name</th>
                <th>Card ID</th>
                <th>User ID</th>
                <th>Times Scanned</th>
                <th>Extra Points</th>
            </tr>
            <?php foreach ($more as $row) { ?>
                <tr>
                    <td><?php echo $row['first'] . ' ' . $row['last'] ?></td>
                    <td><?php echo $row['class_grade'] . ($row['class_sub'] ? '-' . $row['class_sub'] : '') ?></td>
                    <td><?php echo $row['achievement_card_id'] ?></td>
                    <td><?php echo $row['user_id'] ?></td>
                    <td><?php echo $row['total'] ?></td>
                    <td><?php echo (intval($row['card_points']) * intval($row['total']) - 1) ?></td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>
    </body>
</html>
