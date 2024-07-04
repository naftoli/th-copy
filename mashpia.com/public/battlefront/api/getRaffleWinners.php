<?php
$admin_auth = ['school'];
require_once( $_SERVER["DOCUMENT_ROOT"] . '/header.php' );

// make sure only super admins can access
if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

require_once( $_SERVER["DOCUMENT_ROOT"] . '/raffles/shared/classes/Raffle.php' );
// namespace fixing
use raffles\weekly\Raffle as Raffle; // use the raffle from its namespace

$info = json_decode(file_get_contents('php://input'), true);
$raffle_type = '';
if ($info['raffle_type'] == '5') $raffle_type = 'weekly';
else if ($info['raffle_type'] == '60') $raffle_type = 'monthly';
$start_date = $info['start'];
$end_date = $info['end'];

$raffles = [];
$sql = "SELECT 
            *
        FROM
            raffles
        WHERE
            type = '$raffle_type' 
                AND date_ran > 0 
                AND show_for_hq = 1 
                AND run_date >= '$start_date' 
                AND run_date <= '$end_date' 
                AND show_for_hq = 1 
        ORDER BY date_ran DESC";
$raffle_query = mysql_query($sql);
while($raffle_info = mysql_fetch_assoc($raffle_query)){
    $raffle = Raffle::loadFromRow($raffle_info);
    $winners_info = $raffle->get_winner_info(false, false);
    $raffle_from = explode(' ', iconv('WINDOWS-1255', 'UTF-8', jdtojewish($raffle->start_date, true, CAL_JEWISH_ADD_GERESHAYIM)));
    $raffle_from = $raffle_from[0] . ' ' . $raffle_from[1];

    $raffle_to = explode(' ', iconv('WINDOWS-1255', 'UTF-8', jdtojewish($raffle->end_date, true, CAL_JEWISH_ADD_GERESHAYIM)));
    $raffle_to = $raffle_to[0] . ' ' . $raffle_to[1];

    $raffles[] = [
        "raffle_id"     => $raffle->raffle_id,
        "raffle_name"   => $raffle->name,
        "raffle_type"   => $raffle->type,
        "raffle_from"   => $raffle_from,
        "raffle_to"     => $raffle_to,
        "winners"       => $winners_info
    ]; // add the info to the array
}

echo json_encode($raffles);