<?php
header("Access-Control-Allow-Origin: *");
require '../../db.php';
require '../../class.globalSettings.php';

$year = GlobalSettings::getCurrentYear();

$donation = json_decode( $_POST['donation'] );
$id = $donation['donor_id'];
$amount = $donation['total_donation_amount'];
$datetime = $donation['date_time'];
$children = $donation['children'];
foreach ($children as $child) {
    $user_id = $child['user_id'];
    break;
}

if ($id && $amount && $datetime) {
    $sql = "insert into charidy_donations 
            set donor_id = " . $id . ",
            year = " . $year . ",
            amount = " . doubleval($amount) . ",
            donation_date = '" . $datetime . "'";
    if (isset($user_id) && $user_id) $sql .= ", user_id = " . $user_id;
    if (mysql_query( $sql )) {
        echo 0;
    } else {
        echo "error inserting into db";
    }
}
echo "error - missing info";