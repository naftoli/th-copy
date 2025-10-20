<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

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
    'pro' => 'Yediah',
    'expert' => 'Havanah',
    'genius' => 'Iyun'
];
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
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
      top: -200px;
      width: 320px;
      text-align: center;
      font-family: 'FB';
      color: white;
    }

    button {
      padding: 10px;
      font-size: 16px;
    }

    @media print {
      button {
        display: none;
      }

      .no_print {
        display: none;
      }
    }

    .outer {
      margin-top: 20px;
      margin-left: 20px;
      margin-right: 20px;
    }
  </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
<h1 class="no_print">Confirmations</h1>
<button onclick="window.print()">Print</button>
<?php
foreach ($info as $school => $students) {
  echo "<h2 class='no_print'>" . $schools[$school] . "</h2>";
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
    $serial = $student['user_serial'];
    ?>
    <div class="outer">
      <div class="inner">
        <h3>Dear <?= $name ?> (<?= $serial ?>) - <?= $grade ?></h3>
        <br/>
        <p>We want to ensure that all your chidon information is accurate. This is your final opportunity to make
          changes to your Chidon details. You have until 5 Cheshvan • October 27 to make any changes,
          after that point, no changes will be possible.</p>
        <div class="conf">
          Hebrew Name spelling for awards: <?= $he_name ?><br/>
          Chosen Track: <?= $track ?><br/>
          Sweater Size: <?= $sweater ?><br/>
          Book Number: <?= $book ?><br/>
            <?php if ($gender == 'M') : ?>
              Yarmulka Size: <?= $yarmulka ?><br/>
            <?php endif; ?>
          <br/>
          Prizes:<br/>
          <div class="indent">
              <?php
              foreach ($prizes_chosen as $prize) {
                  $name = $prize['prize_name'];
                  if ($prize['size']) $name .= ' ' . $prize['size'];
                  if ($prize['color']) $name .= ' ' . $prize['color'];
                  echo $name . "<br />";
                  if ($prize['he_name']) echo "<div class='indent'>Engraved: " . $prize['he_name'] . "</div>";
              }
              ?>
          </div>
        </div>
        <br/>
        If you would like to make any changes please ask your parents to:<br/>
        <div class="indent">Log in to your parent account.<br/>
          Click "Edit Chidon Enrollment."<br/>
          Make any updates needed and confirm that all is good.<br/>
          You’ll then receive an email confirming the changes.<br/>
        </div>
      </div>
      <br/>
      <br/>
      <?php
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
      echo "<img src='$file_path' style='width: 100%; max-width: 320px;' />";
      echo "<div class='text-overlay'>" . $he_name . "</div>";
      echo "<br /></div><div style='page-break-after: always;'></div>";
    }
  }
  ?>
</body>
</html>