<?php
ini_set('display_errors',1);
$admin_auth = ['school']; 	
require_once ( __DIR__ . '/../../header.php' ); 
require_once ( __DIR__ . '/../../class.globalSettings.php' ); 
$year = GlobalSettings::getRegistrationYear();

require_once ( __DIR__ . '/../../class.adminSchools.php' ); 
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$children = [];
$query = mysql_query("select user_id from mashpia_purchases.purchase_details
                      join mashpia_purchases.purchases using (purchase_id)
                      where item_id = 1 and year = $year");
if ( mysql_num_rows($query) > 0 ) {
    while ($row = mysql_fetch_assoc($query)) {
        $children[] = $row['user_id'];
    }
}
$total = count( $children );

$info = [];
if ($total) {
    $sql = "SELECT u.user_id, u.first, u.last, c.class_grade, c.class_sub, s.school_name 
            FROM users u 
            JOIN classes c ON c.class_id = u.class_id 
            JOIN schools s ON s.school_id = u.school_id 
            WHERE u.user_id in (" . implode(',', $children) . ") 
            AND u.school_id in (" . implode(',', array_keys($schools)) . ") 
            ORDER BY school_name, class_grade, class_sub, last, first";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $info[$row['school_name']][] = $row;
    }
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
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php include( __DIR__ . '/../../admin_header.php'); ?>
    <h1 class="no-print"><?=$year?> Lulav Purchases</h1>
    <?php if ( $admin_user['auth'] == 'super' ) : ?>
    <p class="no-print">Grand Total: <?= $total ?></p>
    <?php endif; ?>
    <?php foreach ( $info as $school => $users ) : ?>
        <?php
        $num_users = count($users);
        if ( array_search($school, $schools) == 4 ) $num_users++; // add michel rapoport order to yehuda munitz order
        ?>
        <h2><?= $school . ' (' . $num_users . ')' ?></h2>
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
                    <?php
                    // for yehuda munitz he should show up 2 times (also for michel rapoport)
                    if ( $user['user_id'] == 15418 ) {
                        ?>
                        <tr>
                            <td><?= $user['class_grade'] . (empty( $user['class_sub'] ) ? '' : '-' . $user['class_sub']) ?></td>
                            <td><?= $user['first'] . " " . $user['last'] ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div style="page-break-after: always"></div>
    <?php endforeach; ?>
</body>
</html>