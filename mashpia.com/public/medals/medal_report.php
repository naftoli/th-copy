<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

$admin_auth = ['school'];
require_once '../header.php';
require_once '../api/header/db.php';
require_once '../class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

// make sure only super auth can view
if ( $admin_user['auth'] != 'super' ) {
    echo 'You are not authorized to view this page.';
    exit;
}

$sql = "
    SELECT 
        u.user_serial,
        s.subject_id,
        s.subject_name,
        COUNT(mm.medal_ord) AS medals
    FROM
        medal_marks mm
            JOIN
        medals m USING (medal_ord)
            JOIN
        users u USING (user_id)
            JOIN
        subjects s USING (subject_id)
            LEFT JOIN
        user_registration ur USING (user_id)
    WHERE
        u.user_registered > 0 AND (
           ur.year = :year OR (ur.year IS NULL AND u.school_id = :school) )
    GROUP BY user_id , subject_id
    ORDER BY user_serial , subject_id , medal_ord";
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute([
    'year'   => $year,
    'school' => 690
]);
$medals = $stmt->fetchAll();
?>
<DOCTYPE html>
<html>
<head>
    <link href="../admin_styles.css" rel="stylesheet" type="text/css">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Medals Report</title>
    <style>
        th, tr, td {
            padding: 10px;
            font-size: 14px;
            border-bottom: 1px solid #cccccc;
        }
    </style>
</head>
<body>
    <?php include('../admin_header.php'); ?>
    <h1>Medals Report</h1>
    <table>
        <tr>
            <th>User Serial</th>
            <th>Subject</th>
            <th>Medals</th>
        </tr>
        <?php
        $total = 0;
        foreach ($medals as $medal) {
            $serial = $medal['user_serial'];
            $subject = $medal['subject_name'];
            $num_medals = $medal['medals'];
            $total += $num_medals;
            echo "<tr><td>" . $serial . "</td><td>" . $subject . "</td><td>" . $num_medals . "</td></tr>";
        }
        echo "<tr><th colspan='2'>Total</th><th>" . number_format($total, 0) . "</th></tr>";
        ?>
    </table>
</body>
</html>