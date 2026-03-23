<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

if ($admin_user['auth'] != 'super') {
    die('Access denied');
}

// load csv file with payments that need to be done
$payments = [];
$handle = fopen('needsPayment.csv', 'r');
while (($data = fgetcsv($handle)) !== false) {
    $payments[$data[0]] = $data[1];
}

$stmtUser = $MASHPIA_DB->prepare("select * from users where user_serial = :serial");

$stmtCharges = $MASHPIA_DB->prepare("
    select * from registration_charges 
    where type in ('RRYSD', 'RRYDA', 'RRHVN') 
    and year = :year and user_id = :user and amount = :amount
");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Payment Report <?= GlobalSettings::getChidonRegYear() ?></title>
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
<h1>Payment Report</h1>
<table>
  <tr>
    <th>User ID</th>
    <th>User Serial</th>
    <th>Child Name</th>
    <th>Amount Owing</th>
    <th>Payment</th>
    <th>Balance</th>
  </tr>
    <?php
    $total = 0;
    foreach ($payments as $serial => $amount) {
        $stmtUser->execute([':serial' => $serial]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
        $stmtCharges->execute([
            ':year' => GlobalSettings::getChidonRegYear(),
            ':user' => $user['user_id'],
            ':amount' => floatval($amount)
        ]);
        $charge = $stmtCharges->fetch(PDO::FETCH_ASSOC);
        $balance = $charge ? 0 : $amount;
        $total += $balance;
        ?>
      <tr>
        <td><?= $user['user_id'] ?></td>
        <td><?= $user['user_serial'] ?></td>
        <td><?= $user['first'] . ' ' . $user['last'] ?></td>
        <td><?= $amount ?></td>
        <td><?= $charge ? $amount : 0 ?></td>
        <td><?= $balance ?></td>
      </tr>
        <?php
    }
    ?>
  <tr>
    <th colspan="5" style="text-align: right">Total Balance:</th>
    <th>$<?= number_format($total, 2) ?></th>
  </tr>
</table>