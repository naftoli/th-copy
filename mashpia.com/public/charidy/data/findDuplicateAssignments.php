<?php
require_once '../../db.php';

$info = [];
$sql = "select donor_id from charidy_donors_callers 
        where year = 5779 
        and charidy_caller_id = 21";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $info[] = $row['donor_id'];
}

$sql = "select donor_id from charidy_donors_callers 
        where year = 5779 
        and donor_id in (" . implode(',', $info) . ") 
        and charidy_caller_id != 21";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  echo $row['donor_id'] . "<br />";
}