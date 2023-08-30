<?php
ini_set('display_errors', 1);
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
$year = GlobalSettings::getChidonRegYear();

if ( isset( $_POST['submit'] ) ) {
    $items = $_POST['type'];
    $purchases = [];
    $user_ids = [];
    $stmt = $MASHPIA_DB->prepare("
        SELECT 
            *
        FROM
            mashpia_purchases.purchase_details
                JOIN
            mashpia_purchases.purchases USING (purchase_id)
        WHERE
            year = :year AND item_id IN ($items)
    ");
    $stmt->execute([
        ':year' => $year
    ]);
    $rows = $stmt->fetchAll();

    $total = 0;
    $totals = [];
    if (!empty($rows)) {
        foreach ($rows as $row) {
            if (isset($purchases[$row['user_id']][$row['item_id']])) $purchases[$row['user_id']][$row['item_id']] += $row['qty'];
            else $purchases[$row['user_id']][$row['item_id']] = $row['qty'];
            $user_ids[] = $row['user_id'];
            $total += $row['qty'];
            if (isset($totals[$row['item_id']])) $totals[$row['item_id']] += $row['qty'];
            else $totals[$row['item_id']] = $row['qty'];
        }

        $info = [];
        $children = implode(',', $user_ids);
        $school_ids = implode(',', array_keys($schools));
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
        foreach ($rows as $row) {
            $info[$row['school_name']][] = $row;
        }
    }
}
$types = [];
$stmt = $MASHPIA_DB->query("
    SELECT * FROM mashpia_purchases.mivtzoim_items 
");
$rows = $stmt->fetchAll();
foreach ( $rows as $row ) {
    $types[$row['yom_tov']][$row['item']] = $row['mivtzoim_item_id'];
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
    
    <?php if ( !isset( $_POST['submit'] ) ) : ?>
        <p>Please choose which yom tov you would like to see:</p>
        <form method="post" action="">
            <select name='type'>
            <?php 
//            $i = 0; // index to find out which one is the last one and have it selected by default
            $numTypes = count( $types );
            echo $numTypes;
            foreach ( $types as $type => $details ) {
                $ids = [];
                foreach ( $details as $item => $item_id ) {
                    $ids[] = $item_id;
                }
                echo "<option value='" . implode(',', $ids) . "'";
//                if ( ++$i == $numTypes ) echo " selected ";
                echo ">" . $type . "</option>";
            }
            ?>
            </select><br /><br />
            <input type="submit" name="submit" value="submit" />
        </form>
    <?php else : ?>

        <?php if ( $admin_user['auth'] == 'super' ) : ?>
        <p class="no-print">Grand Total: <?= $total ?> purchases</p>
        <?php
        foreach ($types as $type => $details) {
            foreach ($details as $item => $item_id) {
                if (isset($totals[$item_id])) echo "<p class='no-print'>Total " . $item . " purchases: " . $totals[$item_id] . "</p>";
            }
        }
        ?>
        <?php endif; ?>

        <?php if (empty($info)) : ?>
        <h>No purchases were found</h>
        <?php else : ?>

            <?php foreach ( $info as $school => $users ) : ?>
                <h2><?= $school . ' (' . count( $users ) . ' chayolim)' ?></h2>
                <table>
                    <thead>
                        <th>Grade</th>
                        <th>Student</th>
                        <?php
                        $totals = [];
                        foreach ( $types as $type => $details ) {
                            foreach ( $details as $item => $item_id ) {
                                $chosen_items = explode(',', $items);
                                if ( in_array( $item_id, $chosen_items ) ) {
                                    $totals[$item_id] = 0;
                                    echo "<th>" . $item . "</th>";
                                }
                            }
                        }
                        ?>
                    </thead>
                    <tbody>
                        <?php foreach ( $users as $user ) : ?>
                            <tr>
                                <td><?= $user['class_grade'] . (empty( $user['class_sub'] ) ? '' : '-' . $user['class_sub']) ?></td>
                                <td><?= $user['first'] . " " . $user['last'] ?></td>
                                <?php
                                $purchase = $purchases[$user['user_id']];
                                foreach ( $types as $type => $details ) {
                                    foreach ( $details as $item => $item_id ) {
                                        $chosen_items = explode(',', $items);
                                        if ( in_array( $item_id, $chosen_items ) ) {
                                            if ( isset( $purchase[$item_id]) ) {
                                                $totals[$item_id] += $purchase[$item_id];
                                                echo "<td>" . $purchase[$item_id] . "</td>";
                                            }
                                            else echo "<td></td>";
                                        }
                                    }
                                }
                                ?>
                            </tr>
                        <?php endforeach; ?>
                        <tr><th colspan="2" align="right">Totals:</th>
                        <?php
                        foreach ( explode(",", $items) as $item_id ) {
                            echo "<th>" . $totals[$item_id] . "</th>";
                        }
                        ?>
                        </tr>
                    </tbody>
                </table>
                <div style="page-break-after: always"></div>
            <?php endforeach; ?>
        <?php endif; ?>

    <?php endif; ?>
</body>
</html>