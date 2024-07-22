<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

/*
  * page should show all students that have registered for this year
  * as well as base commanders, teachers, principals, etc
  */

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

/***************** EXTERNAL DEPENDENCIES **********************/
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.reg.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.parshos.php';

/***************** GET SOME BASIC INFORMATION **********************/
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$year = GlobalSettings::getRegistrationYear();
$parshos = Parshos::getParshos($year);

$students = [];
foreach ($schools as $school_id => $school_name) {
    $reg = new Reg($school_id);
    $students[$school_id] = $reg->getRegisteredChildren();
}

// order the students by grade, name
foreach ($students as $school_id => $details) {
    usort($students[$school_id], function($a, $b) {
        if ($a['class_id'] == $b['class_id']) {
            if ($a['last'] == $b['last']) {
                return strcmp(strtolower($a['first']), strtolower($b['first']));
            }
            return strcmp(strtolower($a['last']), strtolower($b['last']));
        }
        return $a['class_id'] - $b['class_id'];
    });
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Yearly Gift Eligibility Report</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css">
        <link href="../css/grey_select.css" rel="stylesheet" type="text/css">
        <link href="../css/small_tables.css" rel="stylesheet" type="text/css">
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <link href="../css/fancy_checks.css" rel="stylesheet" type="text/css">
        <style>
            h3 {margin: 2%;border-bottom: 1px solid #aaa;}h4 {font-size: .95em;padding: 1%;}a.button{display: inline-block}tr {text-align: left;}
            td.wide {min-width: 125px;} div#table-marks {overflow-x: auto;overflow-y: auto;} td, th{white-space: nowrap;} .fancy-check-container {font-size: 1.5em;}
        </style>
    </head>
    <body>
        <? include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); // load the basic UI ?>
        <h1>Yearly Prize Eligibility Report</h1>
        <div class="infobox">
            It will show a chayol as having marked missions that week even if he/she only completed 1 task the entire week.<br />
            (To see which chayolim checked off tasks for at least 5/7 days per week, go to Mission Marathon>Weekly Raffle>Eligible Students)
        </div>
        <p class="center" style="text-align: center;">Click
            <a target="_blank" href="https://docs.google.com/document/d/1FKrfoxTUTORwW9lO3Kx0mUY9wVeCBFCn0noQzVoj4q8/edit">here</a>
            for the complete rewards manual
        </p>
        <div style="font-weight: bold">
            This gift is only possible due to the generosity of Rabbi Moshe & Ruti Weiss.
            <br /><br />
            To show your appreciation:
            <br />
            <br />
            <ul>
                <li>Please have your children write Thank You cards and send them back to HQ.</li>
                <li>Please take pictures as you give out the books and send to HQ.</li>
            </ul>
        </div>
        <br /><br />
        <div id="dropdowns">
            <? if(count($schools) == 1) {?>
                <select id="school_id" name="school_id" class="hidden"  disabled>
                    <option value="<?=array_keys($schools)[0]?>"><?=array_values($schools)[0]?></option>
                </select>
            <?} else {?>
                <i class="fa fa-university" aria-hidden="true"></i> School: 
                <select id="school_id" name="school_id">
                    <option value="">All Schools</option>
                    <option value="82">A Academy</option>
                    <? foreach($schools as $school_id => $school_name){?>
                        <option value="<?=$school_id?>"><?=$school_name?></option>
                    <?}?>
                </select>
            <?}?>
            <i class="fa fa-users" aria-hidden="true"></i> Platoon:
            <span id="classes"></span>
        </div>
        <div id="options">
            <label for="start_date">
                <i class="fa fa-calendar" aria-hidden="true"></i> Dates: From
            </label>
            <select id="start">
                <?foreach($parshos as $week_start => $parsha_name){?>
                    <option value="<?= $week_start ?>"><?= $parsha_name ?></option>
                <?}?>
            </select>
            <label for="end_date">To</label>
            <select id="end">
                <?foreach($parshos as $week_start => $parsha_name){?>
                    <option value="<?= $week_start ?>" <?//= $week_start == $last_start_date ? "selected" : "" ;?>>
                        <?= $parsha_name ?>
                    </option>
                <?}?>
            </select>
        </div>
        <div id="action-links">
            <a class="button" id="refresh">
                <i class="fa fa-spinner" aria-hidden="true"></i> Load
            </a>
            <a class="button" id="csv_export">
                <i class="fa fa-download" aria-hidden="true"></i> Generate CSV (Excel)
            </a>
        </div>

        <div id="eligible_users_report"></div>
        
        <script src="../js/eligible_students.php.js?v=5.0"></script>
    </body>
</html>