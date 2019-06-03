<?php
ini_set('display_errors',1);
$admin_auth = ['school']; 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
if ( $admin_user['auth'] !== "super" ){
    echo "Invalid Account Permissions. HQ account only"; die();
}

$callers = [];
$sql = "select * from charidy_callers";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $callers[] = $row;
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf8" />
    <style>
      tr, th, td {
        font-size: 14px;
        padding: 5px;
        font-family: Arial;
      }
    </style>
  </head>
  <body>
    <table>
      <thead>
        <tr>
          <th>Team</th>
          <th># of People to Call</th>
          <th># of People Gave This Year</th>
          <th>Total Gave in the Past</th>
          <th>Total Gave this Year</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $grandTotal['people'] = 0;
        $grandTotal['gave'] = 0;
        $grandTotal['past'] = 0;
        $grandTotal['current'] = 0;
        foreach ( $callers as $caller ) {
          $sql = "select count(*) as number from charidy_donors_callers where year = 5779 and charidy_caller_id = " . $caller['charidy_caller_id'];
          $result = mysql_query( $sql );
          $row = mysql_fetch_assoc( $result );
          $numberPeople = $row['number'];

          $sql2 = "select count(*) as number from mashpia_charidy.donations where year = 5779 and donor_id in (
                  select donor_id from charidy_donors_callers where year = 5779 and charidy_caller_id = " . $caller['charidy_caller_id'] . ") 
                  group by donor_id";
          $result = mysql_query( $sql2 );
          $row = mysql_fetch_assoc( $result );
          $numberGave = $row['number'];

          $totalPast = 0;
          $sql3 = "select max(amount) as max from mashpia_charidy.donations where year != 5779 and donor_id in (
                  select donor_id from charidy_donors_callers where year = 5779 and charidy_caller_id = " . $caller['charidy_caller_id'] . ") 
                  group by donor_id";
          $result = mysql_query( $sql3 );
          while ( $row = mysql_fetch_assoc( $result ) ) {
            $totalPast += $row['max'];
          }

          $sql3 = "select sum(amount) as total from mashpia_charidy.donations where year = 5779 and donor_id in (
                  select donor_id from charidy_donors_callers where year = 5779 and charidy_caller_id = " . $caller['charidy_caller_id'] . ")";
          $result = mysql_query( $sql3 );
          $row = mysql_fetch_assoc( $result );
          $totalCurrent = $row['total'];

          echo "<tr><td>" . $caller['first'] . ' ' . $caller['last'] . "</td><td>" . $numberPeople . "</td><td>" . $numberGave . "</td><td>" . 
            number_format( $totalPast, 2) . "</td><td>" . number_format( $totalCurrent, 2 ) . "</td></tr>";
          
          $grandTotal['people'] += $numberPeople;
          $grandTotal['gave'] += $numberGave;
          $grandTotal['past'] += $totalPast;
          $grandTotal['current'] += $totalCurrent;
        }
        echo "<tr><td></td><th>" . number_format( $grandTotal['people'] ) . "</th><th>" . number_format( $grandTotal['gave'] ) . "</th><th>" . 
            number_format( $grandTotal['past'], 2 ) . "</th><th>" . number_format( $grandTotal['current'], 2 ) . "</th></tr>";
        ?>
      </tbody>
    </table>
  </body>
</html>