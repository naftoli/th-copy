<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

// get all admins that have children in MyShliach
$admins = [];
$stmt = $MASHPIA_DB->query("
    select a.* from admins a 
    join admin_auths aa using (admin_id)  
    join users u on u.user_id = aa.id 
    where u.user_registered > 0 
    and u.school_id = 61
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $admins[$row['admin_id']] = $row;
}

// find out if any admin did NOT pay the shipping fee (if intl)
$paid = [];
$stmt = $MASHPIA_DB->prepare("
    select * from registration_charges 
    where year = :year 
    and type in ('RRSUSA', 'RRSCAN', 'RRSINT') 
    and user_id in (
        select id from admin_auths where admin_id = :admin_id
    ) 
");
foreach ($admins as $admin_id => $admin) {
    $stmt->execute([
        'year'      => 5784,
        'admin_id'  => $admin_id
    ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $paid[$admin_id] = count($rows) > 0 ? 1 : 0;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf8" />
  <title>AK Hachayol Report</title>
  <style>
    tr, th, td {
      font-size: 14px;
      padding: 5px;
      font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
    }
  </style>
</head>
<body>
<table>
  <tr>
    <th>Family ID</th>
    <th>Family Name</th>
    <th>Address</th>
    <th>Paid</th>
  </tr>
    <?php
    foreach ($admins as $admin_id => $admin) {
      $name = $admin['first'] . ' ' . $admin['last'];
      $address = $admin['admin_address1'] . " " . $admin['admin_address2'] . "<br />" . $admin['admin_city'] .
          ", " . $admin['admin_state'] . "<br />" . $admin['admin_postal'] . "<br />" . $admin['admin_country'];
      echo "<tr><td>" . $admin_id . "</td><td>" . $name . "</td><td>" . $address . "</td><td>" .
          ($paid[$admin_id] ? 'yes' : 'no') . "</td></tr>";
    }
    ?>
</table>
</body>
</html>
