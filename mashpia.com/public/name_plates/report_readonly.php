<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$admin_auth = ['school'];
require_once '../header.php';
require_once '../api/header/db.php';
require_once '../class.adminSchools.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$super = $admin_user['auth'] == 'super';

$info = [];
$sql = "SELECT * FROM name_plates p 
        JOIN users u ON p.user_id = u.user_id
        JOIN classes c ON u.class_id = c.class_id";
if (!$super) {
    $sql .= " WHERE p.school_id IN (" . implode(',', array_keys($schools)) . ")";
}
$sql .= " ORDER BY p.school_id, c.class_grade, c.class_sub, u.last, u.first";
$stmt = $MASHPIA_DB->query($sql);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $info[$row['school_id']][] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Name Plates Report (Read Only)</title>
    <link href="../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
        .infobox {
            line-height: 1.5;
        }
        tr, th, td {
            padding: 5px;
            font-size: 12px;
            border-bottom: 1px solid #ccc;
        }
        .reason {
            font-size: 10px;
        }
    </style>
</head>
<body>
    <?php include '../admin_header.php'; ?>
    <h1>Name Plates Report (Read Only)</h1>
    <table>
        <thead>
            <tr>
                <th>School</th>
                <th>Class</th>
                <th>Serial</th>
                <th>Child</th>
                <th>Qty</th>
                <th>Shipped</th>
                <th>Hebrew Name</th>
                <th>Reason</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($info as $school_id => $rows) { ?>
                <?php foreach ($rows as $row) { ?>
                    <?php
                    $he_name = $row['first_he'] . ' ' . $row['last_he'];
                    // check if there's dbl quote in string
                    if (strpos($he_name, '"') !== false) {
                        $he_name = str_replace('"', '&quot;', $he_name);
                    }
                    ?>
                    <tr>
                        <td><?php echo $schools[$school_id]; ?></td>
                        <td><?php echo $row['class_grade'] . ($row['class_sub'] ? '-' . $row['class_sub'] : ''); ?></td>
                        <td><?php echo $row['user_serial']; ?></td>
                        <td><?php echo $row['first'] . ' ' . $row['last']; ?></td>
                        <td><?php echo $row['qty']; ?></td>
                        <td><?php echo intval($row['shipped']) ? 'Yes' : 'No'; ?></td>
                        <td><?php echo $he_name; ?></td>
                        <td class="reason"><?php echo $row['reason']; ?></td>
                    </tr>
                <?php } ?>
            <?php } ?>
        </tbody>
    </table>
</body>
</html> 