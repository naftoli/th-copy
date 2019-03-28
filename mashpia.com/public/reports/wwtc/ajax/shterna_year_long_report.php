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
     "SELECT grid_id, done_qty as quantity, user_id, sm_date as mark_date "
    ." FROM tehillim_backups "
    ." WHERE grid_id IN ( 8001, 8002 ) AND sm_date >= " . $dates['start']
);

$tehillim_mission_query = mysql_query(
     " SELECT SUM( mission_count ) AS total FROM date_tasks_mission_marks WHERE subject_id = 1 "
    ." AND mark_date >= " . $dates['start']
);

$tehillim_medal_query = mysql_query(
    " SELECT COUNT(*) AS total FROM medal_marks WHERE subject_id = 1 "
   ." AND date_awarded >= " . $dates['start']
);

// go through all the marks and get the following stats
$total_kapitalach = 0;
$kapitalach = [];

$total_minutes = 0;
$minutes = [];

$total_missions = mysql_fetch_assoc( $tehillim_mission_query )['total'];
$total_medals   = mysql_fetch_assoc( $tehillim_medal_query   )['total'];

$chayolim = [];

$sm = calculateSM( GlobalSettings::getCurrentYear() );

function getSM( $date, $sm ){
    $months = [
        'Tishrei', 'Cheshvon', 'Kislev', 'Teves', 'Shevat', 'Adar I',
        'Adar II', 'Nissan', 'Iyar', 'Sivan', 'Tammuz', 'Av', 'Elul'
    ];

    foreach( $sm as $index => $sm_date ){
        if ( $sm_date > $date ) return $months[ $index - 1 ];
    }
    return end( $months );
}

while( $tehillim_info = mysql_fetch_assoc( $tehillim_info_query ) ) {
    // $tehillim_info['mark_date'] = jdtojewish( $tehillim_info['mark_date'] );
    $tehillim_info['mark_date'] = getSM( $tehillim_info['mark_date'], $sm );

    if ( $tehillim_info['grid_id'] == '8001' && $tehillim_info['quantity'] <= 150 ){
        $total_kapitalach += $tehillim_info['quantity'];
        if ( isset( $kapitalach[ $tehillim_info['mark_date'] ] ) )
            $kapitalach[ $tehillim_info['mark_date'] ] += $tehillim_info['quantity'];
        else
            $kapitalach[ $tehillim_info['mark_date'] ] = $tehillim_info['quantity'];
    } else if ( $tehillim_info['grid_id'] == '8002' && $tehillim_info['quantity'] <= 240 ){
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
    "total_missions" => $total_missions,
    "total_medals"   => $total_medals
]);