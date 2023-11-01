<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

// get all admins that have children in myshliach
$admins = [];
$stmt = $MASHPIA_DB->query("
    select * from admins a 
    join admin_auths aa using (admin_id)  
    join users u on u.user_id = aa.id 
    where u.user_registered > 0 
    and u.school_id = 61
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $admins[$row['admin_id']][] = $row;
}

$charges = [];
$stmt = $MASHPIA_DB->prepare("
    select rc.*, u.first, u.last
    from registration_charges rc 
    join users u using (user_id)
    where rc.year = 5784 
    and (
        type in ('THE', 'THMSUSA', 'THMSCAN', 'THMSINT', 'THAKUSA', 'THAKCAN', 'THAKINT', 'shipping') 
        or type like 'THE%'
    )
    and user_id = :user
");
foreach ($admins as $children) {
  foreach ($children as $child) {
    $stmt->execute(['user' => $child['user_id']]);
    $charges[$child['user_id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
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
    <th>Children Registered</th>
    <th>Number of Registered Children</th>
    <th>Registation amount paid</th>
    <th>Shipping Fee Paid</th>
  </tr>
    <?php
    foreach ($admins as $admin_id => $children) {
      $admin = $children[0];
      $name = $admin['first'] . ' ' . $admin['last'];
      $address = $admin['admin_address1'] . " " . $admin['admin_address2'] . "<br />" . $admin['admin_city'] .
          ", " . $admin['admin_state'] . "<br />" . $admin['admin_postal'] . "<br />" . $admin['admin_country'];
      echo "<tr><td>" . $admin_id . "</td><td>" . $name . "</td><td>" . $address . "</td><td>" . count($children) . "</td><td>";
      foreach ($children as $child) {
        echo $child['first'] . ' ' . $child['last'] . "<br />";
      }
      echo "</td><td>";
      foreach ($children as $child) {
        foreach ($charges[$child['user_id']] as $charge) {
          if (strpos($charge['type'], 'THE') !== false) {
            echo $charge['amount'];
            if (intval($charge['discount']) > 0) echo ' (discount: ' . $charge['discount'] . ')';
            echo "<br />";
            break;
          }
        }
      }
      echo "</td><td>";
      foreach ($children as $child) {
        foreach ($charges[$child['user_id']] as $charge) {
          if (strpos($charge['type'], 'THE') === false) {
            echo $charge['amount'];
            echo "<br />";
            break;
          }
        }
      }
      echo "</td></tr>";
    }
    ?>
</table>
</body>
</html>
