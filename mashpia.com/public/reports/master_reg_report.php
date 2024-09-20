<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

if ($admin_user['auth'] != 'super') {
    die('Access Denied');
}

// get all registered children in users table
$stmt = $MASHPIA_DB->query("select * from users where user_registered > 0");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $users[$row['user_id']] = $row;
}

// get all registered for years in user_registration table
$stmt = $MASHPIA_DB->prepare("select * from user_registration where year = ?");
$res = $stmt->execute([$year]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $registrations[$row['user_id']] = $row;
}

// get all schools
$stmt = $MASHPIA_DB->query("select * from schools");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $schools[$row['school_id']] = $row['school_name'];
}

// get all classes
$stmt = $MASHPIA_DB->query("select * from classes where class_era = 0");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $classes[$row['class_id']] = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
}

// get all admins
$qry = "SELECT 
            a.admin_id, aa.id  
        FROM
            admin_auths aa
                JOIN
            admins a USING (admin_id) 
        WHERE
            aa.auth = 'user'";
$stmt = $MASHPIA_DB->query($qry);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $admin_users[$row['admin_id']][] = $row['id'];
    $user_admins[$row['id']] = $row['admin_id'];
}

// get all ranks
$stmt = $MASHPIA_DB->query("select * from ranks");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $ranks[$row['rank_ord']] = $row['rank_name'];
}

// get all rank marks
$qry = "SELECT 
            user_id, MAX(rank_ord) AS rank_ord
        FROM
            rank_marks
        GROUP BY user_id";
$stmt = $MASHPIA_DB->query($qry);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $user_ranks[$row['user_id']] = $row['rank_ord'];
}

// get all chidon registered kids for 5785
$year = GlobalSettings::getChidonRegYear();
$stmt = $MASHPIA_DB->prepare("select * from th_chidon where year = ?");
$res = $stmt->execute([$year]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $chidon[$row['user_id']] = $row;
}

// get all user ids from both the users array and the registration array
$user_ids = array_unique(array_merge(array_keys($users), array_keys($registrations)));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Master Registration List</title>
    <style>
      tr, th, td {
        font-family: "Arial", sans-serif;
        font-size: 14px;
        padding: 10px;
        border-bottom: 1px solid #f2f2f2;
      }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Master Registration List</h1>
    <table>
        <tr>
            <th></th>
            <th>User ID</th>
            <th>User Serial</th>
            <th>User Name</th>
            <th>Date Registered for 5785</th>
            <th>Currently registered (Users Table)</th>
            <th>Currently registered Date</th>
            <th>Registered for Chidon</th>
            <th>School</th>
            <th>Grade</th>
            <th>Rank</th>
            <th>Family ID</th>
        </tr>
        <?php
        $totals['registered'] = 0;
        $totals['user_registered'] = 0;
        foreach ($user_ids as $idx => $user_id) {
            if (isset($users[$user_id]) && $users[$user_id]['user_registered'] > 0) {
                $totals['user_registered']++;
            }
            if (isset($registrations[$user_id])) {
                $totals['registered']++;
            }
            ?>
            <tr>
                <td><?php echo $idx + 1; ?></td>
                <td><?php echo $user_id; ?></td>
                <td><?php echo isset($users[$user_id]) ? $users[$user_id]['user_serial'] : 'Not Found'; ?></td>
                <td><?php echo isset($users[$user_id]) ? $users[$user_id]['first'] . ' ' . $users[$user_id]['last'] : 'Not Found'; ?></td>
                <td><?php echo isset($registrations[$user_id]) ? $registrations[$user_id]['reg_date'] : 'No'; ?></td>
                <td><?php echo isset($users[$user_id]) ? $users[$user_id]['user_registered'] > 0 ? 'yes' : 'no' : 'Not Found'; ?></td>
                <td><?php echo isset($users[$user_id]) ? $users[$user_id]['user_registered'] > 0 ? $users[$user_id]['user_registered'] : 'Not Registered' : 'Not Found'; ?></td>
                <td><?php echo isset($chidon[$user_id]) ? $chidon[$user_id]['reg_date'] : 'No'; ?></td>
                <td><?php echo isset($users[$user_id]['school_id']) ? $schools[$users[$user_id]['school_id']] : 'Not in a School'; ?></>
                <td><?php echo isset($users[$user_id]['class_id']) ? $classes[$users[$user_id]['class_id']] : 'Not in a Grade'; ?></td>
                <td><?php echo isset($user_ranks[$user_id]) ? $ranks[$user_ranks[$user_id]] : 'Not Found'; ?></td>
                <td><?php echo isset($user_admins[$user_id]) ? $user_admins[$user_id] : 'No Family Account Found'; ?></td>
            </tr>
            <?php
        }
        ?>
        <tr>
            <th>Totals:</th>
            <td><?php echo count($user_ids); ?></td>
            <td colspan="2"></td>
            <td><?php echo $totals['registered']; ?></td>
            <td><?php echo $totals['user_registered']; ?></td>
            <td colspan="5"></td>
        </tr>
    </table>
</body>
</html>