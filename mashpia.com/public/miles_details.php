<?php
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
            order by c.class_grade, c.class_sub, u.last, u.first";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $info[$school_id][$row['class_grade']][$row['class_sub']][$row['user_id']] = $row['first'] . ' ' . $row['last'];
        $userIds[] = $row['user_id'];
    }

    // get points for users
    foreach ($userIds as $id) {
        $p = new Points($id);
        $points[$id] = [
            'missions'          => $p->getMashpiaPoints($from, $to),
            'achievementCards'  => $p->getAchievementCardPoints($from, $to),
            'usedStore'         => $p->getUsedStorePoints($from, $to)
        ];
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
                padding: 5px;
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
        $users = $info[0];
        $points = $info[1];
        foreach ($users as $school => $more) {
            echo "<h2>" . $schools[$school] . "</h2>";
            echo "<p>This report is from " . $_POST['from'] . " until " . $_POST['to'] . "</p>";
            ?>
            <table>
                <tr>
                    <th>Grade</th>
                    <th>Student</th>
                    <th>Mission Points</th>
                    <th>Achievement Card Points</th>
                    <th>Points used in Store</th>
                </tr>
                <?php
                foreach ($more as $grade => $other) {
                    foreach ($other as $sub => $more) {
                        foreach ($more as $students) {
                            foreach ($students as $user_id => $name) {
                                if ($sub) $grade .= '-' . $sub;
                                echo "<tr><td>" . $grade . "</td><td>" . $name . "</td>";
                                echo "<td>" . $points[$user_id]['missions'] . "</td>";
                                echo "<td>" . $points[$user_id]['achievementCards'] . "</td>";
                                echo "<td>" . $points[$user_id]['usedStore'] . "</td></tr>";
                            }
                        }
                    }
                }
                ?>
            </table>
            <?php
        }
    }
    ?>
<?php else: ?>
<form action="miles_details.php" method="post">
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