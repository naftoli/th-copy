<?php
// AUTHENTICATION
$admin_auth = array('school');
require ( $_SERVER['DOCUMENT_ROOT'].'/header.php' );

if( $admin_user['auth'] !== "super" ) {
    echo "Permission Denied. Admins only"; die();
}

// CONSTANTS
require_once ( $_SERVER['DOCUMENT_ROOT'].'/class.globalSettings.php' );
$year = GlobalSettings::getChidonYear();

$teams = [];
$teams_by_id = [];
$team_query = mysql_query("SELECT * FROM th_chidon_teams;");
while($team = mysql_fetch_assoc($team_query)){
    $teams[$team['team']] = $team['team_id'];
    $teams_by_id[$team['team_id']] = $team['team'];
}

// FUNCTIONS
function outputCSV($data) {
    echo "\xEF\xBB\xBF"; // Allow For UNICODE / Hebrew output....
    $output = fopen("php://output", "w");
    foreach ($data as $row)
        fputcsv($output, $row); // here you can change delimiter/enclosure
    fclose($output);
}

function outputCSVHeaders( $file_name = "mashpia-download") {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=$file_name.csv");
    header('Pragma: no-cache');
    header("Expires: 0");
}

function create_columns( $info ) { // $info is an array of columns and unescaped values....
    $colunms = [];
    
    foreach ($info as $column => $value) {
        $value = mysql_real_escape_string($value);
        if( !!$value ) array_push($colunms, " $column = '$value' ");
    }
    
    //print_r($colunms);
    
    return $colunms;
}