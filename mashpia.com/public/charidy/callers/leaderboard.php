<?php
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

$info = [];
foreach ( $callers as $caller ) {

}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf8" />
  </head>
  <body>
    <table>
      <thead>
        <tr>
          <th>Team</th>
          <th># of People to Call</th>
          <th># of People Gave</th>
          <th>Total Gave in the Past</th>
          <th>Total Gave this Year</th>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ( $callers as $caller ) {
          $sql = "select count(*) as number from charidy_donors_callers where year = 5779 and charidy_caller_id = " . $caller['caller_id'];
          $result = mysql_query( $sql );
          $row = mysql_fetch_assoc( $result );
          $numberPeople = $row['number'];

          $sql2 = "select count(*) as number from mashpia_charidy.donations where year = 5779 and donor_id in (
                  select donor_id from charidy_donors_callers where year = 5779 and charidy_caller_id = " . $caller['caller_id'] . ") 
                  group by donor_id";
          $result = mysql_query( $sql2 );
          $row = mysql_fetch_assoc( $result );
          $numberGave = $row['number'];

          $sql3 = "select sum(amount) as total from mashpia_charidy.donations where year != 5779 and donor_id in (
                  select donor_id from charidy_donors_callers where year = 5779 and charidy_caller_id = " . $caller['caller_id'] . ")";
          $result = mysql_query( $sql3 );
          $row = mysql_fetch_assoc( $result );
          $totalPast = $row['total'];

          $sql3 = "select sum(amount) as total from mashpia_charidy.donations where year = 5779 and donor_id in (
            select donor_id from charidy_donors_callers where year = 5779 and charidy_caller_id = " . $caller['caller_id'] . ")";
          $result = mysql_query( $sql3 );
          $row = mysql_fetch_assoc( $result );
          $totalCurrent = $row['total'];

          echo "<tr><td>" . $caller['first'] . ' ' . $caller['last'] . "</td><td>" . $numberPeople . "</td><td>" . $numberGave . "</td><td>" . 
            $totalPast . "</td><td>" . $totalCurrent . "</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </body>
</html>