<?php // Authentication....
ini_set( 'max_execution_time', 600 );
$admin_auth = array( 'school', 'user' ); 
require( 'header.php' );

// imports
require_once( $_SERVER['DOCUMENT_ROOT'].'/class.shabbosMevorchim.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php' );

// generate the shabbos mevorchim report
$sm = new ShabbosMevorchim();
$sm->setReportDates($_GET['date']); // get the date from the GET request....
$reportDates = $sm->getReportDates(); // get the dates for the report
$date = end($reportDates);
$key = key($reportDates);
// generate the results.
$sm->setArmyResults();
?>
<!DOCTYPE html>
<html>
    <head>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Shabbos Mevorchim Tehillim Report</title>
        <style type='text/css'>
            @font-face {
              font-family: Exo;
              src: url("/fonts/Exo/static/Exo-Regular.ttf")
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
                .hebrewFont {
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
            <?php
            $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
            $ids = $as->getSchools();

            foreach ( $ids as $id => $name ) {

                if (isset($_GET['school'])) {
                    if ($_GET['school'] != $id) continue;
                }

                $sm->setSchool( $id );
                $sm->setSchoolResults( $id );
                $sm->setClassResults();
                ?>

                <p align='center' style='font-size: 54px; font-family: Hebrew;'>שבת מברכים <?=$sm->getHebrewMonthFromJd($date)?></p>

                <div class='main' align="center">
                    <?php $sm->generateSummary( $key, $date ) ?>
                    <br /><br />

                    <div style='float: right;'>
                        <?php $sm->generateArmyTable( $key, $date ); ?>
                    </div>

                    <div style='float: left;'>
                        <?php $sm->generateBaseTable( $key, $date ); ?>
                    </div>
                </div>

                <div class="page-break"></div>
                <div style="clear: both"></div>
            <?php } ?>
        </div>
    </body>
</html>