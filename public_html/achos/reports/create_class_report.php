<?php
ini_set('display_errors',1);
$admin_auth = array('school'); 
require('../header.php');

//echo "<pre>"; print_r($_POST); echo "</pre>";
foreach ($_POST as $k => $v) {
    $_POST[$k] = mysql_real_escape_string($v);
}
$start = $_POST['from'];
$end = $_POST['to'];

$subjects = array();
if (isset($_POST['hoo'])) $subjects[] = 2;
if (isset($_POST['fc'])) $subjects[] = 3;
if (isset($_POST['personal'])) $subjects[] = 4;

$data = array();
if (isset($_POST['visits'])) $data[] = 'visits';
if (isset($_POST['points'])) $data[] = 'points';
if (isset($_POST['minutes'])) $data[] = 'minutes';

$sortby = $_POST['sortby'];

// build sql based on options chosen
// get parshos
$parshos = array();
$sql = "select * from parshos where start >= " . $start . " and end <= " . $end;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $parshos[] = $row;
}

// get users signed up to chosen subjects
$users = array();
$sql = "select * from users u
        join user_tracks ut using (user_id)
        join classes c on u.class_id = c.class_id 
        where ut.subject_id in (" . implode(',', $subjects) . ")
        and u.school_id = " . $admin_user['auths']['school'][0] . "
        order by class_grade, class_sub, last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}
//echo "<pre>"; print_r($users); echo "</pre>";
$userInfo = array();
$sql = "select u.user_id, u.first, u.last, c.class_grade, c.class_sub, ut.subject_id 
        from classes c
        join users u using (class_id)
        join user_tracks ut using (user_id) 
        where u.user_id in (" . implode(',', $users) . ")";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $userInfo[$row['user_id']]['name'] = $row['first'] . ' ' . $row['last'];
    $userInfo[$row['user_id']]['class'] = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
    switch ($row['subject_id']) {
        case 2:
            $subject = "Hoo";
            break;
        case 3:
            $subject = "FC";
            break;
        case 4:
            $subject = "Personal";
            break;
    }
    if (!isset($subject)) continue;
    $userInfo[$row['user_id']]['subject'] = $subject;
}

$info = array();
require '../mobile/classes/hoo.php';
foreach ($users as $user) {
    $h = new Hoo($user);
    foreach ($parshos as $parsha) {
        foreach ($data as $val) {
            $fn = 'calc' . ucwords($val);
            $info[$user][$parsha['name']][$val] = $h->$fn($parsha['start'], $parsha['end']);
        }
    }
}
//echo "<pre>"; print_r($info); echo "</pre>";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Class Report</title>
        <link href="../admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            th {
                vertical-align: text-top;
            }
            th, td {
                border: none !important;
                padding: 5px;
                font-size: 12px;
            }
        </style>
    </head>
    
    <body>
        <?php include('../admin_header.php'); ?>
        <h1>Class Report</h1>
        
        <table>
            <tr>
                <th>Class</th>
                <th>Student</th>
                <th>Type</th>
                <?php
                foreach ($parshos as $parsha) {
                    echo "<th>";
                    $num = count($data);
                    echo "<table><tr><th colspan=" . $num . ">" . $parsha['name'] . "</th></tr><tr>";
                    foreach ($data as $val) {
                        echo "<th>" . substr($val,0,1) . "</th>";
                    }
                    echo "</tr></table></th>";
                }
                ?>
                <?php
                $num = count($data);
                echo "<th><table><tr><th colspan=" . $num . ">Total</th></tr><tr>";
                foreach ($data as $val) {
                    echo "<th>" . substr($val,0,1) . "</th>";
                }
                echo "</tr></table></th>";
                ?>
            </tr>
            <?php
            $totals = array();
            foreach ($info as $user => $more) {
                if (!isset($userInfo[$user])) continue;
                $totals[$user] = array();
                echo "<tr><td>" . $userInfo[$user]['class'] . "</td><td>" . $userInfo[$user]['name'] . "</td><td>" .
                    $userInfo[$user]['subject'] . "</td>";
                foreach ($more as $parsha => $other) {
                    echo "<td>";
                    $num = count($data);
                    echo "<table><tr>";
                    foreach ($data as $val) {
                        echo "<td>" . $other[$val] . "</td>";
                        if (isset($totals[$user][$val])) $totals[$user][$val] += $other[$val];
                        else $totals[$user][$val] = $other[$val];
                    }
                    echo "</tr></table></td>";
                }
                echo "<td><table><tr>";
                foreach ($data as $val) {
                    echo "<td>" . $totals[$user][$val] . "</td>";
                }
                echo "</tr></table></td></tr>";
            }
            ?>
        </table>
    </body>
</html>