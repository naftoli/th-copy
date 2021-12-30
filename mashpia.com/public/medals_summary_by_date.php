<?php
$admin_auth = array('school');
require('header.php');
require_once('calendar.php');

require_once 'class.medalReport.php';
$m = new MedalReport;
$dates = $m->getDates();

if (isset($_GET['start']) && isset($_GET['end'])) {
    $m->overrideDates($_GET['start'], $_GET['end']);
}

if (isset($_POST['submit'])) {
    $chosen = $_POST['dates'];
    $arrDates = explode(':', $chosen);
    $m->overrideDates($arrDates[0], $arrDates[1]);
}

$heDates = $m->getHeReportDates();
$m->setMedalSummary();
$summary = $m->getMedalSummary();

$m->setMedalDetails();
$totalSchools = $m->getTotalSchools();
$totalGrades = $m->getTotalGrades();
$totalStudents = $m->getTotalStudents();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

<HEAD>
    <TITLE>Medals Summary Report</TITLE>
    <LINK href="admin_styles.css" rel="stylesheet" type="text/css">
    <SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
        <form action="" method="post">
            Current Report is calculated from <?=$heDates['start_he']?> up to <?=$heDates['end_he']?>.<br />
            Change Dates:
            <select name="dates">
                <?php
                $i = 0;
                $total = count($dates);
                while ($i < ($total - 1)) {
                    $start = $dates[$i] + 1;
                    $end = $dates[++$i];
                    $str1 = jdtojewish($start, true, CAL_JEWISH_ADD_GERESHAYIM);
                    $start_he = iconv('WINDOWS-1255', 'UTF-8', $str1);
                    $str2 = jdtojewish($end, true, CAL_JEWISH_ADD_GERESHAYIM);
                    $end_he = iconv('WINDOWS-1255', 'UTF-8', $str2);
                    echo "<option value='" . $start . ":" . $end . "'>" . $start_he . " - " . $end_he . "</option>";
                }
                ?>
            </select>
            <input type="submit" name="submit" value="Create Report" />
        </form>
    </div>
</div>

<div id="report_div" name="report_div">
    <h2>Totals</h2>
    <p>
        Total Schools: <?=$totalSchools;?><br />
        Total Classes: <?=$totalGrades;?><br />
        Total Students: <?=$totalStudents?><br />
    </p>

    <h2>Medals Summary</h2>
    <div class='page'>
        <?
        $grandtotals = array();
        foreach ($summary as $school => $line) {
            $totalSchools++;
            echo "Medals Summary for " . $school . "<br /><br />";
            foreach ($line as $subject => $medals) {
                echo "<div class='label'>" . $subject . "<br />";
                echo "<div class='medals'>";
                foreach ($medals as $medal => $total) {
                    echo $medal . " - " . $total . "<br />";
                    if (isset($grandtotals[$subject][$medal])) {
                        $grandtotals[$subject][$medal] += $total;
                    } else {
                        $grandtotals[$subject][$medal] = $total;
                    }
                }
                echo "</div></div>";
                echo "<div class='break'></div>";
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
</HTML>
