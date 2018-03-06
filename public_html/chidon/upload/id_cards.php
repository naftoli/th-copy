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

if (!isset($_FILES['id_cards'])){
    echo "Please go back and upload a CSV file"; die();
}

$teams = [];
$team_query = mysql_query("SELECT * FROM th_chidon_teams;");
while($team = mysql_fetch_assoc($team_query)){
    $teams[$team['team']] = $team['team_id'];
}

$upload = array_map('str_getcsv', file($_FILES['id_cards']['tmp_name']));

$headers = ['Chidon ID', 'First Name', 'Last Name', 'Hebrew First Name',
            'Hebrew Last Name', 'Book', 'Grade', 'Walking Zone',
            'Walk Alone',   'Host Name',    'Host Phone Number',    'Host  Address Number',
            'Host Street Name', 'Host Cross Street 1',  'Host Cross Street 2', 'Team Name',
            'Bunk Number',  'Thursday Buses ',  'Friday Buses #',
            'Bowling Lane', 'Test Table', 'Workshop #',   'Sunday- School Bus'];
            
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
        'u.first'           => $row[1],
        'u.last'            => $row[2],
        'u.first_he'        => $row[3],
        'u.last_he'         => $row[4],
        'tc.book'           => $row[5],
        'tc.grade'          => $row[6],
        'tc.walking_zone'   => $row[7],
        'tc.walk_day'       => in_array($row[8], ['yes', 'day only'])   ? "1" : "0",
        'tc.walk_night'     => in_array($row[8], ['yes', 'night only']) ? "1" : "0",
        'tc.host'           => $row[9],
        'tc.host_number'    => $row[10],
        'tc.host_address1'  => $row[11],
        'tc.host_address2'  => $row[12],
        'tc.between_streets'=> $row[13] . " and " . $row[14],
        'tc.team_id'        => $teams[$row[15]],
        'tc.bunk_id'        => $row[16] + 60, // add 60 to the bunk number to get the bunk ID
        'tc.coach_bus'      => $row[17],
        'tc.school_bus'     => $row[18],
        'tc.bowling_lane'   => $row[19],
        'tc.test_table'     => $row[20],
        'tc.workshop_number'=> $row[21],
        'tc.double_decker'  => $row[22]
    ];
    $updates[$row[0]] = $update_info;
}

foreach($updates as $th_chidon_id => $info) {
    $update_sql = "UPDATE th_chidon tc JOIN users u USING (user_id) SET ";
    
    $columns = [];
    foreach ($info as $column => $value) {
        $value = mysql_real_escape_string($value);
        if( $value ) $columns[] = " $column = '$value' ";
    }
    $update_sql .= implode(", ", $columns);
    
    $update_sql .= " WHERE th_chidon_id = '$th_chidon_id'";
    
    //echo $update_sql."\n\n";
    
    $status = mysql_query($update_sql);
    
    if(!$status){
        echo $th_chidon_id." - Failed. Please double check\n";
    }
}
?>
        </pre>
        
        <a href="index.php">Click Here to go back to the uploader....</a>
    </body>
</html>