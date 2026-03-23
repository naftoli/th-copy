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

// get all chidon registered kids 
// $year = GlobalSettings::getChidonYear();
$stmt = $MASHPIA_DB->prepare("select * from th_chidon where year = ?");
$res = $stmt->execute([$year]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $chidon[$row['user_id']] = $row;
}

// get all accounting info for registered users
$stmt = $MASHPIA_DB->prepare("select * from registration_charges where type in ('THE', 'LDE') and year = ?");
$res = $stmt->execute([$year]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $charges[$row['type']][$row['user_id']] = $row['amount'];
}

// get all user ids from both the users array and the registration array and chidon array
$user_ids = array_unique(array_merge(array_keys($users), array_keys($registrations), array_keys($chidon)));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Master Registration List</title>
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" />
    <style>
      body {
        font-family: sans-serif;
        font-size: 12px;
        padding-left: 3%;
        padding-right: 3%;
      }
      tr, th, td {
        padding: 10px;
        font-font: "Arial", sans-serif;
        font-size: 14px;
      }
    </style>
</head>
<body>
    <h1>Master Registration List</h1>
    <table id="table" class="table table-striped table-condensed cell-border hover row-order order-column">
        <thead>
        <tr>
            <th>&nbsp;</th>
            <th>User ID</th>
            <th>User Serial</th>
            <th>User Name</th>
            <th>Date Registered for <?= $year ?></th>
            <th>Currently registered Date (Users Table)</th>
            <th>Registration Payment</th>
            <th>Registered for Chidon</th>
            <th>Chidon Payment</th>
            <th>School</th>
            <th>Grade</th>
            <th>Rank</th>
            <th>Family ID</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $totals['registered'] = 0;
        $totals['user_registered'] = 0;
        $totals['chidon_registered'] = 0;
        foreach ($user_ids as $num => $user_id) {
            if (isset($users[$user_id]) && $users[$user_id]['user_registered'] > 0) {
                $totals['user_registered']++;
            }
            if (isset($registrations[$user_id])) {
                $totals['registered']++;
            }
            if (isset($chidon[$user_id])) {
                $totals['chidon_registered']++;
            }
            ?>
            <tr>
                <td>&nbsp;</td>
                <td><?php echo htmlspecialchars($user_id); ?></td>
                <td><?php echo isset($users[$user_id]) ? htmlspecialchars($users[$user_id]['user_serial']) : 'Not Found'; ?></td>
                <td><?php echo isset($users[$user_id]) ? htmlspecialchars($users[$user_id]['first'] . ' ' . $users[$user_id]['last']) : 'Not Found'; ?></td>
                <td><?php echo isset($registrations[$user_id]) ? htmlspecialchars($registrations[$user_id]['reg_date']) : 'No'; ?></td>
                <td><?php echo isset($users[$user_id]) ? ($users[$user_id]['user_registered'] > 0 ? htmlspecialchars($users[$user_id]['user_registered']) : 'Not Registered') : 'Not Found'; ?></td>
                <td><?php echo isset($charges['THE'][$user_id]) ? htmlspecialchars($charges['THE'][$user_id]) : 'Not Found'; ?></td>
                <td><?php echo isset($chidon[$user_id]) ? htmlspecialchars($chidon[$user_id]['reg_date']) : 'No'; ?></td>
                <td><?php echo isset($charges['LDE'][$user_id]) ? htmlspecialchars($charges['LDE'][$user_id]) : 'Not Found'; ?></td>
                <td><?php echo isset($users[$user_id]['school_id']) ? htmlspecialchars($schools[$users[$user_id]['school_id']]) : 'Not in a School'; ?></td>
                <td><?php echo isset($users[$user_id]['class_id']) ? htmlspecialchars($classes[$users[$user_id]['class_id']]) : 'Not in a Grade'; ?></td>
                <td><?php echo isset($user_ranks[$user_id]) ? htmlspecialchars($ranks[$user_ranks[$user_id]]) : 'Not Found'; ?></td>
                <td><?php echo isset($user_admins[$user_id]) ? htmlspecialchars($user_admins[$user_id]) : 'No Family Account Found'; ?></td>
            </tr>
            <?php
        }
        ?>
        <tr>
            <td>Totals:</td>
            <td><?php echo count($user_ids); ?></td>
            <td></td>
            <td></td>
            <td><?php echo $totals['registered']; ?></td>
            <td><?php echo $totals['user_registered']; ?></td>
            <td></td>
            <td><?php echo $totals['chidon_registered']; ?></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        </tbody>
    </table>
</body>
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script>
  $('#table').DataTable({
    paging: false
  });
</script>
</html>