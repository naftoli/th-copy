<?php
require '../db.php';

$months = [ '', 'Tishrei', 'Cheshvon', 'Kislev', 'Teves', 'Shvat', 'Adar I', 'Adar II', 'Nissan', 'Iyar', 'Sivan', 'Tamuz', 'Av', 'Elul' ];

$info = [];
$sql = "select * from tehillim_ladders";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $info[] = $row;
}
echo "<pre>";
//print_r( $info );
echo "</pre>";
?>
<!doctype html>
<html>
  <head>
    <title>Tehillim Info</title>
    <meta charset="utf8" />
  </head>
  <body>
    <table>
      <thead>
        <tr>
          <th>Ladder</th>
          <th>Year</th>
          <th>Month</th>
          <th>Kapitelach</th>
          <th>Qty</th>
        </tr>
      </thead>
      <tbody>
      <?php
      foreach ( $info as $row ) {
        echo "<tr><td>" . ($row['ladder']-2) . "</td><td>" . $row['age'] . "</td><td>" . $months[$row['month']] . "</td><td>" . $row['kapitelach'] . "</td><td>" . $row['qty'] . "</td></tr>";
      }
      ?>
      </tbody>
    </table>
  </body>
</html>