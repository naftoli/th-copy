<?php
$admin_auth = array(); 	
require_once ( __DIR__ . '/../../header.php' ); 

require_once ( __DIR__ . '/../../class.globalSettings.php' ); 
$year = GlobalSettings::getRegistrationYear();

// get the totals
$totals = [];
$total_query = mysql_query(
    "SELECT type, SUM( amount ) AS total FROM registration_charges WHERE year = $year GROUP BY type;"
);
$grand_total = 0;
while ( $row = mysql_fetch_assoc( $total_query ) ) {
    $grand_total += intval( $row['total'] );
    $totals[] = $row;
}

// get the details
$details = [];
$detail_query = mysql_query(
    "SELECT s.school_name, s.school_number, u.user_serial, u.first, u.last, rc.type, rc.date, rc.year, rc.amount "
    ."FROM registration_charges rc LEFT JOIN schools s USING ( school_id ) "
    ."LEFT JOIN users u USING ( user_id ) LEFT JOIN transactions t USING ( trans_id ) "
    ."WHERE year = $year ORDER BY rc.date DESC, school_name, u.first, u.last, rc.amount;"
);
while ( $row = mysql_fetch_assoc( $detail_query ) ) $details[] = $row;
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
        #details { font-size: 14px; }
    </style>
</head>
<body>
    <?php include( __DIR__ . '/../../admin_header.php'); ?>
    <h1><?=$year?> Soldier Registration Charges</h1>
    <h2>Totals</h2>
    <table>
        <thead>
            <th>Registration Type</th>
            <th>Total Recived</th>
        </thead>
        <tbody>
            <?php
                foreach( $totals as $total ) { ?>
                <tr>
                    <td><?= $total['type'] ?></td>
                    <td>$<?=number_format( intval( $total['total'] ) ) ?></td>
                </tr>
            <?php } ?>
            <tr>
                <th>Grand Total</th>
                <th>$<?=number_format( $grand_total ) ?></th>
            </tr>
        </tbody>
    </table>
    <h2>Details</h2>
    <table id='details'>
        <thead>
            <th colspan='2'>Base</th>
            <th>Serial Number</th>
            <th>Name</th>
            <th>Registration Type</th>
            <th>Registration Time</th>
            <th>Amount Paid</th>
        </thead>
        <tbody>
            <?php
                foreach( $details as $user ) { ?>
                <tr>
                    <td><?= $user['school_number'] ?></td>
                    <td><?= $user['school_name'] ?></td>
                    <td><?= $user['user_serial'] ?></td>
                    <td><?= $user['first'] . " " . $user['last'] ?></td>
                    <td><?= $user['type'] ?></td>
                    <td><?= ( new DateTime($user[ 'date' ]) )->format( 'm/d/Y g:i:sa e' ); ?></td>
                    <td>$<?= $user['amount'] ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>