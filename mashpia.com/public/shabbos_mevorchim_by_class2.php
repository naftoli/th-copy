<?php // Authentication....
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

ini_set( 'max_execution_time', 600 );
$admin_auth = array( 'school', 'user' ); 
require( 'header.php' );

// imports
require_once( $_SERVER['DOCUMENT_ROOT'] . '/class.shabbosMevorchim.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php' );

$sm = new ShabbosMevorchim();
$sm->setReportDates($_GET['date']); // get the date from the GET request....
// set the army results
$sm->setArmyResults();
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

                foreach( $ids as $id => $name ) {
                    // get the report just for this school
                    if ( isset( $_GET['school'] ) && $_GET['school'] != $id ) continue;
                    // generate the report for just this school
                    $sm->setSchool( $id );
                    $sm->setSchoolResults( $id );
                    $sm->setClassResults();

                    // changes from shabbos_mevorchim.php
                    $sm->setStudentResults(0, $_GET['date']);
                    $sm->generateStudentReport();
                    if (isset($_GET['school'])) break;
                }
            ?>
        </div>
    </body>
</html>