<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

$TEAM_OFFSET = 145;

require_once(dirname(__FILE__)."/shared_code.php");

$columns = [
    'Chidon ID'         => "tc.th_chidon_id",
    'First Name'        => "u.first",
    'Last Name'         => "u.last",
    'Hebrew First Name' => "u.first_he",
    'Hebrew Last Name'  => "u.last_he",
    'Book'              => "tc.book",
    'Grade'             => "tc.grade",
    'Walking Zone'      => "tc.walking_zone",
    'Walk Alone'        => "tc.walk_day, tc.walk_night",
    'Host Name'         => "tc.host",
    'Host Phone Number' => "tc.host_number",
    'Host Address Number' => "tc.host_address1",
    'Host Street Name'  => "tc.host_address2",
    'Host Cross Street 1' => "tc.between_streets",
    'Host Cross Street 2' => "tc.between_streets",
    'Team Name'         => "tc.team_id",
    'Bunk Number'       => "tc.bunk_id",
    'Thursday Buses'    => "tc.coach_bus",
    'Friday Buses #'    => "tc.school_bus",
    'Bowling Lane'      => "tc.bowling_lane",
    'Test Table'        => "tc.test_table",
    'Workshop #'        => "tc.workshop_number",
    'Sunday- School Bus'=> "tc.double_decker",
    'Certificate ID'    => "tc.cert_number"
];

$headers = array_keys($columns);

if( $_POST['action'] == "generate" ) {
    outputCSVHeaders( "id_cards" );
    
    $load_info_query = mysql_query(
        "SELECT " . implode(", ", array_values($columns)) . " "
        ." FROM th_chidon tc LEFT JOIN users u USING (user_id) "
        ." WHERE u.gender = '" . $_POST['gender'] . "' "
        ." AND tc.year = '$year' "
        ." AND tc.date_paid IS NOT NULL; "
    );
    
    $csv_info = [$headers]; // add the headers to the CSV file...
    while($user_info = mysql_fetch_assoc($load_info_query)) {
        $cross_streets = explode(" and ", $user_info['between_streets']);
        $csv_info[] = [
            $user_info['th_chidon_id'], // Chidon ID
            $user_info['first'], // First Name
            $user_info['last'], // Last Name
            $user_info['first_he'], // First Name Hebrew
            $user_info['last_he'], // Last Name Hebrew
            $user_info['book'],
            $user_info['grade'],
            $user_info['walking_zone'],
            $user_info['walk_day'] ? ($user_info['walk_night'] ? "yes" : "day only") : "no", // all 3 supported options....
            $user_info['host'], // host info....
            $user_info['host_number'],
            $user_info['host_address1'],
            $user_info['host_address2'],
            $cross_streets[0],
            isset($cross_streets[1]) ? $cross_streets[1] : "",
            $user_info['team_id'] ? $teams[$user_info['team_id']] : "",
            $user_info['bunk_id'] + $TEAM_OFFSET,
            $user_info['coach_bus'],
            $user_info['school_bus'],
            $user_info['bowling_lane'],
            $user_info['test_table'],
            $user_info['workshop_number'],
            $user_info['double_decker'],
            $user_info['cert_number']
        ];
    }
    outputCSV($csv_info);
    
    //echo "<pre>";
    //print_r($csv_info);
    
} else if( $_POST['action'] === "upload" ) {
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
    
    $upload = array_map('str_getcsv', file($_FILES['id_cards']['tmp_name']));

    foreach($upload[0] as $index => $header) {
        if (trim($headers[$index]) !== trim($header)) {
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
            'tc.bunk_id'        => $row[16] + $TEAM_OFFSET, // add 60 to the bunk number to get the bunk ID
            'tc.coach_bus'      => $row[17],
            'tc.school_bus'     => $row[18],
            'tc.bowling_lane'   => $row[19],
            'tc.test_table'     => $row[20],
            'tc.workshop_number'=> $row[21],
            'tc.double_decker'  => $row[22],
            'cert_number'       => $row[23]
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
    } ?>
            </pre>
            
            <a href="index.php">Click Here to go back to the uploader....</a>
        </body>
    </html>
<?php } ?>
