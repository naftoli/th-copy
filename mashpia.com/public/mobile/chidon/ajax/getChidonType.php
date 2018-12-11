<?php
require '../../../db.php';
require '../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$user_id = $_POST['user_id'];
$sql = "select contestant, school_rep from th_chidon where user_id = " . $user_id . " and year = " . $year;
//echo $sql;
$result = mysql_query( $sql );
$row = mysql_fetch_assoc( $result );

if ($row['school_rep']) echo "school representative";
else if ($row['contestant']) echo "contestant";
else echo 0;