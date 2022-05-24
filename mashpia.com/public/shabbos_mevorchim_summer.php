<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', 300);
$admin_auth = ['school'];
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <link href="admin_styles.css" rel="stylesheet" type="text/css">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Shabbos Mevorchim Tehillim Report</title>
    <style type='text/css'>
        @media all {
            .page-break {
                display: none;
            }
            .hayomYom {
                float: right;
                width: 300px;
                padding-right: 10px;
                line-height: 1.5em;
            }
            .logo {
                float: left;
                margin-right: 20px;
            }
            .top {
                margin-left: auto;
                margin-right: auto;
                text-align: center;
            }
            .main {
                margin-left: auto;
                margin-right: auto;
            }
            .user {
                float: left;
                margin-right: 25px;
                margin-bottom: 25px;
            }
        }
        @media print {
            .page-break {
                display: block;
                page-break-after: always;
            }
            tr, th, td {
                font-size: 14px;
            }
            .no-print {
                display: none;
            }
            hr {
                display: none;
            }
            .hayomYom {
                float: right;
                width: 300px;
                padding-right: 10px;
                line-height: 1.5em;
            }
            .logo {
                float: left;
                margin-right: 20px;
            }
            .top {
                margin-left: auto;
                margin-right: auto;
                text-align: center;
            }
            .main {
                margin-left: auto;
                margin-right: auto;
            }
        }
        tr, th, td {
            padding: 10px;
            border: 1px solid black;
            font-size: 12px;
        }
    </style>
</head>

<body>
<?php require_once 'admin_header.php'; ?>
<div class='no-print'>
    <h1>Shabbos Mevorchim Tehillim Report</h1>

    <div align='center'>
        <input type='button' value='Print' onclick='window.print()'>
    </div>
</div>
<br />
<?php
require_once 'class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], false );
$ids = $as->getSchools();
// $ids = [54 => 'Beis Rivka'];

// get dates
require 'class.globalSettings.php';
$sm = calculateSM( GlobalSettings::getCurrentYear() );
$dates = [$sm[11], $sm[12]];

require_once 'class.shabbosMevorchim.php';
$info = [];
$userInfo = [];
foreach ( $ids as $id => $name ) {
    if ($id == 612) continue;
    // if (count($ids) > 1) {
    // 	echo "<h2>" . $name . "</h2>";
    // 	echo "<div class='page-break'></div>";
    // }
    $sm = new ShabbosMevorchim();
    $sm->setSchool($id);
    foreach ($dates as $date) {
        $sm->setReportDates($date);
        $sm->setStudentResults();
        $quotas = $sm->getStudentResults();
        $done = $sm->getStudentDoneResults();
        // echo "<pre>"; print_r($quotas); echo "</pre>"; exit;
        foreach ($quotas as $date => $other) {
            foreach ($other as $grade => $more) {
                foreach ($more as $user_id => $values) {
                    $sql = "select first, last, class_grade, class_sub from users u join classes c using (class_id) where user_id = " . $user_id;
                    $result = mysql_query($sql);
                    $userInfo[$user_id] = mysql_fetch_assoc($result);
                    foreach ($values as $task => $quota) {
                        $result = intval($done[$date][$grade][$user_id][$task]);
                        // echo "User: " . $user_id . "<br />";
                        // echo "Date: " . $date . "<br />";
                        // echo "Quota: " . $quota . "<br />";
                        // echo "Done: " . $result . "<br /><br />";
                        if ($result && $result >= intval($quota)) {
                            $info[$id][$user_id][$date] = [
                                'quota' =>  $quota,
                                'done'  =>  $result
                            ];
                        }
                    }
                }
            }
        }
    }
}
?>
<table>
    <caption>Children that completed their quotas on both Shabbos Mevorchim Menachem Av and Elul</caption>
    <tr>
        <th>School</th>
        <th>Grade</th>
        <th>Student</th>
    </tr>
    <?php
    foreach ($info as $school_id => $more) {
        foreach ($more as $user_id => $dates) {
            if (count($dates) > 1) {
                $user = $userInfo[$user_id];
                $grade = $user['class_grade'] . ($user['class_sub'] ? '-' . $user['class_sub'] : '');
                $name = $user['first'] . ' ' . $user['last'];
                echo "<tr><td>" . $ids[$school_id] . "</td><td>" . $grade . "</td><td>" . $name . "</td></tr>";
            }
        }
    }
    ?>
</table>
</body>
</html>