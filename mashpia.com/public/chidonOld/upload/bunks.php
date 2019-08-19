<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once(dirname(__FILE__)."/shared_code.php");

$columns = [
    'Bunk ID'               => "bunk_id",
    'Bunk Name'             => "bunk_name",
    'Counselor First Name'  => "counselor",
    'Counselor Last Name'   => "counselor",
    'Counselor Number'      => "c_number",
    'Counselor Thursday Bus'=> "c_coach_bus",
    'Counselor Friday Bus'  => "c_school_bus",
    'Counselor Sunday Bus'  => "c_double_decker",
    'Grade'                 => "grade",
    'Walking Zone'          => "walking_zone",
    'Host Address Number'   => "host_address1",
    'Host Street'           => "host_address2",
    'Host Cross Street 1'   => "host_between_streets",
    'Host Cross Street 2'   => "host_between_streets",
    'Chidon Type'           => "chidon_type",
];

$headers = array_keys($columns);

if( $_POST['action'] == "generate" ) {
    outputCSVHeaders( "bunks" ); // render the headers for the page to mark that we are saving a CSV file...
    
    $load_info_query = mysql_query(
        "SELECT " . implode(", ", array_values($columns)) . " "
        ." FROM th_chidon_bunks tcb "
        ." WHERE tcb.chidon_type = '" . $_POST['gender'] . "' "
        ." AND tcb.year = '$year' "
    );
    
    $csv_info = [$headers]; // add the headers to the CSV file...
    while($row = mysql_fetch_assoc($load_info_query)) {
        $cross_streets = explode(" and ", $row['host_between_streets']);
        // get the first and last name...
        $name = explode(" ", $row['counselor']);
        $last_name = array_pop($name);
        $first_name = implode(" ", $name);
        // put it into the CSV 2D table...
        $csv_info[] = [
            $row['bunk_id'], // Chidon ID
            $row['bunk_name'], // First Name
            $first_name, // Last Name
            $last_name,
            $row['c_number'], // Last Name Hebrew
            $row['c_coach_bus'],
            $row['c_school_bus'],
            $row['c_double_decker'],
            $row['grade'],
            $row['walking_zone'],
            $row['host_address1'],
            $row['host_address2'],
            $cross_streets[0],
            isset($cross_streets[1]) ? $cross_streets[1] : "",
            $row['chidon_type']
        ];
    }
    outputCSV($csv_info);
    
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

    if (!isset($_FILES['bunks'])){
        echo "Please go back and upload a CSV file"; die();
    }

    $upload = array_map('str_getcsv', file($_FILES['bunks']['tmp_name']));
            
    foreach($upload[0] as $index => $header) {
        if ($headers[$index] !== $header) {
            echo "INVALID HEADER: Expected ".$headers[$index].". Recived $header\n"
                ."Please go back and upload a VALID CSV file.\n"
                ."If the file you uploaded is valid please contact the programming department."; die();
        }
    }
    
    $updates = [];
    $inserts = [];

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
            'host_between_streets'  => $row[12] ? $row[12] . " and " . $row[13] : "",
            'chidon_type'           => $row[14] == "boys" ? "boys" : "girls"
        ];
        
        if( $row[0] )
            $updates[$row[0]] = $update_info;
        else
            $inserts[] = $update_info;
    }

    foreach($updates as $bunk_id => $info) {
        $update_sql = "UPDATE th_chidon_bunks SET ";
        $columns = create_columns( $info );
        $update_sql .= implode(", ", $columns);
        $update_sql .= " WHERE bunk_id = '$bunk_id'";
        
        //echo $update_sql."\n";
        
        $status = mysql_query($update_sql);
        
        if(!$status){
            echo $update_sql."\n";
            echo $bunk_id." - Failed. Please double check\n";
        }
    }
    
    foreach( $inserts as $info ) {
        $insert_sql = "INSERT INTO th_chidon_bunks SET ";
        //print_r($info);
        $columns = create_columns( $info );
        $insert_sql .= implode(", ", $columns);
        $insert_sql .= ", year='$year'";
        
        $status = mysql_query($insert_sql);
        
        if(!$status){
            echo $insert_sql."\n";
            echo "Create new bunk - Failed. Please double check Sheet and/or contact programming department.... \n";
        }
    }
    ?>
            </pre>
            
            <a href="index.php">Click Here to go back to the uploader....</a>
        </body>
    </html>
<?php } ?>