<?php
ini_set('display_errors',1);
require __DIR__ . '/../../db.php';

$info = [];
$sql = "select th_chidon_id, grade, class_grade 
        from th_chidon 
        join users using (user_id) 
        join classes using (class_id) 
        where year = 5779";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $info[] = $row;
}

$qrys = [];
foreach ( $info as $row ) {
  if ( $row['grade'] != $row['class_grade'] ) {
    $book = intval( $row['class_grade'] ) - 3;
    $qrys[] = "update th_chidon 
              set grade = '" . $row['class_grade'] . "', 
              book = '" . $book . "' 
              where th_chidon_id = " . $row['th_chidon_id'];
  }
}

//echo "<pre>"; print_r( $qrys ); echo "</pre>"; exit;
$updated = 0;
foreach ( $qrys as $qry ) {
  if ( mysql_query( $qry ) ) $updated++;
}
echo "Updated: " . $updated;