<?php
//ini_set('display_errors', 1);
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$ct = new ChidonTests();

// save marks
if (isset($_POST['submit'])) {
    $qrys = [];
    for ($i = 1; $i <= 4; $i++) {
        $level = 'level_' . $i;
        foreach ($_POST[$level] as $id => $mark) {
            if ($mark != '') {
                $mark = intval($mark);
                $qrys[] = "insert into th_chidon_finals 
                            set year = $year, 
                            user_id = $id, 
                            $level = $mark
                            on duplicate key update $level = $mark";
            }
        }
    }
    if (isset($_POST['khk'])) {
        foreach ($_POST['khk'] as $id => $mark) {
            if ($mark != '') {
                $mark = intval($mark);
                $qrys[] = "insert into th_chidon_finals 
                            set year = $year, 
                            user_id = $id, 
                            khk = $mark
                            on duplicate key update khk = $mark";
            }
        }
    }

    mysql_query('set autocommit=0');
    mysql_query('begin');
    $success = true;
    foreach ($qrys as $qry) {
        if (! mysql_query($qry)) {
            $success = false;
            break;
        }
    }
    if ($success) mysql_query('commit');
    else {
        mysql_query('rollback');
        echo "Not Saved. Error in qry: " . $qry . "<br />";
    }
    mysql_query('set autocommit=1');
}

$marks = [];
$sql = "select * from th_khk_marks";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $marks[$row['th_chidon_id']][$row['test_number']] = $row['mark'];
}

$final_marks = [];
$sql = "select * from th_chidon_finals where year = 5782";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $final_marks[$row['user_id']] = $row;
}

function passedKhk($id) {
    global $marks;

    $user_marks = $marks[$id];
    $total = 0;
    if (!empty($user_marks)) {
        foreach ($user_marks as $mark) $total += intval($mark);
        $total /= 4;
        if ($total >= 70) return true;
        else return false;
    }
    return false;
}

function getAward($child) {
    global $final_marks;

    $tracks = [
        1   => 'yesod',
        2   => 'yediah',
        3   => 'havonah',
        4   => 'iyun'
    ];
    $finals = [
        'yesod'     => 20,
        'yediah'    => 40,
        'havonah'   => 60,
        'iyun'      => 80
    ];
    $needed = [
        'yesod'     => 60,
        'yediah'    => 70,
        'havonah'   => 80,
        'iyun'      => 90
    ];
    $awards = [
        'yesod'     => 'certificate',
        'yediah'    => 'plaque',
        'havonah'   => 'medal / plaque',
        'iyun'      => 'trophy / medal / plaque'
    ];

    $highest_track = $child['highest_track'];
    // find out if award is same as before final or not
    $award = false;
    $key = array_search($highest_track, $tracks);
    if ($key !== false) {
        // go down from key to find where the child is holding
        if (isset($final_marks[$child['user_id']])) {
            $row = $final_marks[$child['user_id']];
            $score = 0;
            for ($i = 1; $i <= $key; $i++) {
                $level = 'level_' . $i;
                if ($row[$level]) {
                    $score += $row[$level];
                }
            }
            for ($i = 1; $i <= $key; $i++) {
                $divide_by = $finals[$tracks[$i]];
                $final_score = number_format(($score / $divide_by) * 100, 2);
                if ($final_score >= $needed[$tracks[$i]]) {
                    $award = $tracks[$i];
                }
            }
        }
    }
    if ($award) return $awards[$award];
    else return 'no award yet';
}

$info = [];
foreach ($schools as $id => $school) {
    $ct->setStudents($id);
    $info[$id] = $ct->getStudents();
}
// initialize all tests to not be disabled
$tooLate = false;
// disable marking after certain dates for bc's
if ($admin_user['auth'] != 'super') {
    $today = new DateTime();
    $shutdown = new DateTime('2022-03-11 10:00:00');

    if ($today >= $shutdown) {
        $tooLate = true;
    }
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
        .mark, .khk {
            width: 50px;
        }
        input:disabled {
            background: #ccc;
        }
    </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
<h1>Enter Test Score</h1>
<div class="infobox">Please enter the <strong>number</strong> of questions scored correctly. The system will calculate the correct mark.</div>
<?php
$types = $ct->getTypes();
$levels = array_values($types);
echo "<form action='finals.php' method='post' enctype='multipart/form-data'>";
echo "<div style='float: right'><input type='submit' name='submit' value='Save' style='padding: 12px; font-size: large' /></div><br /><br />";
foreach ($info as $school => $children) {
    if (empty($children)) continue;
    echo "<h2>" . $schools[$school] . "</h2>";
    echo "<table><tr><th>Serial Number</th><th>Grade</th><th>Student</th><th>Highest Track</th>";
    foreach ($types as $old => $new) {
        echo "<th>$new</th>";
    }
    echo "<th>KHK Final</th>";
    echo "<th>Award</th>";
    echo "</tr>";
    foreach ($children as $child) {
        $grade = $child['class_grade'] . ($child['class_sub'] ? '' : '-' . $child['class_sub']);
        $name = $child['first'] . ' ' . $child['last'];
        $id = $child['user_id'];
        echo "<tr><td>" . $child['user_serial'] . "</td><td>" . $grade . "</td><td>" . $name . "</td><td>" .
            $child['highest_track'] . "</td>";
        for ($i = 1; $i <= 4; $i++) {
            // find out which level the child can go up to
            $key = array_search(ucwords($child['highest_track']), $levels);
            $key++;
            // create the proper input box
            $level = 'level_' . $i;
            echo "<td><input type='text' name='{$level}[$id]' class='$level mark'";
            if (isset($final_marks[$id][$level])) echo " value='" . $final_marks[$id][$level] . "'";
            else echo "value='0'";
            if ($i > $key || $tooLate) echo " disabled";
            echo " /></td>";
        }
        // add khk_final
        // check if child should be able to take the khk final
        $disabled = 'disabled';
        if (intval($child['khk_reg']) && passedKhk($child['th_chidon_id']) && !$tooLate) $disabled = '';
        echo "<td><input type='text' name='khk[$id]' class='khk' $disabled ";
        if (isset($final_marks[$id]['khk'])) echo "value='" . $final_marks[$id]['khk'] . "'";
        else echo "value='0'";
        echo " /></td>";
        echo "<td>" . getAward($child) . "</td></tr>";
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
        <?php if (! isset($_POST['submit'])) : ?>
        alert('Please make sure to SAVE after entering scores.');
        <?php endif; ?>
    })

    $(".mark").focus( function() {
        let val = $(this).val()
        if (parseInt(val) == 0) {
            $(this).val('')
        }
    })

    $(".mark").blur(function () {
        const amount = $(this).val()
        if (amount) {
            const max = 20
            if (amount > max) {
                alert('You cannot enter a number greater than ' + max)
                $(this).val('')
                $(this).focus()
            }
        } else {
            if (amount == '') $(this).val(0)
        }
    })
    $(".khk").blur(function () {
        const amount = $(this).val()
        const max = 200
        if (amount > max) {
            alert('You cannot enter a number greater than ' + max)
            $(this).val('')
            $(this).focus()
        }
    })
</script>
</html>
