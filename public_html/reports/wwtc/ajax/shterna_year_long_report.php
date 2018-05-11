<?php
header('Content-type: application/json');
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/class.globalSettings.php');

if ($admin_user['auth'] != 'super') {
    echo json_encode(["success" => false, "error" => "Invalid Account"]);
    die();
}

$dates = GlobalSettings::getCurYearDates();

// 8001 = quota, 8002 = minutes
$tehillim_info_query = mysql_query(
     "SELECT grid_id, done_qty as quantity, user_id, mark_date "
    ." FROM tehillim_backups "
    ." WHERE grid_id IN ( 8001, 8002 ) AND mark_date >= " . $dates['start']
);

// go through all the marks and get the following stats
$total_kapitalach = 0;
$kapitalach = [];

$total_minutes = 0;
$minutes = [];

$chayolim = [];

while( $tehillim_info = mysql_fetch_assoc( $tehillim_info_query ) ) {
    $tehillim_info['mark_date'] = jdtojewish( $tehillim_info['mark_date'] );
    if ( $tehillim_info['grid_id'] == '8001' ){
        $total_kapitalach += $tehillim_info['quantity'];
        if ( isset( $kapitalach[ $tehillim_info['mark_date'] ] ) )
            $kapitalach[ $tehillim_info['mark_date'] ] += $tehillim_info['quantity'];
        else
            $kapitalach[ $tehillim_info['mark_date'] ] = $tehillim_info['quantity'];
    } else if ( $tehillim_info['grid_id'] == '8002' ){
        $total_minutes += $tehillim_info['quantity'];
        if ( isset( $minutes[ $tehillim_info['mark_date'] ] ) )
            $minutes[ $tehillim_info['mark_date'] ] += $tehillim_info['quantity'];
        else
            $minutes[ $tehillim_info['mark_date'] ] = $tehillim_info['quantity'];
    }
    // add the info to the chayol
    $chayolim[ $tehillim_info['user_id'] ] = $tehillim_info['user_id'];
}

echo json_encode([
    "kapitalach" => $kapitalach,
    "total_kapitalach" => $total_kapitalach,
    "minutes" => $minutes,
    "total_minutes" => $total_minutes,
    "total_chayolim" => count($chayolim),
]);