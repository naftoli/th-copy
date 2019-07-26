<?php
require 'db.php';
$info = [];
$sql = "SELECT * FROM mashpiadb.lines_learned where campaign_id in (13,14)";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $info[$row['campaign_id']][$row['user_id']][] = $row['lines_learned'];
}

$duplicates = [];
foreach ( $info as $campaign => $other ) {
  foreach ( $other as $user => $more ) {
    $total = count( $more );
    if ( $total > 1 ) {
      $duplicates[$campaign][$user] = $more[$total - 1];
    }
  }
}

echo "<pre>"; print_r( $duplicates ); echo "</pre>";
foreach ( $duplicates as $campaign => $other ) {
  foreach ( $other as $user => $amount ) {
    $sql = "delete from lines_learned where campaign_id = " . $campaign . " and user_id = " . $user;
    if ( mysql_query( $sql ) ) {
      $sql2 = "insert into lines_learned set campaign_id = " . $campaign . ", user_id = " . $user . ", lines_learned = " . $amount;
      mysql_query( $sql2 );
    }
  }
}
echo "done.";