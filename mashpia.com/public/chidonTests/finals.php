<?php
//ini_set('display_errors', 1);
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$ct = new ChidonTests();

if (isset($_POST['submit'])) {
    $qrys = [];
    foreach ($_POST['chidon_final'] as $id => $mark) {
        if ($mark) {
            $sql = "update th_chidon set chidon_final_mark = " . mysql_real_escape_string(intval($mark)) . " where th_chidon_id = " . $id;
            $qrys[] = $sql;
        }
    }
    foreach ($_POST['trophy_final'] as $id => $mark) {
        if ($mark) {
            $sql = "update th_chidon set trophy_final_mark = " . mysql_real_escape_string(intval($mark)) . " where th_chidon_id = " . $id;
            $qrys[] = $sql;
        }
    }
    foreach ($_POST['khk_final'] as $id => $mark) {
        if ($mark) {
            $sql = "update th_chidon set khk_final_mark = " . mysql_real_escape_string(intval($mark)) . " where th_chidon_id = " . $id;
            $qrys[] = $sql;
        }
    }
    mysql_query('set autocommit=0');
    mysql_query('begin');
    $success = true;
    foreach ($qrys as $qry) {
        if (!mysql_query($qry)) {
            $success = false;
            break;
        }
    }
    if ($success) {
        mysql_query('commit');
    } else {
        mysql_query('rollback');
        echo "Not Saved. Error in qry: " . $qry . "<br />";
    }
    mysql_query('set autocommit=1');
}

$info = [];
foreach ($schools as $id => $school) {
    $ct->setStudents($id);
    $info[$id] = $ct->getStudents();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Enter Test Score</title>
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
        .mark {
            width: 50px;
        }
    </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
<h1>Enter Test Score</h1>
<div class="infobox">Please enter the <strong>number</strong> of questions scored correctly. The system will calculate the correct mark.</div>
<?php
$types = $ct->getTypes();
$types['trophy'] = 'Trophy';
echo "<form action='finals.php' method='post'>";
echo "<div style='float: right'><input type='submit' name='submit' value='Save' style='padding: 12px; font-size: large' /></div><br /><br />";
foreach ($info as $school => $children) {
    if (empty($children)) continue;
    echo "<h2>" . $schools[$school] . "</h2>";
    echo "<table><tr><th>Serial Number</th><th>Grade</th><th>Student</th><th>Track Registered For</th><th>Chidon Final</th>
        <th>Trophy Final</th><th>KHK Final</th></tr>";
    foreach ($children as $child) {
        $grade = $child['class_grade'] . ($child['class_sub'] ? '' : '-' . $child['class_sub']);
        $name = $child['first'] . ' ' . $child['last'];
        $id = $child['th_chidon_id'];
        echo "<tr><td>" . $child['user_serial'] . "</td><td>" . $grade . "</td><td>" . $name . "</td>";
        if (empty($child['test_type'])) $default = true;
        else $default = false;
        foreach ($types as $type => $value) {
            if ($child['test_type'] == $type) echo "<td class='type'>" . ucwords($value) . "</td>";
        }
        echo "<td><input type='text' name='chidon_final[$id]' class='chidon_final mark'";
        if ($child['chidon_final_mark']) echo " value='" . $child['chidon_final_mark'] . "'";
        echo " /></td>";
        echo "<td><input type='text' name='trophy_final[$id]' class='trophy_final mark'";
        if ($child['trophy_final_mark']) echo " value='" . $child['trophy_final_mark'] . "'";
        echo " /></td>";
        echo "<td><input type='text' name='khk_final[$id]' class='khk_final mark'";
        if ($child['khk_final_mark']) echo " value='" . $child['khk_final_mark'] . "'";
        echo " /></td></tr>";
    }
    echo "</table>";
}
echo "<div style='float: right'><input type='submit' name='submit' value='Save' style='padding: 12px; font-size: large' /></div>";
echo "</form>";
?>
</body>
<script>
    $(function() {
        // BCM IA wants to have the page only show when entering a password. not secure but makes her believe it's secure.
        const school_id = <?=$admin_user['auths']['school'][0]?>;
        if (school_id == 176) {
            // password protect
            const password = 'laky';
            let pass = '';
            while (pass != password) {
                pass = prompt('Please enter password.');
            }
        }
        $('body').show();
        alert('Please make sure to SAVE after entering scores.');
    })
    $(".mark").focus( function() {
        let val = $(this).val()
        if (parseInt(val) == 0) {
            $(this).val('')
        }
    })
    $(".chidon_final").keyup( function() {
        const max = 50;
        let val = $(this).val();
        if (parseInt(val) > max) {
            alert('Please be sure that you are entering the number of questions scored correctly, and NOT the test mark. It should not be higher than ' + max);
            $(this).val(0);
            $(this).focus();
        }
    });
</script>
</html>
