<?php
require '../../../db.php'; 

$sql = "select count(*) as sold from lulav_purchases";
$result = mysql_query( $sql );
$row = mysql_fetch_assoc( $result );

echo $row['sold'];