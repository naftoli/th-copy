<?php
$admin_auth = array(); 	
require_once ( __DIR__ . '/../../header.php' ); 

require_once ( __DIR__ . '/../../class.globalSettings.php' ); 
$year = GlobalSettings::getRegistrationYear();

$qry = "SELECT rc.date, u.first, u.last, s.school_id, s.school_name, c.class_grade 
        FROM registration_charges rc 
        JOIN users u USING (user_id) 
        JOIN schools s ON s.school_id = u.school_id 
        JOIN classes c ON c.class_id = u.class_id 
        WHERE type = 'yahadus' ";
// limit to dates if limit exists
if (isset($_POST['fromDate']) && $_POST['fromDate'] && isset($_POST['toDate']) && $_POST['toDate']) {
    $from = mysql_real_escape_string( $_POST['fromDate'] );
    $to = mysql_real_escape_string( $_POST['toDate'] );
    $qry .= "AND rc.date >= '" . $from . " 00:00:00' AND rc.date <= '" . $to . " 23:59:59' ";
}
$qry .= "ORDER BY school_name";
$main_query = mysql_query( $qry );

$data = [];
$schools = [];
while( $row = mysql_fetch_assoc( $main_query ) ) {
    $data[$row['school_id']][] = $row;
    $schools[$row['school_id']] = $row['school_name'];
}

$books = [
    4   => 1,
    5   => 2,
    6   => 3,
    7   => 4,
    8   => 5
];

$grand_totals = [
    1   => 0,
    2   => 0,
    3   => 0,
    4   => 0,
    5   => 0
];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?=$year?> Yahadus Book Purchases</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/admin_styles.css" rel="stylesheet" type="text/css" />
    <style>
        table { width: 100%; }
        th, td { border: 1px solid #888; padding: 4px 8px; }
    </style>
</head>
<body>
    <?php include( __DIR__ . '/../../admin_header.php'); ?>
    <h1><?=$year?> Yahadus Book Purchases</h1>
    <form action="yahadus_books.php" method="post">
        <p>
            To have report based on dates, choose starting and ending dates and then click "Refresh Report"
        </p>  
        <p>  
            From Date: <input type="date" name="fromDate" /> 
            To Date: <input type="date" name="toDate" />
        </p>
        <input type="submit" name="submit" value="Refresh Report" />
    </form>
    <div style="page-break-after: always;"></div>

    <h2>Yahadus Book Purchase Details</h2>
    <table>
        <thead>
            <th>School</th>
            <th>Student</th>
            <th>Grade</th>
            <th>Book #</th>
        </thead>
        <tbody>
            <?php
                foreach( $data as $school_id => $info ) {
                    $totals[$school_id] = [
                        1   => 0,
                        2   => 0,
                        3   => 0,
                        4   => 0,
                        5   => 0
                    ];
                    foreach ( $info as $base ) { 
                        $grade = $base['class_grade'];
                        $book = $books[$base['class_grade']];
                        echo "<tr><td>" . $base['school_name'] . "</td><td>" . $base['first'] . ' ' . $base['last'] . "</td><td>" . $grade . "</td><td>" . $book . "</td></tr>";
                        // add to base and army totals
                        $totals[$school_id][$book]++;
                        $grand_totals[$book]++;
                    }       
                } 
            ?>
        </tbody>
    </table>
    <h2>Grand Totals</h2>
    <table>
        <thead>
            <th>Book #</th>
            <th>Total</th>
        </thead>
        <tbody>
            <?php
                foreach ( $grand_totals as $book => $total ) {
                    echo "<tr><td>" . $book . "</td><td>" . $total . "</td></tr>";
                }
            ?>
        </tbody>
    </table>
    <div style="page-break-after: always;"></div>
    
    <?php
        foreach ( $totals as $school => $info ) {
            echo "<h2>" . $schools[$school] . "</h2>";
            ?>
            <table>
                <thead>
                    <th>Book #</th>
                    <th>Total</th>
                </thead>
                <tbody>
                    <?php
                        foreach ( $info as $book => $total ) {
                            echo "<tr><td>" . $book . "</td><td>" . $total . "</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
            <div style="page-break-after: always;"></div>
            <?php
        }
    ?>
</body>
</html>