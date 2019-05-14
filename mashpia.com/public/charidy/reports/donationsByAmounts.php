<?php
require_once '../../db.php';

$donors = [];
$donations = [];
$sql = "select * from mashpia_charidy.donors";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $donors[$row['donor_id']] = $row;
}

$sql = "select * from mashpia_charidy.donations";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $donations[$row['amount']][$row['donor_id']][] = $row;
}
krsort( $donations );
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf8" />
    <style>
      tr, th, td {
        font-family: Arial;
        font-size: 14px;
        padding: 5px;
      }
    </style>
  </head>
  <body>
    <table>
      <tr>
        <th>Amount</th>
        <th>Donor ID</th>
        <th>Parent ID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Phone</th>
        <th>Email</th>
        <th>Year</th>
      </tr>
      <?php
      $prevAmount = 0;
      foreach ( $donations as $amount => $other ) {
        foreach ( $other as $donor => $more ) {
          foreach ( $more as $donation ) {
            echo "<tr><td>";
            if ( $prevAmount != $amount ) {
              echo $amount;
              $prevAmount = $amount;
            }
            echo "</td><td>" . $donor . "</td><td>" . $donors[$donor]['parent_admin_id'] . "</td><td>" . $donors[$donor]['first_name'] . "</td><td>" . $donors[$donor]['last_name'] . "</td><td>" . 
              $donors[$donor]['phone'] . "</td><td>" . $donors[$donor]['email'] . "</td><td>" . $donation['year'] . "</td></tr>";
          }
        }
      }
      ?>
    </table>
  </body>
</html>
