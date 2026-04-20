<?php // Authentication....
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

ini_set( 'max_execution_time', 600 );
$admin_auth = array( 'school', 'user' ); 
require( 'header.php' );

// imports
require_once( $_SERVER['DOCUMENT_ROOT'] . '/class.shabbosMevorchim.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php' );
?>
<!DOCTYPE html>
<html>
    <head>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Shabbos Mevorchim Tehillim Report - By Class</title>
        <style type='text/css'>
            @font-face {
              font-family: Exo;
              src: url("/fonts/Exo/Exo-VariableFont_wght.ttf")
            }
            @font-face {
              font-family: Hebrew;
              src: url("/fonts/FbTkuma-Regular.otf")
            }
            @media all {
                .page-break { display: none; }
                .hayomYom { float: right; width: 300px; padding-right: 10px; line-height: 1.5em; }
                .logo { float: left; margin-right: 20px; }
                .top { margin-left: auto; margin-right: auto; text-align: center; }
                .main { margin-left: auto; margin-right: auto; }
                #tehillim {
                    font-family: Exo;
                }
                .hebrewDate {
                    font-family: Hebrew;
                }
            }
            @media print {
                .page-break { display: block; page-break-after: always; }
                tr, th, td { font-size: 14px; }
                .no-print { display: none; }
                hr { display: none; }
            }
            tr, th, td { border: 1px solid black; padding: 10px; font-size: 12px; }
        </style>
    </head>
    <body>
        <?php require_once($_SERVER['DOCUMENT_ROOT'].'/admin_header.php'); ?>

        <?php
        if (! isset($_REQUEST['date'])) : ?>
            <h1>Shabbos Mevorchim HQ Report</h1>
            <form method="post" action="shabbos_mevorchim_by_class_hq.php">
                For: <select name="date">
                    <?php
                    $sm = new ShabbosMevorchim();
                    $sm->setReportDates();
                    $reportDates = $sm->getReportDatesAll();

                    $i = 0;
                    $num = count($reportDates);
                    foreach ($reportDates as $month => $d) {
                        if (++$i == $num) 
                            echo "<option value=" . $d . " selected='selected'>Shabbos Mevorchim " . $month . "</option>";
                        else 
                            echo "<option value=" . $d . ">Shabbos Mevorchim " . $month . "</option>"; 
                    }
                    ?> 
                </select><br /><br />
                <input type="submit" name="submit" id="submit" value="generate report">
            </form>
        <?php else: ?>
            <?php
            $sm = new ShabbosMevorchim();
            $sm->setReportDates($_REQUEST['date']); // get the date from the GET request....
            // set the army results
            $sm->setArmyResults();
            ?>
            <div id="tehillim">
                <div class='no-print'>
                    <h1>Shabbos Mevorchim Tehillim Report</h1>

                    <div class="infobox" style="line-height: 1.2">
                        SMT Reports are pulled out right after the Shabbos Mevorchim Tehillim Deadline. If a parent or base commander entered an amount
                        on their account after this deadline, it will not show on this report and it will not be used to determine the winning schools/classes,
                        however they will still receive their miles and mission.
                    </div>

                    <div align='center'>
                        <input type='button' value='Print' onclick='window.print();'>
                    </div>
                </div>
                <br />
                <?php // render the results for each school.
                    $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
                    $ids = $as->getSchools();

                    echo "<table>";
                    echo "<thead>";
                    echo "<tr><th>School</th><th>Chayol</th><th>Goal</th>";
                    echo "<th>Accomplishment</th>";
                    echo "<th>Minutes Goal</th>";
                    echo "<th>Minutes Accomplishment</th>";
                    echo "</tr>";
                    echo "</thead>";
                    echo "<tbody>";

                    $grandTotals = [];
                    foreach( $ids as $id => $name ) {
                        // generate the report for just this school
                        $sm->setSchool( $id );
                        $sm->setSchoolResults( $id );
                        $sm->setClassResults();

                        // changes from shabbos_mevorchim.php
                        $sm->setStudentResults(0, $_REQUEST['date']);
                        $sm->generateStudentReport(true, $id, $name);
                    }

                    echo "<tr><td align='right' colspan='2'>Grand Totals:</td>";
                    foreach ($grandTotals as $type => $task) {
                        echo "<td>" . number_format($task['goal']) . ' ' . $type . "</td>";
                        echo "<td>" . number_format($task['done']) . ' ' . $type . "</td>";
                    }
                    echo "</tr>";
                    echo "</tbody>";
                    echo "</table>";
                ?>
            </div>
        <?php endif; ?>
    </body>
</html>