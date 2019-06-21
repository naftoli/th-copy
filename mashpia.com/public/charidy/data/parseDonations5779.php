<?php

// upload cvs file and create json objects
$info = [];
if ( isset( $_FILES['donations'] ) ) {
  if ( ( $handle = fopen($_FILES['donations']['tmp_name'], "r") ) !== FALSE ) {
    while ( ( $data = fgetcsv($handle, 0, ",") ) !== FALSE ) {
      $json = json_decode( stripslashes( $data[6] ) );
      if ( !$json->donor_id ) $info[] = $json;
    }
  }
}

echo count( $info );
echo "<pre>";
// output to screen json objects
print_r( $info );
echo "</pre>";
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf8" />
  </head>
  <body>
    <form method="post" action="parseDonations5779.php" enctype="multipart/form-data">
      <input type="file" name="donations" /><br /><br />
      <input type="submit" name="submit" value="upload" />
    </form>
  </body>
</html>