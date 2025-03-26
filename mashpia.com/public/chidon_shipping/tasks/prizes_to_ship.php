<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    die('No permission.');
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$year = GlobalSettings::getChidonYear();
$ct = new ChidonTests($year);

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['auth'], $admin_user['admin_id'], true, true);
$schools = $as->getSchools();

$stmt = $MASHPIA_DB->prepare("
    SELECT 
        cup.user_id,
        cup.he_name,
        cp.prize_id,
        cp.prize_name,
        cp.size,
        cp.color,
        tc.ultimate_trip,
        u.first,
        u.last,
        u.user_serial,
        c.class_grade,
        c.class_sub, 
        u.school_id, 
        u.class_id 
    FROM
        chidon_user_prizes cup
            JOIN
        chidon_prizes cp USING (prize_id)
            JOIN
        users u USING (user_id)
            JOIN
        th_chidon tc ON tc.user_id = u.user_id
            AND tc.year = cup.year
            LEFT JOIN
        th_chidon_info tci ON u.user_id = tci.user_id
            AND tc.year = tci.year
            JOIN
        classes c ON u.class_id = c.class_id
    WHERE
        cup.year = :year AND tc.date_paid > 0
            AND tc.ultimate_trip = 0
            AND tc.user_id IN (SELECT 
                user_id
            FROM
                registration_charges
            WHERE
                year = 5785 AND type = 'RRYSD')
");
$stmt->execute(['year' => $year]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// for each child check if the child passed Yediah+
$info = [];
foreach ($rows as $row) {
    // check highest track earned
    $track = $ct->getHighestTrackPassed($row)['highest_track'];
    if (in_array($track, ['', 'yesod', 'Yesod'])) continue;
    $row['track'] = $track;
    $info[] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Prizes to Ship</title>
        <style>
            tr, th, td {
                padding: 10px;
                font-size: 14px;
                font-family: Arial, Helvetica, sans-serif;
                border-bottom: 1px solid #cccccc;
            }
        </style>
    </head>
    <body>
        <h1>Prizes to Ship</h1>
        <table>
            <tr>
                <th>School</th>
                <th>Grade</th>
                <th>Serial</th>
                <th>Name</th>
                <th>Prize</th>
            </tr>
            <?php
            foreach ($info as $row) {
                $school_name = $schools[$row['school_id']];
                $grade = $row['class_grade'] . ($row['class_sub'] ? ' (' . $row['class_sub'] . ')' : '');
                $name = $row['first'] . ' ' . $row['last'];
                echo "<tr><td>" . $school_name . "</td><td>" . $grade . "</td><td>" . $row['user_serial'] . "</td><td>"
                    . $name . "</td><td>" . $row['prize_name'] . "</td></tr>";
            }
            ?>
        </table>
    </body>
</html>
