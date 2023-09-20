<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = array('school');
require('header.php');

if (isset($_POST['submit'])) {
    //echo 'here'; exit;
    if (isset($_POST['school'])) {
        if ($_POST['school'] == -1 && (in_array($_POST['report'], array('school', 'class')))) {
            echo "Please choose a school.";
            exit;
        }
        $school = "&school=" . $_POST['school'];
    } else {
        $school = '';
    }

    $date = $_POST['date'];
    switch ($_POST['report']) {
        case 'army':
            header("Location: shabbos_mevorchim_summary.php?date=$date");
            break;
        case 'school':
            header("Location: shabbos_mevorchim.php?date=$date$school");
            break;
        case 'class':
            header("Location: shabbos_mevorchim_by_class2.php?date=$date$school");
            break;
        case 'student':
            header("Location: shabbos_mevorchim_by_student.php?date=$date$school");
            break;
        case 'competition':
            header("Location: shabbos_mevorchim_hq.php?date=$date");
            break;
    }
}

require_once 'class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();
$sm = calculateSM($year);
$months = array(
    0 => 'Tishrei',
    1 => 'Cheshvon',
    2 => 'Kislev',
    3 => 'Teves',
    4 => 'Shvat',
    5 => 'Adar I',
    6 => 'Adar II',
    7 => 'Nissan',
    8 => 'Iyar',
    9 => 'Sivan',
    10 => 'Tamuz',
    11 => 'Av',
    12 => 'Elul'
);
// if plain yr change adar 1 to adar and remove adar 2
if ($sm[6] == $sm[7]) {
    unset($sm[6]);
    $months[5] = 'Adar';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

<HEAD>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Shabbos Mevorchim Report</title>
  <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
  <script>
    $(function () {
      $("#submit").click(function () {
        var type = $(".reportType:checked").val();
        if (type == 'school' || type == 'class') {
          if ($("#school").val() == -1) {
            alert("You must choose a school.");
            return false;
          }
        }
      });
    });
  </script>
  <link href="admin_styles.css" rel="stylesheet" type="text/css">
  <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
  <style type='text/css'>
    table {
      font-size: 12px;
    }

    th, td {
      padding: 3px 10px;
    }

    .newPage {
      page-break-after: always;
    }

    fieldset {
      border: 1px solid white;
      padding: 10px;
      padding-top: 0px;
      -moz-border-radius: 10px;
      -webkit-border-radius: 10px;
      border-radius: 10px;
      font-size: 14px;
    }

    legend {
      margin-left: 20px;
      padding: 5px;
      color: purple;
      font-size: 16px;
    }
  </style>
</HEAD>

<BODY>
<? include('admin_header.php'); ?>
<h1>Shabbos Mevorchim Report</h1>
<form method="post" action="choose_sm_report.php">
    <?php
    //if it is a super user, let them choose school
    if ($admin_user['auth'] == 'super') {
        require_once 'class.adminSchools.php';
        $as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
        $schools = $as->getSchools();
        echo "<p>Please choose school for school or class report.</p>";
        echo "<select name='school' id='school'>";
        echo "<option value='-1'>Choose School</option>";
        foreach ($schools as $id => $school) {
            echo "<option value='$id'>" . $school . "</option>";
        }
        echo "</select><br /><br />";
    }
    ?>
    For: <select name="date">
        <?php
        foreach ($sm as $month => $date) {
            echo "<option value=" . $date . ">Shabbos Mevorchim " . $months[$month] . "</option>";
        }
        ?>
    </select><br/><br/>
    What type of report are you looking for?<br/><br/>
    <input type="radio" name="report" class="reportType" value="army" checked="checked"> Army-wide Report<br/>
    <input type="radio" name="report" class="reportType" value="school"> Base Report<br/>
    <input type="radio" name="report" class="reportType" value="class"> Platoon Report<br/>
    <input type="radio" name="report" class="reportType" value="competition"> School Wide Competition Report<br/>
    <br/>
    <!--<input type="radio" name="report" value="student"> Student Report<br /><br />-->
    <input type="submit" name="submit" id="submit" value="generate report">
</form>
		<span id="show-loading"></span>
<script>
  $("#submit").click(function(){
  	alert("Please be patient, due to the large amount of data the report may take up to 10 minutes to generate.");
  	$("#show-loading").html("<div class='loader'></div>");
  });
</script>
</body>
</html>