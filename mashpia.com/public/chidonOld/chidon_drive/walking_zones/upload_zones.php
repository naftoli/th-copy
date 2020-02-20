<?php
ini_set('display_errors', 1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

if (($handle = fopen("zones2.csv", "r")) !== FALSE) {
  $qrys = [];
  // $fields = ['chidon_walking_zone_id','zone_5780','street','st_label','cross_label','cross1','cross2','even_start','even_end','odd_start','odd_end'];
  $fields = ['zone_5780','street','st_label','cross_label','cross1','cross2','even_start','even_end','odd_start','odd_end'];
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $i = 0;
    foreach ($fields as $field) {
      $$field = $data[$i++];
    }

    // if ($chidon_walking_zone_id) {
    //   $qrys[] = "update chidon_walking_zones 
    //             set zone_5780 = '" . $zone_5780 . "', 
    //             st_label = '" . $st_label . "', 
    //             cross_label = '" . $cross_label . "'  
    //             where chidon_walking_zone_id = " . $chidon_walking_zone_id;
    // } else {
      $qrys[] = "insert into chidon_walking_zones 
                set zone_5780 = '" . $zone_5780 . "', 
                st_label = '" . $st_label . "', 
                cross_label = '" . $cross_label . "', 
                street = '" . $street . "', 
                cross1 = '" . $cross1 . "', 
                cross2 = '" . $cross2 . "', 
                even_start = " . $even_start . ", 
                even_end = " . $even_end . ", 
                odd_start = " . $odd_start . ", 
                odd_end = " . $odd_end;
    // }
  }
  // echo "<pre>"; print_r( $qrys ); echo "</pre>"; exit;
  foreach ( $qrys as $line => $qry ) mysql_query( $qry ) or die( "Error on line: " . $line . "<br />" . $qry . "<br />" . mysql_error() );
  fclose($handle);
}
echo "done.";