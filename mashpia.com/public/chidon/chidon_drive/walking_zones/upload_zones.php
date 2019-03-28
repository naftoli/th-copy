<?php
ini_set('display_errors', 1);
require  __DIR__ . '/../../../db.php';

function sort_array( $info ) {
  $data = [];
  foreach ( $info as $value ) {
    if ( !in_array( $value, $data ) ) $data[] = $value;
  }
  sort( $data );
  return $data;
}

if (($handle = fopen("zones.csv", "r")) !== FALSE) {
  $qrys = [];
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $i = 0;
    $even_numbers = $data[$i++];
    $odd_numbers = $data[$i++];
    $street = $data[$i++];
    $cross1 = $data[$i++];
    $cross2 = $data[$i++];
    $zone = $data[$i++];

    $even_numbers_array = !empty( $even_numbers ) ? explode('-', $even_numbers) : null;
    $odd_numbers_array = !empty( $odd_numbers ) ? explode('-', $odd_numbers) : null;

    $even_start = isset( $even_numbers_array[0] ) ? $even_numbers_array[0] : 0;
    $even_end = isset( $even_numbers_array[1] ) ? $even_numbers_array[1] : 0;
    $odd_start = isset( $odd_numbers_array[0] ) ? $odd_numbers_array[0] : 0;
    $odd_end = isset( $odd_numbers_array[1] ) ? $odd_numbers_array[1] : 0;

    $qrys[] = "insert into chidon_walking_zones 
              set zone = " . $zone . ", 
              street = '" . $street . "', 
              cross1 = '" . $cross1 . "', 
              cross2 = '" . $cross2 . "', 
              even_start = " . $even_start . ", 
              even_end = " . $even_end . ", 
              odd_start = " . $odd_start . ", 
              odd_end = " . $odd_end;
  }
  //echo "<pre>"; print_r( $qrys ); echo "</pre>";
  foreach ( $qrys as $line => $qry ) mysql_query( $qry ) or die( "Error on line: " . $line . "<br />" . $qry . "<br />" . mysql_error() );
  fclose($handle);
}