<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

$admin_auth = array('school');
require ( $_SERVER['DOCUMENT_ROOT'].'/header.php' );
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <h1>Error Log:</h1>
        <pre>
<?php

if (!isset($_FILES['bunks'])){
    echo "Please go back and upload a CSV file"; die();
}

$teams = [];
$team_query = mysql_query("SELECT * FROM th_chidon_teams;");
while($team = mysql_fetch_assoc($team_query)){
    $teams[$team['team']] = $team['team_id'];
}

$upload = array_map('str_getcsv', file($_FILES['bunks']['tmp_name']));

$headers = ['Bunk ID',  'Bunk Name',    'Counselor First Name',
            'Counselor Last Name', 'Counselor Number', 'Counselor Thursday Bus',
            'Counselor Friday Bus', 'Counselor Sunday Bus', 'Grade', 'Walking Zone',
            'Host Address Number', 'Host Street', 'Host Cross Street 1', 'Host Cross Street 2'];
            
foreach($upload[0] as $index => $header) {
    if ($headers[$index] !== $header) {
        echo "INVALID HEADER: Expected ".$headers[$index].". Recived $header\n"
            ."Please go back and upload a VALID CSV file.\n"
            ."If the file you uploaded is valid please contact the programming department."; die();
    }
}

$updates = [];

foreach($upload as $index => $row) {
    if($index === 0) continue; // skip the first row (the headers)
    // cast the info to the correct collumns in the DBS....
    $update_info = [
        'bunk_name'             => $row[1],
        'counselor'             => $row[2] ? $row[2] . " " . $row[3] : "",
        'c_number'              => $row[4],
        'c_coach_bus'           => $row[5],
        'c_school_bus'          => $row[6],
        'c_double_decker'       => $row[7],
        'grade'                 => $row[8],
        'walking_zone'          => $row[9],
        'host_address1'         => $row[10],
        'host_address2'         => $row[11],
        'host_between_streets'  => $row[12] ? $row[12] . " and " . $row[13] : ""
    ];
    $updates[$row[0]] = $update_info;
}

foreach($updates as $bunk_id => $info) {
    $update_sql = "UPDATE th_chidon_bunks SET ";
    $columns = [];
    foreach ($info as $column => $value) {
        $value = mysql_real_escape_string($value);
        if( $value ) $columns[] = " $column = '$value' ";
    }
    $update_sql .= implode(", ", $columns);
    $update_sql .= " WHERE bunk_id = '$bunk_id'";
    
    //echo $update_sql."\n";
    
    $status = mysql_query($update_sql);
    
    if(!$status){
        echo $update_sql."\n";
        echo $bunk_id." - Failed. Please double check\n";
    }
}
?>
        </pre>
        
        <a href="index.php">Click Here to go back to the uploader....</a>
    </body>
</html>