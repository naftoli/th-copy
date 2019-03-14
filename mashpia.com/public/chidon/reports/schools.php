<?php
require '../../db.php';
require 'vars.php';

$schools = [];
$sql = "select s.school_name, tc.* from th_chidon_schools tc 
        join schools s using (school_id) 
        where registered = 1 and year = " . $year;
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $schools[] = $row;
}
//echo "<pre>"; print_r( $schools ); echo "</pre>";
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Chidon Schools</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
      tr,th,td {
        font-size: 12px;
        padding: 5px;
        font-family: Arial;
      }
    </style>
  </head>
  <body>
    <table>
      <tr>
        <th>School</th>
        <th>Bus Info</th>
        <th>Food for Trip Back</th>
        <th>Has Payment Info on File</th>
      </tr>
      <?php
      foreach ( $schools as $school ) {
        echo "<tr><td>" . $school['school_name'] . "</td><td>";
        $bus = intval( $school['bus'] );
        switch ( $school['bus'] ) {
          case 0:
            echo "No Bus Needed";
            break;
          case 1:
            echo "Bus leaves to Newark after event";
            break;
          case 2:
            echo "Bus leaves to Crown Heights after event";
            break;
        }
        echo "</td><td>";
        if ( intval( $school['food'] ) ) echo "yes";
        else echo "no";
        echo "</td><td>";
        if ( $school['payment_profile_id'] ) echo "yes";
        else echo "no";
        echo "</td></td>";
      }
      ?>
    </table>
  </body>
</html>