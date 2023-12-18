<?php
$admin_auth = array('school');
require('header.php');
require_once('calendar.php');

function getInfo()
{
    $sql = "SELECT 
            s.subject_name, m.medal_name, ms.subject_id, ms.medal_ord 
        FROM
            medals_subjects ms
                JOIN
            medals m USING (medal_ord)
                JOIN
            subjects s USING (subject_id)
        ORDER BY subject_id , medal_ord";
    $result = mysql_query($sql);
    $info = [];
    while ($row = mysql_fetch_assoc($result)) {
        $info[$row['subject_name']][$row['medal_name']] = $row;
    }
    return $info;
}

require_once 'class.medalReport.php';
$m = new MedalReport;

if (isset($_GET['start']) && isset($_GET['end'])) {
    $m->overrideDates($_GET['start'], $_GET['end']);
}

if (isset($_POST['date_selection'])) {
    $dates = explode(':', $_POST['date_selection']);
    $start = $dates[0];
    $end = $dates[1];
    $m->overrideDates($start, $end);
}

$heDates = $m->getHeReportDates();
$m->setMedalSummary(true);
$summary = $m->getMedalSummary();
$dates = $m->getReportDates();

$m->setMedalDetails();
$totalSchools = $m->getTotalSchools();
$totalGrades = $m->getTotalGrades();
$totalStudents = $m->getTotalStudents();

$subject_medals = getInfo();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?= $dir ?>">

<HEAD>
  <TITLE>Medals Summary Report</TITLE>
  <LINK href="admin_styles.css" rel="stylesheet" type="text/css">
  <SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <style type="text/css">
    th, td {
      padding: 5px;
      font-size: 12px;
    }

    .column {
      width: 3.5in;
      height: 10.5in;
      padding: .3in;
    }

    .label {
      width: 2in;
      font-size: 14px;
    }

    .medals {
      width: 1.5in;
      margin-left: .3in;
      font-size: 14px;
    }

    .break {
      clear: both;
    }

    .page-break {
      page-break-after: always;
    }

    @media screen {
      .no-print {
        display: block;
      }
    }

    @media print {
      .no-print {
        display: none;
      }

      .page {
        width: 8in;
        height: 10.5in;
      }
    }
  </style>
</HEAD>


<BODY>
<?php include('admin_header.php'); ?>

<div class="no-print">
  <h1>Medals Report Summary</h1>
  <div>
    Current Report is calculated from <?= $heDates['start_he'] ?> up to <?= $heDates['end_he'] ?>.<br/>
    <form action="" method="post">
      <p>
          <?php
          echo $m->getHtmlSelect();
          ?>
        <input type="submit" name="submit" value="Modify Report"/>
      </p>
    </form>
  </div>
</div>

<div class="no-print">
  <button id="medalsBtnAll">Set All Medals as Shipped</button>
</div>

<div id="report_div" name="report_div">
  <h2>Totals</h2>
  <p>
    Total Schools: <?= $totalSchools; ?><br/>
    Total Classes: <?= $totalGrades; ?><br/>
    Total Students: <?= $totalStudents ?><br/>
  </p>

  <h2>Medals Summary</h2>
  <div class='page'>
      <?
      $grandtotals = array();
      foreach ($summary as $school => $line) {
          $totalSchools++;
          echo "Medals Summary for " . $school . "<br /><br />";
//          foreach ($line as $subject => $medals) {
//              echo "<div class='label'>" . $subject . "<br />";
//              echo "<div class='medals'>";
//              foreach ($medals as $medal => $total) {
//                  echo $medal . " - " . $total . "<br />";
//                  if (isset($grandtotals[$subject][$medal])) {
//                      $grandtotals[$subject][$medal] += $total;
//                  } else {
//                      $grandtotals[$subject][$medal] = $total;
//                  }
//              }
//              echo "</div></div>";
//              echo "<div class='break'></div>";
//          }
          foreach ($subject_medals as $subject => $medals) {
              echo "<div class='label'>" . $subject . "<br />";
              echo "<div class='medals'>";
              foreach ($medals as $medal => $row) {
                  if (isset($line[$subject][$medal])) $total = $line[$subject][$medal];
                  else $total = 0;
                  echo $medal . " - " . $total . "<br />";
                  if (isset($grandtotals[$subject][$medal])) {
                      $grandtotals[$subject][$medal] += $total;
                  } else {
                      $grandtotals[$subject][$medal] = $total;
                  }
              }
          }
          echo "<br />";
          echo "<div class='page-break'></div>";
          echo "<br />";
      }
      ?>
  </div>
</div>

<h2>Grand Totals</h2>
<table>
  <tr>
    <th>Subject</th>
    <th>Medal</th>
    <th>Total</th>
  </tr>
    <?
    $gtotal = 0;
    foreach ($grandtotals as $subject => $info) {
        foreach ($info as $medal => $total) {
            $gtotal += $total;
            echo "<tr><td>" . $subject . "</td><td>" . $medal . "</td><td>" . $total . "</td></tr>";
        }
    }
    echo "<tr><th></th><th></th><th>" . number_format($gtotal) . "</th></tr>";
    ?>
</table>

</BODY>
<script>
  const start = <?= $dates['start']; ?>;
  const end = <?= $dates['end']; ?>;

  $(function () {
    $("#medalsBtnAll").click(function () {
      $.getJSON('edit_functions.php?function_name=update_medals_all&parameters=' + start + '_' + end, function (data) {
        if (success == 1) {
          alert('All medals have been set as shipped.');
        } else {
          alert('There was an error setting all medals as shipped.');
        }
      }).fail(function () {
        alert('There was an error setting all medals as shipped.');
      })
    })
  })
</script>
</HTML>
