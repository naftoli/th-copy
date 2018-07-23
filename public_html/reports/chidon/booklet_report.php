<?php
$admin_auth = array(); 	
require_once ( __DIR__ . '/../../header.php' ); 

require_once ( __DIR__ . '/../../class.globalSettings.php' ); 
$year = GlobalSettings::getRegistrationYear();

$booklet_users = [];
$booklet_users_query = mysql_query(
    "SELECT amount, date, year, schools.school_id, school_name, logo, first, last "
    ."FROM registration_charges JOIN schools USING (school_id) "
    ."JOIN users USING (user_id) WHERE type = 'chidon' "
    ."AND year = $year ORDER BY school_name, first, last, date;"
);
while ( $row = mysql_fetch_assoc( $booklet_users_query ) ) {
    $booklet_users[$row['school_id']][] = $row;
}
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
    <h2>Base Totals</h2>
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
    <?php
        foreach( $booklet_users as $school_id => $users ) {
            $base = $users[0]; ?>
            <h2><?=$base[ 'school_name' ]?></h2>
            <table>
                <thead>
                    <tr>
                        <th>First</th>
                        <th>Last</th>
                        <th>Registered For Chidon</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach( $users as $user ) { ?>
                            <tr>
                                <td><?= $user[ 'first' ]; ?></td>
                                <td><?= $user[ 'last' ]; ?></td>
                                <td><?= ( new DateTime($user[ 'date' ]) )->format( 'm/d/Y g:i:sa e' ); ?></td>
                            </tr>
                        <?php 
                        } 
                    ?>
                </tbody>
            </table>
        <?php
        } 
    ?>
</body>
</html>