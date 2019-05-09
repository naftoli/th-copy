<?php
require_once '../../db.php';

$donors = [];
$donations = [];
$sql = "select * from mashpia_charidy.donors";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $donors[] = $row;
}

$sql = "select * from mashpia_charidy.donations";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $donations[$row['donor_id']][$row['year']][] = $row;
}
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
        <th>Donor ID</th>
        <th>Parent ID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Phone</th>
        <th>Email</th>
        <th>5776 Donation</th>
        <th>5777 Donation</th>
        <th>5778 Donation</th>
        <td></td>
      </tr>
      <?php
      $years = [5776,5777,5778];

      foreach ( $years as $year ) {
        $totals[$year] = 0;
      }

      foreach ( $donors as $donor ) {
        $donor_id = $donor['donor_id'];
        echo "<tr><td>" . $donor_id . "</td><td>" . $donor['parent_admin_id'] . "</td><td>" . $donor['first_name'] . "</td><td>" . $donor['last_name'] . "</td><td>" . 
          $donor['phone'] . "</td><td>" . $donor['email'] . "</td><td>";
        foreach ( $years as $year ) {
          if ( isset( $donations[$donor_id][$year] ) ) {
            $total = 0;
            foreach ( $donations[$donor_id][$year] as $donation ) {
              $total += $donation['amount'];
              $totals[$year] += $donation['amount'];
            }
            echo "$" . number_format( $total );
          }
          echo "</td><td>";
        }
        echo "</td></tr>";
      }
      echo "<tr><td colspan='7'></td>";
      foreach ( $totals as $year => $amount ) {
        echo "<th>$" . number_format( $amount ) . "</th>";
      }
      echo "</tr>";
      ?>
    </table>
  </body>
</html>
