<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

$super = $admin_user['auth'] == 'super';

$children = [];
$stmt = $MASHPIA_DB->query("
    SELECT 
        u.*, c.*
    FROM
        users u
            JOIN
        classes c ON c.class_id = u.class_id 
    WHERE
        c.class_grade IN ('7' , '8')
            AND u.school_id IN (" . implode(',', array_keys($schools)) . ") 
    GROUP BY u.user_id
    ORDER BY u.school_id , c.class_grade , c.class_sub , u.last , u.first
");
$rows = $stmt->fetchAll();
foreach ($rows as $row) {
    $children[$row['school_id']][] = $row;
}

// figure out which years we need to check
$years = [];
$curYr = GlobalSettings::getChidonRegYear();
$reqYr = $_REQUEST['year'];
$i = 4;
$yr = ($reqYr ?? $curYr) - $i;
for (; $i > 0; $i--) {
    $years[] = $yr++;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>KHK Eligibility</title>
    <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
    <style type='text/css'>
        table {
            font-size: 14px;
        }
        th, td {
            padding: 5px;
        }
    </style>
</head>
<body>
<?php include('../../admin_header.php'); ?>
<h1>KHK Eligibility</h1>
<div>
    Choose Year:
    <select name="year" id="year">
        <?php
        $lastYr = $curYr - 1;
        $chosen = $reqYr ?? $curYr;
        foreach ([$curYr, $lastYr] as $yr) {
            echo "<option value='" . $yr . "'";
            if ($yr == $chosen) echo " selected";
            echo " >" . $yr . "</option>";
        }
        ?>
    </select>
</div>
<br />
<table>
    <tr>
        <th>Serial Number</th>
        <th>School</th>
        <th>Grade</th>
        <th>Student</th>
        <!--                <th>Number of times Registered for Shabbaton</th>-->
        <?php foreach ($years as $yr) { echo "<th>" . $yr . "</th>"; } ?>
        <th>Eligible</th>
        <?php if ($super) : ?>
            <th>Eligibility Copy</th>
        <?php endif; ?>
        <th>Registered for KHK</th>
    </tr>
    <?php
    $khkTotal = 0;
    foreach ($schools as $id => $school) {
        if (isset($children[$id])) {
            // get array of user ids for finding out khk eligibility
//                    $users_ids = [];
//                    foreach ($children[$id] as $child) $user_ids[] = $child['user_id'];
//                    $user_ids = array_filter($children[$id], function($child) {
//                        return $child['user_id'];
//                    });
//                    echo "<pre>"; print_r($user_ids); echo "</pre>"; exit;
//                    $khkInfo = KHK::getKHKEligibility($user_ids);
//                    $eligibility = $khkInfo[0];
//                    $yrsDetails = $khkInfo[1];
            foreach ($children[$id] as $child) {
                $user_id = $child['user_id'];
                $khkInfo = KHK::getKHKEligibility([$user_id], $chosen);
                $eligibility = $khkInfo[0];
                $yrsDetails = $khkInfo[1];
                $eligible = $eligibility[$user_id];
                if ($eligible) $khkTotal++;
                $grade = $child['class_grade'] . (empty($child['class_sub']) ? '' : '-' . $child['class_sub']);
                echo "<tr id='" . $child['user_id'] . "'><td>" . $child['user_serial'] . "</td><td>" . $school .
                    "</td><td>" . $grade . "</td><td>" . $child['first'] . ' ' . $child['last'] . "</td>";
                foreach ($yrsDetails[$user_id] as $yr => $value) {
                    echo "<td>" . ($value ? "&#10003;" : "&#10007;") . "</td>";
                }
                echo "<td><input type='checkbox' class='eligibility' name='eligibility[" . $child['user_id'] . "]' ";
                if ($eligible) echo "checked ";
                if (! $super) echo " disabled ";
                echo "/></td>";
                if ($super) {
                    echo "<td>";
                    echo $eligible ? "&#10003;" : "&#10007;";
                    echo "</td>";
                }
                echo "<td>";
                $sqlKhk = "select khk_reg from th_chidon where year = $chosen and user_id = " . $child['user_id'];
                $resKhk = mysql_query($sqlKhk);
                $rowKhk = mysql_fetch_assoc($resKhk);
                echo $rowKhk['khk_reg'] ? "&#10003;" : "&#10007;";
                echo "</td></tr>";
            }
        }
    }
    ?>
</table>
<?= $khkTotal ?>
</body>
<script>
    <?php if ($super) : ?>
    $(".eligibility").click( function () {
        let id = $(this).parent().parent().attr('id')
        let checked = $(this).is(":checked") ? 1 : 0
        $.post('updateKHK.php', { user_id: id, checked: checked }, function( result ) {
            let res = JSON.parse(result)
            if (res.success) alert('Updated.')
            else alert(res.error)
        })
    })
    <?php endif; ?>
    $("#year").change( function() {
        location.href = "khk_eligibility.php?year=" + this.value
    })
</script>
</html>