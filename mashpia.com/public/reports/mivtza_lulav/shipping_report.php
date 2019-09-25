<?php
$admin_auth = array('school'); 	
require_once ( __DIR__ . '/../../header.php' ); 

require_once ( __DIR__ . '/../../class.globalSettings.php' ); 
$year = GlobalSettings::getRegistrationYear();

require_once ( __DIR__ . '/../../class.adminSchools.php' ); 
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // needed for including chidon only schools
$schools = $as->getSchools();

$combined_users = [];

$shipping_sql = "SELECT DISTINCT a.school_name, c.first, c.last, a.school_address1, a.school_address2, a.school_city, a.school_state, ";
$shipping_sql .= "a.school_country, a.school_postal, a.lulav_shipping, d.users ";
$shipping_sql .= "FROM schools a ";
$shipping_sql .= "INNER JOIN admin_auths b ON a.school_id = b.id ";
$shipping_sql .= "INNER JOIN admins c ON c.admin_id = b.id ";
$shipping_sql .= "INNER JOIN lulav_purchases d ON d.admin_id = b.id ";
$shipping_sql .= "WHERE b.position = 'Base Commander' AND d.year = $year ";
$shipping_sql .= "AND a.school_id in (" . implode(',', array_keys($schools)) . ") ";
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
    </style>
</head>
<body>
    <?php include( __DIR__ . '/../../admin_header.php'); ?>
    <h1>Lulav Shipping Report</h1>
    <form action="combined.php" method="post">
        <input type="submit" name="submit" value="Refresh Report" />
    </form>
    <div style="page-break-after: always;"></div>
    <?php
        foreach( $combined_users as $school_id => $users ) {
            $base = $users[0]; 
            $school_address = $base['shipping_first'] . ' ' . $base['shipping_last'] . "<br />" . $base['shipping_address1'] . ' ' . $base['shipping_address2'] . "<br />" . 
                $base['shipping_city'] . ', ' . $base['shipping_state'] . ' ' . $base['shipping_postal'] . "<br />" . $base['shipping_country'];
            ?>
            <h2><?=$base[ 'school_name' ]?></h2>
            Shipping Type: <?= $base['shipping_method'] ?><br /><br />
            <?= $school_address ?><br /><br />
            Base Commander: <?= $base['first'] .' '.['last'] ?>
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
                            //if ( !$schoolTransitioned ) $grade++;
                            ?>
                            <tr>
                                <td><?= $grade . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']); ?></td>
                                <td><?= $user[ 'first' ] .' ' .$user[ 'last' ]; ?></td>
                            </tr>
                        }
                    ?>
                </tbody>
            </table>
            <div style="page-break-after: always;"></div>
            <h2>Total Study Guides for <?=$base['school_name'];?></h2>

            <br /><br />
            Shipping Type: <?= $base['shipping_method'] ?><br /><br />
            <?= $school_address ?><br /><br />
            Base Commander: <?= $base['first'] .' '.['last'] ?>
            <div style="page-break-after: always;"></div>
        <?php
        } 
    ?>
</body>
</html>