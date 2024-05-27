<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
/*
Make a report that has all kids that are able to register which is all kids in grade 4-8 in 5783.
Should include the following columns:
Serial
Full name
School
Class
Registered in 5779
Registered in 5780
Registered in 5781
Registered in 5782
Registered in 5783
KHK eligible Yes/No
KHK Registered Yes/No
Parents Email
Parents phone Number
At the bottom of each schools section, add totals per class and totals per school registered/non registered. For HQ add the grand total of all schools.
 */
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

$startGrade = 4;
$endGrade = 8;
$grades = [];
for ($i = $startGrade; $i <= $endGrade; $i++) $grades[] = "$i";

$chidonYr = GlobalSettings::getChidonRegYear();
$year = $_REQUEST['year'] ?? $chidonYr;
if ($chidonYr > $year) {
    $startGrade--;
    $endGrade--;
}

// personal info
$users = [];
$userIds = [];
$classes = [];
$teachers = [];
$eighthGrades = [];
$sql = "
    SELECT 
        u.first,
        u.last,
        u.user_serial,
        u.school_id,
        u.user_id,
        c.class_id,
        c.class_grade,
        c.class_sub,
        c.class_teacher,
        a.admin_email,
        a.admin_phone_mobile,
        a.admin_phone_work,
        a.admin_phone_home
    FROM
        users u
            JOIN
        classes c USING (class_id)
            LEFT JOIN
        admin_auths aa ON aa.id = u.user_id
            LEFT JOIN
        admins a USING (admin_id)
    WHERE
        (aa.auth = 'user' or aa.auth is null) AND class_grade in (\"" . implode('","', $grades) . "\")
            AND u.school_id in (" . implode(',', array_keys($schools)) . ") 
    ORDER BY u.school_id , c.class_grade , c.class_sub , last , first
";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[$row['school_id']][$row['class_id']][] = $row;
    $userIds[] = $row['user_id'];
    $classes[$row['class_id']] = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
    $teachers[$row['class_id']] = $row['class_teacher'];
    if ($row['class_grade'] == '8') $eighthGrades[] = $row['class_id'];
}

// chidon info
$chidonInfo = [];
$sql = "SELECT tc.user_id, tc.khk_reg, tc.year, tc.date_paid, tci.highest_track 
        FROM th_chidon tc 
        LEFT JOIN th_chidon_info tci USING (year, user_id) 
        ORDER BY year";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $chidonInfo[$row['user_id']][$row['year']] = $row;
}

$eligibility = KHK::eligibility($userIds);
//echo "<pre>"; print_r($eligibility); print_r($chidonInfo); echo "</pre>"; exit;
$trackYr = 5782;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Comprehensive Registration Report</title>
        <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            tr, th, td {
                padding: 10px;
                font-size: 12px;
                border-bottom: 1px solid grey;
            }
        </style>
    </head>
    <body>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
    <h1>Comprehensive Registration Report</h1>
    <div class="infobox">
        Please Note: the word "enrolled" refers to the enrollment in the beginning of the year. The word "registered"
        refers to registration for the experience at the end of the year.
    </div>
    <div>
        Choose Year:
        <select name="year" id="year">
            <?php
            for ($y = 5782; $y <= $chidonYr; $y++) {
              echo "<option value='$y'" . ($y == $year ? ' selected' : '') . ">$y</option>";
            }
            ?>
        </select>
    </div>
    <?php
    $totals = [];
    $grandTotals = [];
    foreach ($users as $school_id => $more) {
        // init grand totals
        $grandTotals[$school_id]['kids'] = 0;
        $grandTotals[$school_id]['reg'] = 0;
        $grandTotals[$school_id]['khk'] = 0;
        $grandTotals[$school_id]['khk_reg'] = 0;

        echo "<h2>" . $schools[$school_id] . "</h2>";
        foreach ($more as $class_id => $other) {
            // init totals
            $totals[$school_id][$class_id]['kids'] = 0;
            $totals[$school_id][$class_id]['reg'] = 0;
            $totals[$school_id][$class_id]['khk'] = 0;
            $totals[$school_id][$class_id]['khk_reg'] = 0;

            echo "<h3>Grade " . $classes[$class_id] . ' - ' . $teachers[$class_id] . "</h3>";
            ?>
            <table>
                <tr>
                    <th>Serial Number</th>
                    <th>Full Name</th>
                    <th>School</th>
                    <th>Class</th>
                    <?php
                    for ($i = 4; $i >= 0; $i--) {
                        echo "<th>" . ($year - $i) . "</th>";
                    }
                    ?>
                    <th>KHK Eligible</th>
                    <th>KHK Enrolled</th>
                    <th>Parent Email</th>
                    <th>Parent Phone Number</th>
                </tr>
                <?php
                foreach ($other as $user) {
                    $grade = $user['class_grade'] . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']);
                    echo "<tr><td>" . $user['user_serial'] . "</td><td>" . ($user['first'] . ' ' . $user['last']) . "</td><td>" .
                        $schools[$user['school_id']] . "</td><td>" . $grade . "</td>";
                    // registration
                    for ($i = 4; $i >= 0; $i--) {
                        echo "<td>";
                        if (($year - $i) >= $trackYr){
                            // update total possible reg for lastest yr
                            if ($i == 0) $totals[$school_id][$class_id]['kids']++;
                            if (isset($chidonInfo[$user['user_id']][$year - $i])) {
                                if ($chidonInfo[$user['user_id']][$year - $i]['date_paid'] > 0) {
                                    if (isset($chidonInfo[$user['user_id']][$year - $i]['highest_track']))
                                        echo $chidonInfo[$user['user_id']][$year - $i]['highest_track'];
                                    else echo "didn't pass"; // will never show b/c there's no kids that registered for chidon experience but didn't pass
                                } else {
                                  // if we are in the same year as enrollment, only say 'enrolled'
                                    if (GlobalSettings::getChidonRegYear() == $year) echo "enrolled";
                                    else echo "enrolled but not registered";
                                }
                                // totals are only for latest yr
                                if ($i == 0) $totals[$school_id][$class_id]['reg']++;
                            }
                            else echo "not enrolled";
                        } else {
                            if (isset($chidonInfo[$user['user_id']][$year - $i])) {
                                if ($chidonInfo[$user['user_id']][$year - $i]['date_paid'] > 0) echo "registered";
                                else echo "enrolled but not registered";
                            }
                            else echo "not enrolled";
                        }
                        echo "</td>";
                    }
                    // khk
                    echo "<td>";
                    if ($eligibility[$user['user_id']]) {
                        echo "yes";
                        $totals[$school_id][$class_id]['khk']++;
                    }
                    else echo "no";
                    echo "</td><td>";
                    if (isset($chidonInfo[$user['user_id']][$year]) && intval($chidonInfo[$user['user_id']][$year]['khk_reg'])) {
                        echo "yes";
                        $totals[$school_id][$class_id]['khk_reg']++;
                    }
                    else echo "no";
                    echo "</td>";
                    // parent info
                    if (isset($user['admin_email'])) {
                        echo "<td>" . $user['admin_email'] . "</td>";
                        $phone = $user['admin_phone_mobile'] ? $user['admin_phone_mobile'] . "<br />" : '';
                        $phone .= $user['admin_phone_home'] ? $user['admin_phone_home'] . "<br />" : '';
                        $phone .= $user['admin_phone_work'] ? $user['admin_phone_work'] . "<br />" : '';
                        echo "<td>" . $phone . "</td></tr>";
                    } else {
                      echo "<td colspan='2'>No Parent Account in System</td></tr>";
                    }
                }
            echo "</table><br />";
        }
        // init grand total for this school
        $grandTotal['kids'] = 0;
        $grandTotal['reg'] = 0;
        $grandTotal['khk'] = 0;
        $grandTotal['khk_reg'] = 0;

        echo "<h2>" . $schools[$school_id] . " Summary</h2>";
        echo "<table><tr><th>Grade</th><th>Teacher</th><th>Amount eligible to enroll</th><th>Amount Enrolled</th><th>KHK Eligible</th><th>KHK Enrolled</th></tr>";
        foreach ($totals[$school_id] as $class_id => $more) {
            $grandTotal['kids'] += $more['kids'];
            $grandTotal['reg'] += $more['reg'];
            $grandTotals[$school_id]['kids'] += $more['kids'];
            $grandTotals[$school_id]['reg'] += $more['reg'];

            echo "<tr><td>" . $classes[$class_id] . "</td><td>" . $teachers[$class_id] . "</td><td>" . $more['kids'] . "</td><td>" . $more['reg'] . "</td>";
            if (in_array($class_id, $eighthGrades)) {
                echo "<td>" . $more['khk'] . "</td><td>" . $more['khk_reg'] . "</td></tr>";
                $grandTotal['khk'] += $more['khk'];
                $grandTotal['khk_reg'] += $more['khk_reg'];
                $grandTotals[$school_id]['khk'] += $more['khk'];
                $grandTotals[$school_id]['khk_reg'] += $more['khk_reg'];
            }
            else echo "<td></td><td></td></tr>";
        }
        echo "<tr><th colspan='2'>Grand Total</th><th>" . $grandTotal['kids'] . "</th><th>" . $grandTotal['reg'] . "</th><th>" . $grandTotal['khk'] . 
            "</th><th>" . $grandTotal['khk_reg'] . "</th></tr>";
        echo "</table><br /><br /><div style='page-break-after: always'></div>";
    }
    if ($admin_user['auth'] == 'super') {
        // totals for HQ
        $total['kids'] = 0;
        $total['reg'] = 0;
        $total['khk'] = 0;
        $total['khk_reg'] = 0;

        echo "<h2>School Grand Totals</h2>";
        echo "<table><tr><th>School</th><th>Amount eligible to enroll</th><th>Amount Enrolled</th><th>KHK Eligible</th><th>KHK Enrolled</th></tr>";
        foreach ($schools as $school_id => $school_name) {
            if (isset($grandTotals[$school_id])) {
                echo "<tr><td>" . $school_name . "</td><td>" . $grandTotals[$school_id]['kids'] . "</td><td>" . $grandTotals[$school_id]['reg'] . 
                    "</td><td>" . $grandTotals[$school_id]['khk'] . "</td><td>" . $grandTotals[$school_id]['khk_reg'] . "</td></tr>";

                $total['kids'] += $grandTotals[$school_id]['kids'];
                $total['reg'] += $grandTotals[$school_id]['reg'];
                $total['khk'] += $grandTotals[$school_id]['khk'];
                $total['khk_reg'] += $grandTotals[$school_id]['khk_reg'];
            }
        }
        echo "<tr><th>Grand Total</th><th>" . $total['kids'] . "</th><th>" . $total['reg'] . "</th><th>" . $total['khk'] . "</th><th>" . $total['khk_reg'] . "</th></tr>";
        echo "</table>";
    }
    ?>
    </body>
    <script>
        $("#year").change( function () {
            let yr = $(this).val()
            location.href = "comprehensive_reg_report.php?year=" + yr
        })
    </script>
</html>