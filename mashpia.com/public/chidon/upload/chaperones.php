<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once(dirname(__FILE__)."/shared_code.php");

$columns = [
    "Chaperone ID" => "th_chidon_chap_id",
    "First Name" => "name",
    "Last Name" => "name",
    "Phone Number" => "phone",
    "Walking Zone" => "walking_zone",
    "Host Street Number" => "acc_address",
    "Host Street" => "acc_address",
    "Host Cross Street 1" => "acc_cross_st",
    "Host Cross Street 2" => "acc_cross_st",
    "Chidon Type" => "chidon_type"
];

$headers = array_keys($columns);

if( $_POST['action'] == "generate" ) {
    $gender = $_POST['gender'];
    outputCSVHeaders( "chaperones-$gender-$year" ); // render the headers for the page to mark that we are saving a CSV file...
    
    $load_info_query = mysql_query(
        "SELECT " . implode(", ", array_values($columns)) . " "
        ." FROM th_chidon_chaps tcc "
        ." WHERE tcc.chidon_type = '$gender' "
        ." AND tcc.year = '$year' "
    );
    
    $csv_info = [$headers]; // add the headers to the CSV file...
    while($row = mysql_fetch_assoc($load_info_query)) {
        $cross_streets = explode(" and ", $row['acc_cross_st']);
        // get the first and last name...
        $name = explode(" ", $row['name']);
        $last_name = array_pop($name);
        $first_name = implode(" ", $name);
        // split up the address....
        $address = explode(" ", $row['acc_address']);
        $house_number = array_shift($address);
        $address = implode(" ", $address);
        // put it into the CSV 2D table...
        $csv_info[] = [
            $row['th_chidon_chap_id'], // Chidon ID
            $first_name, // Last Name
            $last_name,
            $row['phone'], // Last Name Hebrew
            $row['walking_zone'],
            $house_number,
            $address,
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
    
    if (!isset($_FILES['chaperones'])){
        echo "Please go back and upload a CSV file"; die();
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
<?php } ?>
