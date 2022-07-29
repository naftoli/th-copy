<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = array('school');
require('header.php');
require 'class.adminSchools.php';
require_once 'class.points.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

function notValid($info, $needed) {
    foreach ($needed as $field) {
        if (!$info[$field] || empty($info[$field])) {
            return "All fields must be filled in!";
        }
    }
    return false;
}

function getInfo($params) {
    $school_id = mysql_real_escape_string($params['school']);
    $from = mysql_real_escape_string($params['from']);
    $to = mysql_real_escape_string($params['to']);

    // init vars
    $info = [];
    $userIds = [];
    $points = [];

    // get users
    $sql = "select u.user_id, u.first, u.last, c.class_grade, c.class_sub 
            from users u 
            join classes c on c.class_id = u.class_id 
            where u.school_id = $school_id 
            order by c.class_grade, c.class_sub, u.last, u.first";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $info[$school_id][$row['class_grade']][$row['class_sub']][$row['user_id']] = $row['first'] . ' ' . $row['last'];
        $userIds[] = $row['user_id'];
    }

    // get points for users
    foreach ($userIds as $id) {
        $p = new Points($id);
//        $p->setDebugOn();
        $points[$id]['missions'] = $p->getMissionHistory($from, $to);
        $points[$id]['points'] = $p->getPointsHistory($from, $to);
    }

    return [
        'users'     => $info,
        'points'    => $points
    ];
}

if (isset($_POST['submit'])) {
    $msg = notValid($_POST, ['school', 'from', 'to']);
    if (!$msg) {
        $from = new DateTime($_POST['from']);
        $to = new DateTime($_POST['to']);
        if ($to < $from) $msg = "FROM date cannot be BEFORE the TO date!";
    }
}

$resource_types = [
    'admin_users_manual'        => 'Base Commander Update',
    'direct_transfer'           => 'Base Commander Update',
    'scratch_card'              => 'Scratch Card',
    'specific achievement card' => 'Achievement Card',
    'store'                     => 'Store Purchase',
    'transaction_manager_store' => 'Store Purchase Reversal'
]
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <link href="admin_styles.css" rel="stylesheet" type="text/css">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Detailed Miles Report</title>
    <style type='text/css'>
        @media print {
            table {
                page-break-after: always;
            }
            .no-print {
                display: none;
            }
        }
        @media all {
            th, td {
                padding: 10px;
                font-size: 12px;
                border-bottom: 1px solid grey;
            }
        }
    </style>
</head>

<?php include('admin_header.php'); ?>
<h1 class="no-print">Detailed Miles Report</h1>
<?php if (isset($_POST['submit'])) : ?>
    <?php
    if (isset($msg) && !empty($msg)) {
        echo "<div style='color: red'>";
        echo $msg;
        echo "</div>";
    } else {
        $info = getInfo($_POST);
        $users = $info['users'];
        $points = $info['points'];
        foreach ($users as $school => $more) {
            echo "<h2>" . $schools[$school] . "</h2>";
            foreach ($more as $grade => $other) {
                foreach ($other as $sub => $more) {
                    foreach ($more as $user_id => $name) {
                        echo "<h2></h2>";
                        echo "Grade: " . ($grade . ($sub ? '-' . $sub : '')) . "<br />";
                        echo "Name: " . $name . "<br />";
                        echo "This report is from " . $_POST['from'] . " until " . $_POST['to'] . "<br /><br />";
                        $missionPoints = $points[$user_id]['missions'];
                        if (!empty($missionPoints)) {
                            $total = 0;
                            echo "<table>";
                            ?>
                            <caption>Task Points</caption>
                            <tr>
                                <th>Date</th>
                                <th>Task</th>
                                <th>Points</th>
                                <th>Total</th>
                            </tr>
                            <?php
                            foreach ($missionPoints as $date => $details) {
                                foreach ($details as $task) {
                                    $total += 0.5;
                                    echo "<tr><td>" . $date . "</td><td>" . $task['short_name'] . "</td><td>0.5</td><td>" .
                                            $total . "</td></tr>";
                                }
                            }
                            echo "</table><br /><br />";
                        } else {
                            echo "No Tasks Done.<br /><br />";
                        }
                        $otherPoints = $points[$user_id]['points'];
                        if (!empty($otherPoints)) {
                            $total = 0;
                            echo "<table>";
                            ?>
                            <caption>Other Points</caption>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Points</th>
                                <th>Total</th>
                            </tr>
                            <?php
                            foreach ($otherPoints as $date => $details) {
                                foreach ($details as $item) {
                                    $total += floatval($item['points']);
                                    echo "<tr><td>" . $date . "</td><td>" . $resource_types[$item['resource_name']] .
                                        "</td><td>" . $item['points'] . "</td><td>" . $total . "</td></tr>";
                                }
                            }
                            echo "</table><br/><br />";
                        } else {
                            echo "No Other Points.<br /><br />";
                        }
                    }
                }
            }
        }
    }
    ?>
<?php else: ?>
<form action="miles_history.php" method="post">
    <?php
    if (count($schools) > 1) {
        echo "<select name='school'>";
        echo "<option value='0'>Select school</option>";
        foreach ($schools as $school_id => $school_name) {
            echo "<option value='$school_id'>$school_name</option>";
        }
        echo "</select><br /><br />";
    } else {
        $key = key($schools);
        echo "<input type='hidden' name='school' value='$key' />";
    }
    ?>
    Choose Dates:<br /><br />
    From: <input type="date" name="from" /> <br />
    To: <input type="date" name="to" /><br /><br />
    <input type="submit" name="submit" value="Submit" />
</form>
<?php endif; ?>
</body>
</html>