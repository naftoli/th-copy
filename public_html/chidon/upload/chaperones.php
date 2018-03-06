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

if (!isset($_FILES['chaperones'])){
    echo "Please go back and upload a CSV file"; die();
}

$teams = [];
$team_query = mysql_query("SELECT * FROM th_chidon_teams;");
while($team = mysql_fetch_assoc($team_query)){
    $teams[$team['team']] = $team['team_id'];
}

$upload = array_map('str_getcsv', file($_FILES['chaperones']['tmp_name']));

$headers = [
    'Chaperone ID',	'First Name',	'Last Name',	'Phone Number',
    'Walking Zone',	'Host Street Number',	'Host Street',	'Host Cross Street 1',	'Host Cross Street 2'
];
            
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
        'name'          => $row[1] ? $row[1] . " " . $row[2] : "",
        'phone'         => $row[3],
        'walking_zone'  => $row[4],
        'acc_address'   => $row[5] ? $row[5] . " " . $row[6] : "",
        'acc_cross_st'  => $row[7] ? $row[7] . " and " . $row[7] : ""
    ];
    $updates[$row[0]] = $update_info;
}

foreach($updates as $chap_id => $info) {
    $update_sql = "UPDATE th_chidon_chaps SET ";
    
    $columns = [];
    foreach ($info as $column => $value) {
        $value = mysql_real_escape_string($value);
        if( $value ) $columns[] = " $column = '$value' ";
    }
    $update_sql .= implode(", ", $columns);
    
    $update_sql .= " WHERE th_chidon_chap_id = '$chap_id'";
    
    //echo $update_sql."\n";
    
    $status = mysql_query($update_sql);
    
    if(!$status){
        echo $chap_id." - Failed. Please double check\n";
    }
}
?>
        </pre>
        
        <a href="index.php">Click Here to go back to the uploader....</a>
    </body>
</html>