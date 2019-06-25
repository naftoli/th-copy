<?php
chdir('files');
$info = [];

// create json objects from files
if ( ( $handle = fopen("Donations.csv", "r") ) !== FALSE ) {
  while ( ( $data = fgetcsv($handle, 0, ",") ) !== FALSE ) {
    $row = [];
    foreach ( $data as $column ) {
      $json = json_decode( stripslashes( $column ) );
      $row[] = $json;
    }
    $ref_id = $row[0];
    $json_id = $row[1]->get_data;
    //if ( !$json->donor_id ) $info[] = $json;
    $info[$ref_id] = $json_id;
  }
}

if ( ( $handle = fopen("KidsTH.csv", "r") ) !== FALSE ) {
  while ( ( $data = fgetcsv($handle, 0, ",") ) !== FALSE ) {
    $row = [];
    foreach ( $data as $column ) {
      $json = json_decode( stripslashes( $column ) );
      $row[] = $json;
    }
  }
}

// echo count( $info );
echo "<pre>";
// output to screen json objects
print_r( $donations );
echo "</pre>";