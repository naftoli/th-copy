<?php
$admin_auth = array(); 	
require_once ( __DIR__ . '/../../header.php' ); 

require_once ( __DIR__ . '/../../class.globalSettings.php' ); 
$year = GlobalSettings::getRegistrationYear();

$main_query = mysql_query("
    SELECT school_id, count(*) AS total FROM mashpiadb.registration_charges 
    WHERE type = 'chidon' 
    AND year = $year 
    GROUP BY school_id;
");
$data = [];
$schools = [];
while( $row = mysql_fetch_assoc( $main_query ) ) {
    $data[$row['school_id']] = $row['total'];
    if ( !in_array( $row['school_id'], $schools ) ) {
        $schools[] = $row['school_id'];
    }
}

// get school names
$schoolNames = [];
$sql = "SELECT school_id, school_name FROM schools WHERE school_id IN (" . implode(',', $schools) . ")";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $schoolNames[$row['school_id']] = $row['school_name'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?=$year?> Study Guides Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/admin_styles.css" rel="stylesheet" type="text/css" />
    <style>
        table { width: 100%; }
        th, td { border: 1px solid #888; padding: 4px 8px; }
    </style>
</head>
<body>
    <?php include( __DIR__ . '/../../admin_header.php'); ?>
    <h1><?=$year?> Study Guides Report</h1>
    <h2>Details</h2>
    <table>
        <thead>
            <th>School</th>
            <th>Number of Study Guides</th>
        </thead>
        <tbody>
            <?php
                foreach( $data as $school_id => $total ) { 
                    echo "<tr><td>" . $schoolNames[$school_id] . "</td><td>" . $total . "</td></tr>";
                } 
            ?>
        </tbody>
    </table>
</body>
</html>