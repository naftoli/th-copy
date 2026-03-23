<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

if ($admin_user['auth'] != 'super') {
    die('Access denied');
}

$year = GlobalSettings::getChidonYear();

// get all prizes selected by users
$stmtPrizes = $MASHPIA_DB->prepare("
    SELECT 
        *
    FROM
        chidon_user_prizes cup
            JOIN
        chidon_prizes cp USING (prize_id)
    WHERE
        cup.year = :year
");
$resPrizes = $stmtPrizes->execute([':year' => $year]);
$prizes = $stmtPrizes->fetchAll(PDO::FETCH_ASSOC);
$user_prizes = [];
foreach ($prizes as $prize) {
    $user_prizes[$prize['user_id']][] = $prize;
}

// list of kids that ordered more than 75 worth of personalized prizes and didn't pay so we deleted their prizes
$deleted = [7754689, 7758082, 7758438, 7760528, 7763104, 7763344, 7763352, 7763585, 7764789, 7765193, 7770020, 7770948,
    7771049, 7771259, 7771422, 7772403, 7772617, 7773774, 7774117, 7774204, 7774528, 7774550, 7774695, 7775756, 7777592,
    7778891, 7779021, 7779959, 7781249, 7782388, 7785394];

// load csv file with payments that need to be done
$payments = [];
$handle = fopen('needsPayment.csv', 'r');
while (($data = fgetcsv($handle)) !== false) {
    $payments[$data[0]] = $data[1];
}

// load csv file with ppl that have more than 75 credits worth of prizes
$over75 = [];
$handle = fopen('over75.csv', 'r');
while (($data = fgetcsv($handle)) !== false) {
    $over75[$data[0]] = $data[1];
}

// get user info
$stmtUserInfo = $MASHPIA_DB->prepare("
    SELECT 
        u.user_id,
        u.user_serial,
        u.first,
        u.last,
        c.class_grade,
        c.class_sub,
        s.school_name,
        a.first AS aFirst,
        a.last AS aLast,
        a.admin_email,
        a.admin_phone_mobile,
        a.admin_phone_work,
        a.admin_phone_home, 
        tc.confirmed_info 
    FROM
        users u
            JOIN
        classes c ON c.class_id = u.class_id
            JOIN
        schools s ON s.school_id = u.school_id
            JOIN
        th_chidon tc USING (user_id)
            LEFT JOIN
        admin_auths aa ON aa.id = u.user_id
            LEFT JOIN
        admins a USING (admin_id)
    WHERE
        tc.year = :year
");
$resUserInfo = $stmtUserInfo->execute([':year' => $year]);
$info = $stmtUserInfo->fetchAll(PDO::FETCH_ASSOC);
$user_info = [];
foreach ($info as $user) {
    $user_info[$user['user_serial']] = $user;
}

// create array with all serials
$allUsers = array_merge($deleted, array_keys($payments), array_keys($over75));
$allUsers = array_unique($allUsers);
// order by school, grade, class, last name, first name
usort($allUsers, function ($a, $b) use ($user_info) {
    $a = $user_info[$a];
    $b = $user_info[$b];
    if ($a['school_name'] != $b['school_name']) {
        return $a['school_name'] <=> $b['school_name'];
    }
    if ($a['class_grade'] != $b['class_grade']) {
        return $a['class_grade'] <=> $b['class_grade'];
    }
    if ($a['class_sub'] != $b['class_sub']) {
        return $a['class_sub'] <=> $b['class_sub'];
    }
    if ($a['last'] != $b['last']) {
        return $a['last'] <=> $b['last'];
    }
    return $a['first'] <=> $b['first'];
});

$stmtCharges = $MASHPIA_DB->prepare("
    select * from registration_charges 
    where type in ('RRYSD', 'RRYDA', 'RRHVN') 
    and year = :year and user_id = :user and amount = :amount
");
?>
<!DOCTYPE html>
<html>
<head>
  <title>HQ Confirmation Report</title>
  <style>
    table {
      font-family: Arial, sans-serif;
      font-size: 14px;
    }

    tr, th, td {
      border-bottom: #f0f0f0 1px solid;
      padding: 10px;
    }
  </style>
</head>
<body>
<h1>HQ Confirmation Report</h1>
<table>
  <tr>
    <th></th>
    <th>User ID</th>
    <th>User Serial</th>
    <th>School</th>
    <th>Grade/Class</th>
    <th>Child Name</th>
    <th>Problem</th>
    <th>Current Prize Selection Total</th>
    <th>Chose personalized Prize</th>
    <th>Paid</th>
    <th>Confirmed Information</th>
    <th>Parent Name</th>
    <th>Parent Email</th>
    <th>Parent Mobile</th>
    <th>Parent Work</th>
    <th>Parent Home</th>
  </tr>
    <?php
    $i = 1;
    foreach ($allUsers as $serial) {
        if (in_array($serial, $deleted)) {
            $problem = 'Over 75 credits of personalized prizes were chosen and not paid so prizes were deleted';
        } elseif (isset($payments[$serial])) {
            $problem = 'Personalized prize chosen and not paid';
        } elseif (isset($over75[$serial])) {
            $problem = 'Over 75 credits chosen';
        } else {
            $problem = '';
        }
        $user = $user_info[$serial];
        if (!$user) continue;
        ?>
      <tr>
        <td><?= $i++ ?></td>
        <td><?= $user['user_id'] ?></td>
        <td><?= $user['user_serial'] ?></td>
        <td><?= $user['school_name'] ?></td>
        <td><?= $user['class_grade'] . ($user['class_sub'] ? '-' . $user['class_sub'] : '') ?></td>
        <td><?= $user['first'] . ' ' . $user['last'] ?></td>
        <td><?= $problem ?></td>
        <td>
            <?php
            if (isset($user_prizes[$user['user_id']])) {
                $total = 0;
                foreach ($user_prizes[$user['user_id']] as $prize) {
                    $total += intval($prize['price']);
                }
                echo $total;
            } else {
                echo 0;
            }
            ?>
        </td>
        <td>
            <?php
            if (isset($user_prizes[$user['user_id']])) {
                $personalized = false;
                foreach ($user_prizes[$user['user_id']] as $prize) {
                    if (!empty($prize['personalization'])) {
                        $personalized = true;
                        break;
                    }
                }
                echo $personalized ? 'Yes' : 'No';
            } else {
                echo 'No';
            }
            ?>
        </td>
        <td>
            <?php
            if (isset($payments[$serial])) {
                $stmtCharges->execute([
                    ':year' => $year,
                    ':user' => $user['user_id'],
                    ':amount' => floatval($payments[$serial])
                ]);
                $charge = $stmtCharges->fetch(PDO::FETCH_ASSOC);
                echo $charge ? 'Yes' : 'No';
            } else {
                echo 'No';
            }
            ?>
        </td>
        <td><?= $user['confirmed_info'] ? 'Yes' : 'No' ?></td>
        <?php if ($user['aFirst'] || $user['aLast']) { ?>
          <td><?= $user['aFirst'] . ' ' . $user['aLast'] ?></td>
          <td><?= $user['admin_email'] ?></td>
          <td><?= $user['admin_phone_mobile'] ?></td>
          <td><?= $user['admin_phone_work'] ?></td>
          <td><?= $user['admin_phone_home'] ?></td>
        <?php } else { ?>
          <td colspan='5'>no parent account found</td>
        <?php } ?>
      </tr>
        <?php
    }
    ?>
</table>