<?php

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true); // add chidon schools
$schools = $as->getSchools();

require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$stmtPrizes = $MASHPIA_DB->prepare("
    SELECT * 
    FROM chidon_user_prizes cup 
    JOIN chidon_prizes ON cup.prize_id = chidon_prizes.prize_id 
    WHERE cup.year = :year 
    AND user_id in (
        SELECT user_id 
        FROM users 
        WHERE school_id = :school
    )
");

$info = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT tc.*, u.*, c.* 
    FROM th_chidon tc 
    JOIN users u ON tc.user_id = u.user_id 
    JOIN classes c ON u.class_id = c.class_id 
    WHERE tc.year = :year AND u.school_id = :school
");
foreach ($schools as $school_id => $name) {
    $stmt->execute([':year' => $year, ':school' => $school_id]);
    $info[$school_id] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtPrizes->execute([':year' => $year, ':school' => $school_id]);
    $rows = $stmtPrizes->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $prizes[$school_id][$row['user_id']][] = $row;
    }
}
//echo "<pre>"; print_r($prizes); echo "</pre>"; exit;
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Confirmations</title>
    <link href="../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
      div.conf {
        font-size: 12px;
        line-height: 1.2;
      }
      div.indent {
        margin-left: 30px;
      }
    </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
<h1>Confirmations</h1>
<?php
foreach ($info as $school => $students) {
    echo "<h2>" . $schools[$school] . "</h2>";
    foreach ($students as $student) {
        $serial = $student['user_serial'];
        $name = $student['first'] . ' ' . $student['last'];
        $grade = $student['class_grade'] . (empty($student['class_sub']) ? '' : '-' . $student['class_sub']);
        $he_name = $student['first_he'] . ' ' . $student['last_he'];
        $track = $student['test_type'];
        $sweater = $student['size'];
        $book = $student['book'];
        $yarmulka = $student['yarmulka'];
        $gender = $student['gender'];
        $user_id = $student['user_id'];
        $prizes_chosen = isset($prizes[$school][$user_id]) ? $prizes[$school][$user_id] : [];
        ?>

        <h3><?= $name ?> (<?= $serial ?>)</h3>
        <h4><?= $grade ?></h4>
        <div class="conf">
        <br />
        Hebrew Name spelling for awards: <?= $he_name ?><br />
        <br />
        Chosen Track: <?= $track ?><br />
        Sweater Size: <?= $sweater ?><br />
        Book Number: <?= $book ?><br />
        <?php if ($gender == 'M') : ?>
            Yarmulka Size: <?= $yarmulka ?><br />
        <?php endif; ?>
        Chidon Prizes:<br />
        <div class="indent">
        <?php
        foreach ($prizes_chosen as $prize) {
            $name = $prize['prize_name'];
            if ($prize['size']) $name .= ' ' . $prize['size'];
            if ($prize['color']) $name .= ' ' . $prize['color'];
            echo $name . "<br />";
            if ($prize['he_name']) echo "<div class='indent'>Engraved: " . $prize['he_name'] . "</div>";
        }
        echo "</div><br />";
        echo "<img src='/chidonOld/certs/Jpegs/$serial.jpg' style='max-height: 600px' /><br /><br />";
        echo "<div style='page-break-after: always;'></div></div>";
    }
}
?>
</body>
</html>