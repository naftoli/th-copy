<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
set_time_limit(300);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true); // add chidon schools
$schools = $as->getSchools();

require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$currentYear = GlobalSettings::getChidonYear();
$year = isset($_POST['yr']) ? $_POST['yr'] : $currentYear;

require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$ct = new ChidonTests($year);

require $_SERVER['DOCUMENT_ROOT'] . '/chidon_shipping/class.chidonShipping.php';
$cs = new ChidonShipping($year);

// save marks
$msg = '';
if (isset($_POST['submit']) && isset($_POST['track_1'])) {
//    echo "<pre>"; print_r($_POST); echo "</pre>"; exit;
    $qrys = [];
    for ($i = 1; $i <= 4; $i++) {
        $track = 'track_' . $i;
        foreach ($_POST[$track] as $id => $mark) {
            if ($mark != '') {
                $mark = intval($mark);
                $qrys[] = "insert into th_chidon_finals 
                            set year = $year, 
                            user_id = $id, 
                            $track = $mark 
                            on duplicate key update $track = $mark";
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
        if (!mysql_query($qry)) {
            $success = false;
            break;
        }
    }
    if ($success) {
      mysql_query('commit');
      $msg = "Saved";
    }
    else {
        mysql_query('rollback');
        $msg = "Not Saved. Error in qry: " . $qry . "<br />";
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
$sql = "select * from th_chidon_finals where year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $final_marks[$row['user_id']] = $row;
}

function passedKhk($child)
{
    global $marks;

    if (isset($marks[$child['th_chidon_id']]) && $child['date_paid'] > 0) {
        $user_marks = $marks[$child['th_chidon_id']];
        $total = 0;
        foreach ($user_marks as $mark) $total += intval($mark);
        $total /= 4;
        if ($total >= 70) return true;
    }
    return false;
}

function getTrack($child) {
    global $cs;
    $track = $cs->getTrackByTests($child);
    return $track;
}

function getAward($child)
{
    $awards = [
      'yesod' => 'certificate',
      'yediah' => 'plaque',
      'havonah' => 'medal / plaque',
      'iyun' => 'medal / plaque / blue trophy',
    ];

    $track = $child['highest_track'];
    return $track ? $awards[$track] : 'no award yet';
}

function showIyun($child) {
    global $ct;
    $ct->setStudents($child['school_id'], $child['class_id'], $child['user_id']);
    $ct->setScores();
    $scores = $ct->getScores();
    $cumulative_track = $ct->calculateCumulative($child, $scores);
//    if ($child['user_id'] == 23136) {
//      echo "Cumulative track for " . $child['user_id'] . ": " . $cumulative_track;
//      exit;
//    }
    return $child['highest_track'] == 'iyun' || $cumulative_track == 'iyun';
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
//    $shutdown = new DateTime('2023-03-06 19:20:00');
//    if ($today >= $shutdown) {
//        $tooLate = true;
//    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
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
<?php if ($msg) echo "<div style='color: red'>$msg</div><br />"; ?>
<div class="infobox">Please enter the <strong>number</strong> of questions scored correctly. The system will calculate
  the correct mark.
</div>
<?php
if ($admin_user['auth'] == 'super') {
    $selectedYr = isset($_POST['yr']) ? $_POST['yr'] : $currentYear;
    echo '<form action="finals.php" method="post">';
    echo "Change Year: <select name='yr' onchange='this.form.submit()'>";
    for ($i = 5782; $i <= $currentYear; $i++) {
        echo "<option value='$i'";
        if ($i == $selectedYr) echo " selected";
        echo ">$i</option>";
    }
    echo "</select></form>";
}

if (isset($_POST['grade'])) {
    $gradeChosen = $_POST['grade'];
    $exceptions = [192, 63, 13, 42, 726];
    $types = $ct->getTypes();
    $tracks = array_values($types);
    echo "<form action='finals.php' method='post' enctype='multipart/form-data'>";
    echo "<div style='float: right'><input type='submit' name='submit' value='Save' style='padding: 12px; font-size: large' /></div><br /><br />";
    foreach ($info as $school => $children) {
        if (empty($children)) continue;
        //    if ($tooLate && in_array($school, $exceptions)) $tooLate = false;
        echo "<h2>" . $schools[$school] . "</h2>";
        echo "<table><tr><th>Serial Number</th><th>Grade</th><th>Student</th><th>Highest Track</th>";
        foreach ($tracks as $track) {
            echo "<th>$track</th>";
        }
        echo "<th>KHK Final</th>";
        echo "<th>Award</th>";
        echo "</tr>";
        foreach ($children as $child) {
            $highest = getTrack($child); // track based off tests
            $idx1 = array_search(ucwords($child['highest_track']), $tracks);
            $idx2 = array_search(ucwords($highest), $tracks);
            if ($idx2 > $idx1) $child['highest_track'] = $highest;
            if ($child['date_paid'] > 0) {
                if ($gradeChosen > 0 && $child['class_id'] != $gradeChosen) continue;
                $grade = $child['class_grade'] . (empty($child['class_sub']) ? '' : '-' . $child['class_sub']);
                $name = $child['first'] . ' ' . $child['last'];
                $id = $child['user_id'];
                echo "<tr><td>" . $child['user_serial'] . "</td><td>" . $grade . "</td><td>" . $name . "</td><td>" .
                    $child['highest_track'] . "</td>";
                for ($i = 1; $i <= 4; $i++) {
                    // find out which track the child can go up to
                    $key = array_search(ucwords($child['highest_track']), $tracks);
                    $key++;
                    // create the proper input box
                    $track = 'track_' . $i;
                    echo "<td><input type='text' name='{$track}[$id]' class='$track mark'";
                    if (isset($final_marks[$id][$track])) echo " value='" . $final_marks[$id][$track] . "'";
                    else echo "value='0'";
                    // for iyun we also look at cumulative marks
                    if ($i == 4 && (!showIyun($child) || $tooLate)) echo " disabled";
                    else if ($i > $key || $tooLate) echo " disabled";
                    echo " /></td>";
                }
                // add khk_final
                // check if child should be able to take the khk final
                $disabled = 'disabled';
                if (intval($child['khk_reg']) && passedKhk($child) && !$tooLate) $disabled = '';
                echo "<td><input type='text' name='khk[$id]' class='khk' $disabled ";
                if (isset($final_marks[$id]['khk'])) echo "value='" . $final_marks[$id]['khk'] . "'";
                else echo "value='0'";
                echo " /></td><td>" . getAward($child) . "</td></tr>";
            }
        }
        echo "</table>";
    }
    echo "<div style='float: right'><input type='submit' name='submit' value='Save' style='padding: 12px; font-size: large' /></div>";
    echo "</form>";
} else {
    ?>
  <form action="finals.php" method="post">
    Choose Class: <select name="grade">
      <option value="0">All Classes</option>
          <?php
          if (isset($admin_user['auths']['school'][0])) {
              $sql = "select class_id, class_grade, class_sub from classes where school_id = " . $admin_user['auths']['school'][0];
              $result = mysql_query($sql);
              while ($row = mysql_fetch_assoc($result)) {
                  if (intval($row['class_grade']) >= 4) {
                      $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
                      echo "<option value='" . $row['class_id'] . "'>" . $grade . "</option>";
                  }
              }
          }
          ?>
    </select><br/><br/>
    <input type="submit" name="submit" value="Submit"/>
  </form>
<?php } ?>
</body>
<script>
  $(function () {
    // BCM IA wants to have the page only show when entering a password. not secure but makes her believe it's secure.
    const school_id = <?= $admin_user['auths']['school'][0] ?? 0 ?>;
    if (school_id == 176) {
      // password protect
      const password = 'laky';
      let pass = '';
      while (pass != password) {
        pass = prompt('Please enter password.');
      }
    }
    $('body').show();
      <?php if (!isset($_POST['submit'])) : ?>
    alert('Please make sure to SAVE after entering scores.');
      <?php endif; ?>
  })

  $(".mark").focus(function () {
    let val = $(this).val()
    if (parseInt(val) == 0) {
      $(this).val('')
    }
  })

  $(".mark").blur(function () {
    const max = 20
    checkAmount(this, max)
  })

  $(".khk").blur(function () {
    const max = 200
    checkAmount(this, max)
  })

  function checkAmount(elem, max) {
    const amount = $(elem).val()
    if (amount == '') $(elem).val(0)
    else if (amount > max) {
      alert('You cannot enter a number greater than ' + max)
      $(elem).val('')
      $(elem).focus()
    }
  }
</script>
</html>
