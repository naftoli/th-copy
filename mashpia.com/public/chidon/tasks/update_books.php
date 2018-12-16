<?php
ini_set('max_execution_time', 300);
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$year = GlobalSettings::getChidonYear();
$info = [];

$sql = "select th_chidon_id, grade from th_chidon where year = " . $year;
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $info[] = $row;
}

foreach ( $info as $row ) {
  if ( $row['grade'] ) {
    switch ( $row['grade'] ) {
      case 4:
        $book = 1;
        break;
      case 5:
        $book = 2;
        break;
      case 6:
        $book = 3;
        break;
      case 7:
        $book = 4;
        break;
      case 8:
        $book = 5;
        break;
    }
    if ( $row['book'] != $book ) {
      $sql = "update th_chidon set book = " . $book . " where th_chidon_id = " . $row['th_chidon_id'];
      mysql_query( $sql );
    }
  }
}
echo "done";