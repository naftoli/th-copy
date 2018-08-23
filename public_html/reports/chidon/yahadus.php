<?php
$admin_auth = array(); 	
require_once ( __DIR__ . '/../../header.php' ); 

require_once ( __DIR__ . '/../../class.globalSettings.php' ); 
$year = GlobalSettings::getRegistrationYear();

$booklet_users = [];
$qry = "SELECT amount, date, year, schools.school_id, school_name, logo, first, last, c.class_grade, c.class_sub "
    ."FROM registration_charges rc JOIN schools USING (school_id) "
    ."JOIN users USING (user_id) " 
    ."JOIN classes c ON c.class_id = users.class_id " 
    ."WHERE type = 'yahadus' " 
    ."AND year = $year ";
// limit to dates if limit exists
if (isset($_POST['fromDate']) && $_POST['fromDate'] && isset($_POST['toDate']) && $_POST['toDate']) {
    $from = mysql_real_escape_string( $_POST['fromDate'] );
    $to = mysql_real_escape_string( $_POST['toDate'] );
    $qry .= "AND rc.date >= '" . $from . " 00:00:00' AND rc.date <= '" . $to . " 23:59:59' ";
}
$qry .= "ORDER BY school_name, first, last, date";
//echo $qry;
$booklet_users_query = mysql_query( $qry );
while ( $row = mysql_fetch_assoc( $booklet_users_query ) ) {
    $booklet_users[$row['school_id']][] = $row;
}

$booklets = [
    4   =>  1,
    5   =>  2,
    6   =>  3, 
    7   =>  4, 
    8   =>  5
];

$booklet_grand_totals = [
    1   =>  0,
    2   =>  0,
    3   =>  0, 
    4   =>  0, 
    5   =>  0
];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Chidon Booklet Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/admin_styles.css" rel="stylesheet" type="text/css" />
    <style>
        table { width: 100%; }
        th, td { border: 1px solid #888; padding: 4px 8px; }
    </style>
</head>
<body>
    <?php include( __DIR__ . '/../../admin_header.php'); ?>
    <h1>Chidon Booklet Report</h1>
    <form action="yahadus.php" method="post">
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
    <?php
        foreach( $booklet_users as $school_id => $users ) {
            $booklet_totals = [
                1   =>  0,
                2   =>  0,
                3   =>  0, 
                4   =>  0, 
                5   =>  0
            ];
            $base = $users[0]; ?>
            <h2><?=$base[ 'school_name' ]?></h2>
            <table>
                <thead>
                    <tr>
                        <th>First</th>
                        <th>Last</th>
                        <th>Grade</th>
                        <th>Book #</th>
                        <th>Registered For Chidon</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach( $users as $user ) { 
                            $grade = $user['class_grade'];
                            //if ( !$schoolTransitioned ) $grade++;
                            ?>
                            <tr>
                                <td><?= $user[ 'first' ]; ?></td>
                                <td><?= $user[ 'last' ]; ?></td>
                                <td><?= $grade . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']); ?></td>
                                <td><?= $booklets[$grade]; ?></td>
                                <td><?= ( new DateTime($user[ 'date' ]) )->format( 'm/d/Y g:i:sa e' ); ?></td>
                            </tr>
                            <?php 
                            // totals per school
                            $booklet_totals[$booklets[$grade]]++;
                            // grand totals
                            $booklet_grand_totals[$booklets[$grade]]++;
                        }
                    ?>
                </tbody>
            </table>
            <h2>Booklet Totals for <?=$base['school_name'];?></h2>
            <table>
                <tr>
                    <th>Booklet #</th>
                    <th>Total</th>
                </tr>
                <?php
                ksort( $booklet_totals );
                foreach ( $booklet_totals as $booklet => $total ) {
                    echo "<tr><td>" . $booklet . "</td><td>" . $total . "</td></tr>";
                }
                ?>
            </table>
            <div style="page-break-after: always;"></div>
        <?php
        } 
    ?>
    <h2>Totals</h2>
    <table>
        <thead>
            <tr>
                <th>Base</th>
                <th># of Booklets</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach( $booklet_users as $school_id => $users ) {
                $base = $users[0]; ?>
                <tr>
                    <td><?= $base[ 'school_name' ]; ?></td>
                    <td><?= count( $users ); ?></td>
                </tr>
            <?php 
            } ?>
        </tbody>
    </table>
    <h2>Grand Totals</h2>
    <table>
        <tr>
            <th>Booklet #</th>
            <th>Grand Total</th>
        </tr>
        <?php
        ksort( $booklet_grand_totals );
        foreach ( $booklet_grand_totals as $booklet => $total ) {
            echo "<tr><td>" . $booklet . "</td><td>" . $total . "</td></tr>";
        }
        ?>
    </table>
</body>
</html>