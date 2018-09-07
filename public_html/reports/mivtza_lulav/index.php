<?php
$admin_auth = array(); 	
require_once ( __DIR__ . '/../../header.php' ); 
require_once ( __DIR__ . '/../../class.globalSettings.php' ); 
$year = GlobalSettings::getRegistrationYear();

$children = [];
$query = mysql_query("SELECT users FROM lulav_purchases WHERE year = $year");
while ( $row = mysql_fetch_assoc( $query ) ) {
    if ( strpos($row['users'], ',') !== false ) {
        $users = explode(',', $row['users']);
        foreach ( $users as $id ) {
            $children[] = intval( $id );
        }
    } else {
        $children[] = intval( $row['users'] );
    }
}
//echo "<pre>"; print_r( $children ); echo "</pre>"; exit;

$info = [];
$sql = "SELECT u.first, u.last, c.class_grade, c.class_sub, s.school_name 
        FROM users u 
        JOIN classes c ON c.class_id = u.class_id 
        JOIN schools s ON s.school_id = u.school_id 
        WHERE u.user_id in (" . implode(',', $children) . ") 
        ORDER BY school_name, class_grade, class_sub, last, first";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $info[$row['school_name']][] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?=$year?> Lulav Purchases</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/admin_styles.css" rel="stylesheet" type="text/css" />
    <style>
        table { width: 100%; }
        th, td { border: 1px solid #888; padding: 4px 8px; }
    </style>
</head>
<body>
    <?php include( __DIR__ . '/../../admin_header.php'); ?>
    <h1><?=$year?> Lulav Purchases</h1>
    <?php foreach ( $info as $school => $users ) : ?>
        <h2><?= $school . ' (' . count( $users ) . ')' ?></h2>
        <table>
            <thead>
                <th>Grade</th>
                <th>Student</th>
            </thead>
            <tbody>
                <?php foreach ( $users as $user ) : ?>
                    <tr>
                        <td><?= $user['class_grade'] . (empty( $user['class_sub'] ) ? '' : '-' . $user['class_sub']) ?></td>
                        <td><?= $user['first'] . " " . $user['last'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
</body>
</html>