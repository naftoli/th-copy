<?php
$admin_auth = ['school'];
require_once '../../header.php';
require_once '../../class.adminSchools.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$info = [];
$sql = "select a.admin_email, u.first, u.last, c.class_grade, c.class_sub  
        from admins a 
        join admin_auths aa using (admin_id) 
        join users u on u.user_id = aa.id 
        join classes c on c.class_id = u.class_id 
        where aa.auth = 'user' 
        and u.school_id in (" . implode(',', array_keys( $schools )) . ") 
        and u.user_registered > 0 
        order by c.class_grade, c.class_sub";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $info[$row['class_grade']][$row['class_sub']][] = $row;
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf8" />
    <style>
      body {
        font-size: 18px;
      }
    </style>
  </head>
  <body>
  <?php
  foreach ( $info as $grade => $more ) {
    foreach ( $more as $sub => $other ) {
      echo "<br /><br /><br /><br /><br /><br /><h1>For Grade: " . $grade;
      if ( $sub != '' ) echo '-' . $sub;
      echo "</h1>";
      echo "<div style='page-break-after: always'></div>";
      foreach ( $other as $row ) {
      ?>
  <br /><br /><br /><br />
  B"H
  <br /><br />
  Dear Mommy and Tatty,
  <br /><br />
Help me win a dollar from the Rebbe!
<br /><br />
It’s really simple. On Rosh Chodesh Sivan (June 4), please go to Charidy.com/TH and make a donation in my honor. Make sure to use this email address <b><?=$row['admin_email']?></b> so that my name will be entered into the raffle.
<br /><br />
Thank you for all your support for Tzivos Hashem, so I (and my fellow soldiers) can be true chayolim of the Rebbe and all together, we can fulfill our mission of bringing Moshiach, now.
<br /><br />
Love,
<br /><br />
______________________<br />
<b><?=$row['first'] . ' ' . $row['last']?></b>
<div style="page-break-after: always;"></div>
    <?php
      }
    }
  }
  ?>
  </body>
</html>