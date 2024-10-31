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
    SELECT 
        *
    FROM
        chidon_user_prizes cup
            JOIN
        chidon_prizes ON cup.prize_id = chidon_prizes.prize_id
    WHERE
        cup.year = :year
            AND user_id IN (SELECT 
                user_id
            FROM
                users
            WHERE
                school_id = :school)
");

$info = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT 
        tc.*, u.*, c.*
    FROM
        th_chidon tc
            JOIN
        users u ON tc.user_id = u.user_id
            JOIN
        classes c ON u.class_id = c.class_id
    WHERE
        tc.year = :year AND u.school_id = :school
    ORDER BY c.class_grade , c.class_sub , u.last , u.first
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
$tracks = [
    'maven' => 'Yesod',
    'pro'   => 'Yediah',
    'expert'  => 'Havanah',
    'genius' => 'Iyun'
];

// list of children whose prizes were deleted b/c they had more than one personalized prize
$deleted = [7754689, 7758082, 7758438, 7760528, 7763104, 7763344, 7763352, 7763585, 7764789, 7765193, 7770020, 7770948,
    7771049, 7771259, 7771422, 7772403, 7772617, 7773774, 7774117, 7774204, 7774528, 7774550, 7774695, 7775756, 7777592,
    7778891, 7779021, 7779959, 7781249, 7782388, 7785394];

// load csv file with payments that need to be done
$payments = [];
$handle = fopen('https://mashpia.com/chidonOld/newReports/needsPayment.csv', 'r');
while (($data = fgetcsv($handle)) !== false) {
    $payments[$data[0]] = $data[1];
}

// load csv file with ppl that have more than 75 credits worth of prizes
$over75 = [];
$handle = fopen('https://mashpia.com/chidonOld/newReports/over75.csv', 'r');
while (($data = fgetcsv($handle)) !== false) {
    $over75[$data[0]] = $data[1];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Confirmations</title>
    <link href="../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
      @font-face {
        font-family: 'FB';
        src: url('https://mashpia.com/certs/FbTrick-Regular.otf') format('opentype');
      }
      @font-face {
        font-family: 'FB_Black';
        src: url('https://mashpia.com/certs/FbTrick-Black.otf') format('opentype');
      }
      body {
        font-size: 14px;
        line-height: 1.2;
      }
      div.indent {
        margin-left: 30px;
      }
      .text-overlay {
        font-size: 18px;
        position: relative;
        top: -245px;
        width: 400px;
        text-align: center;
        font-family: 'FB';
        color: white;
      }
      button {
        padding: 10px;
        font-size: 16px;
      }
    </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
<h1>Confirmations</h1>
<button onclick="window.print()">Print</button>
<?php
foreach ($info as $school => $students) {
    echo "<h2>" . $schools[$school] . "</h2>";
    foreach ($students as $student) {
        $serial = $student['user_serial'];
        $name = $student['first'] . ' ' . $student['last'];
        $grade = $student['class_grade'] . (empty($student['class_sub']) ? '' : '-' . $student['class_sub']);
        $he_name = $student['first_he'] . ' ' . $student['last_he'];
        $track = $tracks[$student['test_type']];
        $sweater = $student['size'];
        $book = $student['book'];
        $yarmulka = $student['yarmulka'];
        $gender = $student['gender'];
        $user_id = $student['user_id'];
        $prizes_chosen = isset($prizes[$school][$user_id]) ? $prizes[$school][$user_id] : [];

        // figure out if we need to let the parent know that they need to review the prizes
        $serial = $student['user_serial'];
        if (in_array($serial, $deleted)) {
            $problem = "Important note: There was a bug in the system and when you enrolled, 
              it allowed you to choose more than 75 credits worth of personalized prizes which were not even paid for. 
              As a result, we had to remove those prizes. Please login and choose up to 75 credits worth of prizes. 
              If you don't choose the prizes before the 10th of cheshvan, you will not receive any prizes.  
              Sorry for the inconvenience.";
        } else if (isset($payments[$serial])) {
            $problem = "Important note: When a personalized prizes is chosen, it must be paid for in advance and 
              there is no refunds for this prize, even if you don't register for the prizes after test three, unfortunately, 
              there was a bug in the system and when you chose your personalized prizes it did not charge the card on file. 
              Please make sure to login and pay now so that your prizes can be personalized. If you don't pay by the 10th of 
              cheshvan, your prizes will not be personalized. Sorry for the inconvenience.";
        } else if (isset($over75[$serial])) {
            $problem = "Important note: There was a bug in the system and when you enrolled, 
              it allowed you to choose more than 75 credits worth of prizes. Please edit your choice of prizes with up to 75 credits. 
              If you don't edit the prizes before the 10th of cheshvan, HQ will determine which prizes you will receive. 
              Sorry for the inconvenience.";
        } else {
            $problem = '';
        }
        ?>
        <h3><?= $name ?> (<?= $serial ?>)</h3>
        <h4><?= $grade ?></h4>
        <br />
        <p><?= $problem ?></p>
        <div class="conf">
        Hebrew Name spelling for awards: <?= $he_name ?><br />
        Chosen Track: <?= $track ?><br />
        Sweater Size: <?= $sweater ?><br />
        Book Number: <?= $book ?><br />
        <?php if ($gender == 'M') : ?>
            Yarmulka Size: <?= $yarmulka ?><br />
        <?php endif; ?>
        Prizes:<br />
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
        // figure out which certificate to use
        $book = $student['book'];
        $gender = $student['gender'];
        $school_type_id = $student['school_type_id'];

        if (strtoupper($gender) == 'F') {
            $gender = 'G';
        } else {
            $gender = 'B';
        }

        if (in_array($school_type_id, [2, 3])) {
            $file = $gender . (intval($book) + 3) . '.png';
        } else {
            $file = $gender . (intval($book) + 3) . ' Non Chabad.png';
        }

        $file_path = "https://mashpia.com/certs/" . $file;
        echo "<img src='$file_path' style='width: 100%; max-width: 400px;'>";
        echo "<div class='text-overlay'>" . $he_name . "</div>";
        echo "<br /><div style='page-break-after: always;'></div></div>";
    }
}
?>
</body>
</html>