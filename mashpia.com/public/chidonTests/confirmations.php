<?php

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true); // add chidon schools
$schools = $as->getSchools();

require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require $_SERVER['DOCUMENT_ROOT'] . '/chidonOld/shipping/class.chidonShipping.php';
$c = new ChidonShipping();

$prizes = [];
foreach ($schools as $school_id => $name) {
    $prizes[$school_id] = $c->getPrizes('all', $school_id);
}

$info = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT tc.*, u.* FROM th_chidon tc 
    JOIN users u ON tc.user_id = u.user_id 
    WHERE tc.year = :year AND u.school_id = :school
");
foreach ($schools as $school_id => $name) {
    $stmt->execute([':year' => $year, ':school' => $school_id]);
    $info[$school_id] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Confirmations</title>
    <link href="../admin_styles.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
<h1>Confirmations</h1>
<?php
foreach ($info as $school => $students) {
    foreach ($students as $student) {
        $name = $student['first'] . ' ' . $student['last'];
        $grade = $student['class_grade'] . (empty($student['class_sub']) ? '' : '-' . $student['class_sub']);
        $he_name = $student['first_he'] . ' ' . $student['last_he'];
        $track = $student['test_type'];
        $sweater = $student['size'];
        $book = $student['book'];
        $yarmulka = $student['yarmulka'];
        $gender = $student['gender'];
        $user_id = $student['user_id'];
        $prizes_chosen = $prizes[$school][$user_id];
        ?>

        <h2><?= $name ?></h2>
        <h3><?= $grade ?></h3>
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
        <ul>
        <?php
        foreach ($prizes_chosen as $prize) {
            $name = $prize['item'];
            if ($prize['size']) $name .= ' ' . $prize['size'];
            if ($prize['color']) $name .= ' ' . $prize['color'];
            echo "<li>" . $name . "</li>";
            if ($prize['name']) echo "<ul><li>Engraved: " . $prize['name'] . "</li></ul>";
        }
        echo "<ul>";
        echo "<div style='page-break-after: always;'></div>";
    }
}
?>
</body>
</html>