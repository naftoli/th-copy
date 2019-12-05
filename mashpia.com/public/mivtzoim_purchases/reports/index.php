<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
$year = GlobalSettings::getCurrentYear();

if ( isset( $_POST['submit'] ) ) {
    switch ( $_POST['type'] ) {
        case 'Lulav':
            $items = [ 1 ];
            break;
        case 'Chanuka':
            $items = [ 2, 3 ];
            break;
    }
    $items = implode(',', $items);
}
$items = implode(',', [ 2, 3 ]);

$purchases = [];
$user_ids = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT 
        *
    FROM
        mivtzoim_purchases.purchase_details
            JOIN
        mivtzoim_purchases.purchases USING (purchase_id)
    WHERE
        year = :year AND item_id IN ($items)
");
$stmt->execute([
    ':year' => $year
]);
$rows = $stmt->fetchAll();

$total = 0;
foreach ( $rows as $row ) {
    $purchases[$row['user_id']][$row['item_id']] = $row['qty'];
    $user_ids[] = $row['user_id'];
    $total += $row['qty'];
}

$info = [];
$children = implode(',', $user_ids);
$school_ids = implode(',', array_keys( $schools ));
$stmt = $MASHPIA_DB->query("
    SELECT u.user_id, u.first, u.last, c.class_grade, c.class_sub, s.school_name 
    FROM users u 
    JOIN classes c ON c.class_id = u.class_id 
    JOIN schools s ON s.school_id = u.school_id 
    WHERE u.user_id in ($children) 
    AND u.school_id in ($school_ids) 
    ORDER BY school_name, class_grade, class_sub, last, first
");
$rows = $stmt->fetchAll();
foreach ( $rows as $row ) {
    $info[$row['school_name']][] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?=$year?> Mivtzoim Purchases</title>
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
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
    <h1 class="no-print"><?=$year?> Mivtzoim Purchases</h1>
    <?php if ( $admin_user['auth'] == 'super' ) : ?>
    <p class="no-print">Grand Total: <?= $total ?> purchases</p>
    <?php endif; ?>
    <?php foreach ( $info as $school => $users ) : ?>
        <h2><?= $school . ' (' . count( $users ) . ')' ?></h2>
        <table>
            <thead>
                <th>Grade</th>
                <th>Student</th>
                <th>Menorah</th>
                <th>Brochure</th>
            </thead>
            <tbody>
                <?php foreach ( $users as $user ) : ?>
                    <tr>
                        <td><?= $user['class_grade'] . (empty( $user['class_sub'] ) ? '' : '-' . $user['class_sub']) ?></td>
                        <td><?= $user['first'] . " " . $user['last'] ?></td>
                        <?php
                        $purchase = $purchases[$user['user_id']];
                        if ( isset( $purchase[2]) ) echo "<td>" . $purchase[2] . "</td>";
                        else echo "<td></td>";
                        if ( isset( $purchase[3]) ) echo "<td>" . $purchase[2] . "</td>";
                        else echo "<td></td>";
                        ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div style="page-break-after: always"></div>
    <?php endforeach; ?>
</body>
</html>