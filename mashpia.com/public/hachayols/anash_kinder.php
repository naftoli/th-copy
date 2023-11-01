<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

// get all admins that have children in myshliach
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

// get all children registered into chidon and the charges they paid
$children = [];
$stmt = $MASHPIA_DB->query("
    select rc.*, u.first, u.last, tc.parent_id from th_chidon tc 
    join registration_charges rc using (user_id, year) 
    join users u using (user_id)
    where tc.year = 5784 
    and (
        type in ('THE', 'THMSUSA', 'THMSCAN', 'THMSINT', 'THAKUSA', 'THAKCAN', 'THAKINT', 'shipping') 
        or type like 'THE%'
    )
    and user_id in (
        select id from admin_auths where admin_id in (" . implode(',', array_keys($admins)) . ")
    )
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $children[$row['parent_id']][$row['user_id']][] = [
        'type'  => $row['type'],
        'paid'  => $row['amount'],
        'discount'  => $row['discount'],
        'date'  => $row['date'],
        'name'  => $row['first'] . ' ' . $row['last']
    ];
}

// find out if any admin did NOT pay the shipping fee (if intl)
$paid = [];
$stmt = $MASHPIA_DB->prepare("
    select * from registration_charges 
    where year = :year 
    and type in ('THMSUSA', 'THMSCAN', 'THMSINT', 'THAKUSA', 'THAKCAN', 'THAKINT', 'shipping') 
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
    <th>Children Registered</th>
    <th>Number of Registered Children</th>
    <th>Registation amount paid</th>
    <th>Date Paid</th>
    <th>Shipping Fee Paid</th>
    <th>Date Paid</th>
  </tr>
    <?php
    foreach ($admins as $admin_id => $admin) {
      $name = $admin['first'] . ' ' . $admin['last'];
      $address = $admin['admin_address1'] . " " . $admin['admin_address2'] . "<br />" . $admin['admin_city'] .
          ", " . $admin['admin_state'] . "<br />" . $admin['admin_postal'] . "<br />" . $admin['admin_country'];
      echo "<tr><td>" . $admin_id . "</td><td>" . $name . "</td><td>" . $address . "</td><td>" .
          ($paid[$admin_id] ? 'yes' : 'no') . "</td></td>";
      foreach ($children[$admin_id] as $kids) {
        echo $kids[0]['name'] . "<br />";
      }
      echo "</td><td>" . count($children[$admin_id]) . "</td><td>";
      foreach ($children[$admin_id] as $kids) {
        foreach ($kids as $child) {
          if (strpos($child['type'], 'THE') !== false) {
            echo $child['paid'];
            if (intval($child['discount']) > 0) echo ' (discount: ' . $child['discount'] . ')';
            echo "</td><td>" . $child['date'] . "</td><td>";
            break;
          }
        }
      }
      foreach ($children[$admin_id] as $kids) {
        foreach ($kids as $child) {
          if (strpos($child['type'], 'THE') !== false) continue;
          echo $child['paid'] . "</td><td>" . $child['date'] . "</td></tr>";
          break;
        }
      }
    }
    ?>
</table>
</body>
</html>
