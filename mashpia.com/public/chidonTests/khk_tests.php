<?php
//ini_set('display_errors', 1);
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if (isset($_POST['submit'])) {
    echo "<pre>"; print_r($_POST); echo "</pre>";
    $qrys = [];
    foreach ($_POST['marks'] as $id => $marks) {
        foreach ($marks as $num => $mark) {
            $qrys[] = "insert into th_chidon set khk_test_$num = $mark where th_chidon_id = " . $id;
        }
    }
    echo "<pre>"; print_r($qrys); echo "</pre>";
}

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

$info = [];
foreach ($schools as $school_id => $school) {
    $sql = "select * from users u 
            join classes c on c.class_id = u.class_id 
            join th_chidon tc using (user_id) 
            where tc.khk = 1 
            and u.school_id = " . $school_id . "            
            and tc.year = " . $year;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $info[$school_id][] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Enter KHK Marks</title>
    <link href="../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
        tr, th, td {
            font-size: 14px;
            padding: 5px;
        }
        td:not(.type) {
            vertical-align: top;
        }
        body {
            display: none;
        }
    </style>
</head>
<body>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
    <h1>Enter KHK Marks</h1>
    <?php
    echo "<form action='' method='post'>";
    echo "<div style='float: right'><input type='submit' name='submit' value='Save Marks' style='padding: 12px; font-size: large' /></div>";
    foreach ($info as $school => $children) {
        if (empty($children)) continue;
        echo "<h2>" . $schools[$school] . "</h2>";
        echo "<table><tr><th>Chidon KHK ID</th><th>Grade</th><th>Student</th><th>Test 1</th><th>Test 2</th><th>Test 3</th><th>Test 4</th></tr>";
        foreach ($children as $child) {
            $grade = $child['class_grade'] . ($child['class_sub'] ? '' : '-' . $child['class_sub']);
            $name = $child['first'] . ' ' . $child['last'];
            $id = $child['th_chidon_id'];
            echo "<tr><td>" . $id . "</td><td>" . $grade . "</td><td>" . $name . "</td>";
            for ($i = 1; $i <= 4; $i++) {
                $mark = 0;
                if ($child["khk_test_$i"] > 0) $mark = $child["khk_test_$i"];
                echo "<td><input type='text' name='marks[$id][$i]' value='" . $mark . "' size='5' /></td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "<div style='float: right'><input type='submit' name='submit' value='Save Marks' style='padding: 12px; font-size: large' /></div>";
    echo "</form>";
    ?>
    </body>
</html>

