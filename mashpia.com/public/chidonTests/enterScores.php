<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('max_execution_time', 300);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$super = $admin_user['auth'] == 'super';

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true); // add chidon schools
$schools = $as->getSchools();

require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
if (isset($_POST['yr'])) $ct = new ChidonTests($_POST['yr']);
else $ct = new ChidonTests();

$testNumber = isset($_REQUEST['test_num']) ? $_REQUEST['test_num'] : 1;

// check if there's avgs that were set for the school
// if not, route them to settings page
if ($admin_user['auth'] != 'super') {
    $settingsSet = true;
    $school_id = $admin_user['auths']['school'][0];
    $settings = $ct->getSettings($school_id, 0, 0);
    foreach (['chidon_passing_avgs', 'chidon_final_passing_avgs', 'chidon_test_levels'] as $table) {
        if ($table == 'chidon_test_levels') $details = ['tests']; // finals are not used for tests
        else $details = ['maven', 'pro', 'expert', 'genius'];
        foreach ($details as $type) {
            if ($type == 'genius' && !$super) $settings[$school_id][$table][$type] = 80; // genius is disabled for non-super users
            if (empty($settings[$school_id][$table][$type])) {
                $settingsSet = false;
                break;
            }
        }
    }
    if (!$settingsSet) {
        header("Location: settings.php?fromMarks=1");
        exit;
    }
}

// initialize all tests to be disabled
$disabled = true;

// school exceptions
$exceptions = [
    1 => [],
    2 => [],
    3 => [19, 110]
];
$exceptionsDates = [
    1 => [
      
    ],
    2 => [
      
    ],
    3 => [
      
    ]
];
// disable marking after certain dates for bc's
if ($admin_user['auth'] != 'super') {
    $today = new DateTime();
    $opening = ChidonTests::getOpeningDates();
    $shutdown = ChidonTests::getClosingDates();
    if ($opening[$testNumber] && $today >= $opening[$testNumber] && $shutdown[$testNumber] && $today < $shutdown[$testNumber]) {
        $disabled = false;
    }
    if ($disabled) {
      $school_id = $admin_user['auths']['school'][0];
      if (
          in_array($school_id, $exceptions[$testNumber]) ||
          (isset($exceptionsDates[$testNumber][$school_id]) && $today < $exceptionsDates[$testNumber][$school_id])
      ) {
        $disabled = false;
      }
    }
} 

if (in_array($admin_user['admin_id'], [175069, 200721])) $disabled = false; // naftoli and tzivi can override all

if (isset($_POST['yr']) && $_POST['yr'] != $year) $disabled = true;

if (isset($_POST['scores'])) {
    // echo "<pre>"; print_r($_POST['scores']); echo "</pre>"; exit;
    // make sure not to save marks if we have set disabled to true
    if (!$disabled) {
        $ct->insertScores($_POST['scores'], $_POST['levels']);
        header("Location: marks.php?test_num=" . $testNumber);
        exit;
    }
}

if ($admin_user['auth'] == 'super' || isset($_POST['submit'])) {
    $class_id = $_POST['grade'] ?? 0;
    $info = [];
    $scores = [];
    foreach ($schools as $id => $school) {
        $ct->setStudents($id, $class_id);
        $info[$id] = $ct->getStudents();
        $ct->setScores();
        $scores[$id] = $ct->getScores();
        $levels[$id] = $ct->getLevels();
    }
}

// find out which schools to disable
$locked = [];
$stmt = $MASHPIA_DB->prepare("SELECT * FROM chidon_confirmations WHERE year = ?");
$stmt->execute([$year]);
$result = $stmt->fetchAll();
foreach ($result as $row) {
    $locked[] = $row['school_id'];
}

// find out if school set the report cards to show on parent accounts
$field = 'show_report_card_' . $testNumber;
$stmtReportCards = $MASHPIA_DB->prepare("
    SELECT " . $field . " FROM schools WHERE school_id = ?
")
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
  </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
<h1>Enter Test Score</h1>
<?php
if ($admin_user['auth'] == 'super') {
    $selectedYr = isset($_POST['yr']) ? $_POST['yr'] : $year;
    echo '<form action="enterScores.php" method="post">';
    echo "Change Year: <select name='yr' onchange='this.form.submit()'>";
    for ($i = 5782; $i <= $year; $i++) {
        echo "<option value='$i'";
        if ($i == $selectedYr) echo " selected";
        echo ">$i</option>";
    }
    echo "<input type='hidden' name='test_num' value=" . $testNumber . ">";
    echo "</form>";
}
?>
<?php if ($admin_user['auth'] == 'super' || isset($_POST['submit'])) { ?>
  <h2>Test #<?= $testNumber ?></h2>
  <div class="infobox">Please enter the <strong>number</strong> of questions scored correctly. The system will calculate
    the correct mark.
  </div>
    <?php
    $types = $ct->getTypes();
    echo "<form action='' method='post'>";
    echo "<div style='float: right'><input type='submit' name='submit' value='Save & Review Marks' style='padding: 12px; font-size: large' /></div>";
    echo "<a href='settings.php'><input type='button' value='Marks/Levels Settings' style='padding: 12px; font-size: large' /></a>";
    foreach ($info as $school => $children) {
        if (empty($children)) continue;
        echo "<h2>" . $schools[$school] . "</h2>";

        $field = 'show_report_card_' . $testNumber;
        $resReportCard = $stmtReportCards->execute([$school]);
        $reportCard = $stmtReportCards->fetch()['' . $field . ''];

        echo "<p><input type='checkbox' class='report_cards' id='" . $school . "'";
        if ($reportCard == 1) echo " checked";
        echo " /> Show Report Card for Test #" . $testNumber . " on Parent Accounts</p>";
        echo "<table><tr><th>Serial Number</th><th>Grade</th><th>Student</th><th>Track Chosen</th>";
        foreach ($types as $type => $value) {
            echo "<th>" . ucwords($value) . " Score</th>";
        }
        echo "</tr>";
        foreach ($children as $child) {
            // check locked
            if (
              $admin_user['auth'] != 'super' && 
              in_array($school, $locked) && 
              !in_array($school, $exceptions[$testNumber])
            ) $disabled = true;
            $grade = $child['class_grade'] . (empty($child['class_sub']) ? '' : '-' . $child['class_sub']);
            $name = $child['first'] . ' ' . $child['last'];
            $id = $child['th_chidon_id'];
            $levelValue = $ct->getLevel($child['user_id'], 'tests');
            if (isset($levels[$school][$id][$testNumber]))
                $levelValue = $levels[$school][$id][$testNumber];

            echo "<tr><td>" . $child['user_serial'] . "</td><td>" . $grade . "</td><td>" . $name . "</td>";
            if (empty($child['test_type'])) $default = true;
            else $default = false;
            if (!in_array($child['test_type'], array_keys($types))) echo "<td></td>";
            else {
                foreach ($types as $type => $value) {
                    if ($child['test_type'] == $type) echo "<td class='type'>" . ucwords($value) . "</td>";
                }
            }
            foreach ($types as $type => $value) {
                $class = 'score';
                if ($type == 'expert') $class = 'expert';
                $score = isset($scores[$school][$id][$testNumber][$type]) ? intval($scores[$school][$id][$testNumber][$type]) : 0;
                echo "<td><input type='text' class='$type' name='scores[$id][$testNumber][$type]' value='" . ($score > 0 ? $score : '') . "' size='4' class='$class' ";
//                        if ($type != 'genius' && $disabled) echo "disabled ";
//                        else if ($type == 'genius' && ($levelValue == 1 || $disableIyun)) echo "disabled "; // don't set variable to disabled b/c then the test levels will be disabled
                if ($disabled) echo "disabled ";
                else if ($type == 'genius' && $levelValue == 1) echo "disabled ";
                echo "/></td>";
            }

            echo "<td><select class='level' name='levels[$id][$testNumber]'";
            if ($disabled) echo " disabled";
            echo ">";
            echo "<option value='1'";
            if ($levelValue == 1) echo " selected";
            echo ">1</option>";
            echo "<option value='2'";
            if ($levelValue == 2) echo " selected";
            echo ">2</option>";
            echo "</select></td></tr>";
        }
        echo "</table>";
    }
    echo '<input type="hidden" name="test_num" value=' . $testNumber . '>';
    echo "<div style='float: right'><input type='submit' name='submit' value='Save & Review Marks' style='padding: 12px; font-size: large' /></div>";
    echo "</form>";
} else {
    ?>
  <form action="enterScores.php" method="post">
    Choose Class: <select name="grade">
      <option value="0">All Classes</option>
          <?php
          $sql = "select class_id, class_grade, class_sub from classes where school_id = :id";
          $stmt = $MASHPIA_DB->prepare($sql);
          $stmt->execute(['id' => $admin_user['auths']['school'][0]]);
          $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
          foreach ($rows as $row) {
              if (intval($row['class_grade']) >= 4) {
                  $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
                  echo "<option value='" . $row['class_id'] . "'>" . $grade . "</option>";
              }
          }
          ?>
    </select><br/><br/>
    <input type="hidden" name="test_num" value="<?= $testNumber ?>">
    <input type="submit" name="submit" value="Submit"/>
  </form>
    <?php
}
?>
</body>
<script>
  const test_number = <?= $testNumber ?>;
  $(function () {
    const showAlert = <?= isset($_POST['submit']) ? 1 : 0?>;
    if (showAlert) alert('Please make sure to SAVE after entering scores.');
    checkShowReportCards()
  })
  $(".score").focus(function () {
    let val = $(this).val()
    if (parseInt(val) == 0) {
      $(this).val('')
    }
  })
  $(".score").keyup(function () {
    const max = 10;
    let val = $(this).val();
    if (parseInt(val) > max) {
      alert('Please be sure that you are entering the number of questions scored correctly, and NOT the test mark. It should not be higher than ' + max);
      $(this).val(0);
      $(this).focus();
    }
  });
  $(".expert").focus(function () {
    let val = $(this).val()
    if (parseInt(val) == 0) {
      $(this).val('')
    }
  })
  $(".expert").keyup(function () {
    const max = 20;
    let val = $(this).val();
    if (parseInt(val) > max) {
      alert('Please be sure that you are entering the number of questions scored correctly, and NOT the test mark. It should not be higher than ' + max);
      $(this).val(0);
      $(this).focus();
    }
  });

  async function checkShowReportCards() {
    const res = await fetch('api/getReportCardsInfo.php')
    const info = await res.json()
    if (info.success) {
      const data = info.info
      const ids = Object.keys(data)
      for (let id of ids) {
        if (data[id][test_number] && document.getElementById(id))
          document.getElementById(id).checked = parseInt(data[id][test_number]) ? true : false
      }
    }
  }

  $(".report_cards").click(function () {
    let id = $(this).attr('id')
    let isChecked = $(this).is(":checked")
    fetch('api/setReportCardsInfo.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        school_id: id,
        value: isChecked,
        test_num: test_number
      })
    })
      .then(res => res.json())
      .then(res => {
        if (res.success) alert('Saved.')
        else alert('Error saving.')
      })
  })

  $('.level').change(function () {
    const selectedLevel = $(this).val();
    const iyunInput = $(this).closest('tr').find('.genius'); // Find the corresponding iyun input in the same row

    // Enable or disable the iyun input based on the selected level
    if (selectedLevel == '2') { // Assuming level 2 enables the iyun input
      iyunInput.attr('disabled', false);
    } else {
      iyunInput.attr('disabled', true);
      iyunInput.val('0'); // Clear the value if disabled
    }
  })

  document.addEventListener('DOMContentLoaded', function () {
    $(".level").each(function () {
      if ($(this).val() == '1') {
        const iyunInput = $(this).closest('tr').find('.genius');
        iyunInput.attr('disabled', true)
      }
    })
  })
</script>
</html>
