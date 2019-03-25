<?php
ini_set('display_errors',1);
require __DIR__ . '/../../../db.php';

$info = [];
$sql = "
  SELECT 
      *
  FROM
      th_chidon tc
          JOIN
      users u USING (user_id)
  WHERE
      tc.year = 5779 AND tc.date_paid > 0
          AND u.gender = 'M'
";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $info[] = $row;
}
//echo "<pre>"; print_r( $info ); echo "</pre>";
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>ID Cards</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    @font-face {
      font-family: tramp;
      src: url('fonts/FiraCode-Regular.otf');
    }
    @font-face {
      font-family: goth;
      src: url('fonts/GOTHAM-MEDIUM.OTF');
    }
  </style>
</head>
<body>

</body>
</html>