<?php
$admin_auth = array(); 	
require_once ( __DIR__ . '/../../header.php' ); 

require_once ( __DIR__ . '/../../class.globalSettings.php' ); 
$year = GlobalSettings::getRegistrationYear();

$main_query = mysql_query(
    "SELECT s.school_id, s.school_name, !ISNULL(sr.type) as live, sr.date_paid, sr.amount_paid, total, "
    ."total_registered, not_chayolei, classes_unconfirmed "
    ."FROM schools s LEFT JOIN school_registrations sr USING (school_id) "
    ."JOIN ( "
        ."SELECT  school_id, COUNT(*) AS total FROM users GROUP BY school_id "
    .") u USING (school_id) JOIN ( "
        ."SELECT school_id, COUNT(*) AS not_chayolei FROM users WHERE yan = 1 OR chidon = 1 GROUP BY school_id"
    .") nc USING (school_id) LEFT JOIN ("
        ."SELECT school_id, COUNT(*) AS total_registered FROM user_registration WHERE year = $year GROUP BY school_id"
    .") ur USING (school_id) LEFT JOIN ("
        ."SELECT school_id, COUNT(*) AS classes_unconfirmed FROM classes WHERE confirmed = 0 GROUP BY school_id"
    .") c USING (school_id) WHERE sr.year = $year OR sr.year IS NULL GROUP BY school_id"
);
$data = [];
while( $row = mysql_fetch_assoc( $main_query ) ) $data[] = $row;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?=$year?> Registration Charges</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/admin_styles.css" rel="stylesheet" type="text/css" />
    <style>
        table { width: 100%; }
        th, td { border: 1px solid #888; padding: 4px 8px; }
    </style>
</head>
<body>
    <?php include( __DIR__ . '/../../admin_header.php'); ?>
    <h1><?=$year?> Base Registration Status</h1>
    <h2>Details</h2>
    <table>
        <thead>
            <th>Base</th>
            <th>Registered</th>
            <th>Amount Paid</th>
            <th>Soldiers Registered</th>
            <th>Non-Chayolei Soldiers</th>
            <th>Confirmed Platoon Info</th>
        </thead>
        <tbody>
            <?php
                foreach( $data as $base ) { 
                    if ( !$base['total_registered'] ) $base['total_registered'] = 0; 
                    if ( $base['total'] == 1 && $base['not_chayolei'] == 1) continue; ?>
                    <tr>
                        <td><?= $base['school_name'] ?></td>
                        <td><?= $base[ 'date_paid' ] ? 
                            ( new DateTime($base[ 'date_paid' ]) )->format( 'm/d/Y g:i:s' ) : 
                            ( $base['live'] ? 'Not Registered' : 'Registration Not Live' ); 
                        ?></td>
                        <td>$<?= number_format($base['amount_paid'], 0) ?></td>
                        <td><?= $base['total_registered'] .'/'. ( $base['total'] - $base['not_chayolei'] ) ?></td>
                        <td><?= $base['not_chayolei'] ?></td>
                        <td><?= $base['classes_unconfirmed'] ? 'No' : 'Yes' ?></td>
                    </tr>
                <?php 
                } 
            ?>
        </tbody>
    </table>
</body>
</html>