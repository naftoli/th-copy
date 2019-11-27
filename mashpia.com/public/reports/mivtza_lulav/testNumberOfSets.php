<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$info = '';
$sql = "select * from mivtzoim_purchases.lulav_purchases where year = 5780";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $info .= ',' . $row['users'];
}
echo "<pre>"; print_r( explode(',', $info ) ); echo "</pre>";