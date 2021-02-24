<?php
require '../../../db.php';
require '../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$user_id = $_POST['user_id'];
$types = ['shabbaton_maven', 'shabbaton_pro', 'shabbaton_expert', 'shabbaton_trophy'];
$fields = implode("','", $types);
$sql = "select '$fields' from th_chidon where user_id = " . $user_id . " and year = " . $year;
//echo $sql;
$result = mysql_query( $sql );
$row = mysql_fetch_assoc( $result );

foreach ($types as $type) {
    if ($row[$type]) {
        echo $type;
        exit;
    }
}
echo 0;