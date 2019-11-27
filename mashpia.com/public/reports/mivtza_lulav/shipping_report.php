<?php
$admin_auth = array('school'); 	
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php'; 

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php'; 
$year = GlobalSettings::getRegistrationYear();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php'; 
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // needed for including chidon only schools
$schools = $as->getSchools();

$combined_users = [];

$users = [];
$users_sql = "SELECT users FROM mivtzoim_purchases.lulav_purchases WHERE year = " . $year;
$user_query = mysql_query( $users_sql );
while ( $row = mysql_fetch_assoc( $user_query ) ) {
    if ( strpos($row['users'], ',') ) {
        $temp = explode(',', $row['users']);
        foreach ( $temp as $user ) {
            if ( is_numeric( $user ) ) $users[] = $user;
        }
    } else {
        $users[] = $row['users'];
    }
}

$shipping_sql = "SELECT s.*, e.class_grade, e.class_sub, b.first, b.last, d.first as uFirst, d.last as uLast ";
$shipping_sql .= "FROM schools s ";
$shipping_sql .= "JOIN admin_auths a ON s.school_id = a.id ";
$shipping_sql .= "JOIN admins b USING (admin_id)  ";
$shipping_sql .= "JOIN users d ON d.school_id = s.school_id ";
$shipping_sql .= "JOIN classes e ON e.class_id = d.class_id ";
$shipping_sql .= "WHERE d.user_id IN (" . implode(',', $users) . ") ";
$shipping_sql .= "GROUP BY d.user_id ";
$shipping_sql .= "ORDER BY school_name, e.class_grade, e.class_sub, d.last, d.first";
$shipping_query = mysql_query( $shipping_sql );
while ( $row = mysql_fetch_assoc( $shipping_query ) ) {
    $combined_users[$row['school_id']][] = $row;
}
?>
<!DOCTYPE html>  
<html> 
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Chidon Combined Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/admin_styles.css" rel="stylesheet" type="text/css" />
    <style>
        table { width: 100%; }
        th, td { border: 1px solid #888; padding: 4px 8px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
    <h1 class="no-print">Lulav Shipping Report</h1>
    <?php
    foreach( $combined_users as $school_id => $users ) {
        $total = count( $users );
        $base = $users[0]; 
        $school_address = $base['first'] . ' ' . 
                          $base['last'] . "<br />" . 
                          $base['school_address1'] . ' ' . 
                          $base['school_address2'] . "<br />" . 
                          $base['school_city'] . ', ' . 
                          $base['school_state'] . ' ' . 
                          $base['school_country'] . "<br />" . 
                          $base['school_postal'];

    
    ?>
    <h2><?=$base['school_name']; ?></h2>

    <?php if ($base['lulav_shipping'] > 0) { ?>
        Shipping Type: Shipping <br /><br />
    <?php } else { ?>
        Shipping Type: Pickup <br /><br />
    <?php } ?>

    Total Sets Purchased: <?= $total ?><br /><br />
    <?=$school_address; ?><br /><br />

    <table>
        <thead>
            <tr>
                <th>Grade</th>
                <th>Name</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach( $users as $user ) { 
                $grade = $user['class_grade'];
                ?>
                <tr>
                    <td><?= $grade . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']); ?></td>
                    <td><?= $user[ 'uFirst' ] .' ' .$user[ 'uLast' ]; ?></td>
                </tr>
            <?php
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