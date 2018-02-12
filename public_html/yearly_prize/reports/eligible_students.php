<?
/***************** DEBUGGING SETTINGS **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
    echo "<h2>Debug Log: </h2>";
}
if($debug) echo "<pre>";

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

/***************** EXTERNAL DEPENDENCIES **********************/
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/class.schoolsUsers.php';

require_once dirname(__FILE__).'/../functions/get_parsha_names.php';
require_once dirname(__FILE__).'/../classes/TotalWeeklyTasks.php';

/***************** GET SOME BASIC INFORMATION **********************/
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
// create a weekly tasks object
$totalWeeklyTasks = new TotalWeeklyTasks(0, unixtojd());
// set the start date in debugging mode
if($debug && isset($_GET['start']) && $_GET['start']){
    $totalWeeklyTasks->start_date = $_GET['start'];
}
// generate the weeks array
$totalWeeklyTasks->get_week_dates();
// calculate the end_date for the last week and use that to get the parsha names
$last_start_date = $totalWeeklyTasks->week_dates[count($totalWeeklyTasks->week_dates) - 1]['start']; // used to set the defaults in the dropdown
$end_date = $totalWeeklyTasks->week_dates[count($totalWeeklyTasks->week_dates) - 1]['end'];
$parshos = get_parsha_names($totalWeeklyTasks->start_date, $end_date);

// set the dropdown to what the user expects
$get_type = isset($_GET['type']) ? $_GET['type'] : false;
if($debug) echo $get_type."\n";

// end debugging
if($debug) echo "</pre>";
?>
<!DOCTYPE html">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Yearly Prize Eligibility Report</title>
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
        <p class="center" style="text-align: center;">Click
            <a target="_blank" href="https://docs.google.com/document/d/1FKrfoxTUTORwW9lO3Kx0mUY9wVeCBFCn0noQzVoj4q8/edit">here</a>
            for the complete rewards manual
        </p>
        <div id="dropdowns">
            <? if(count($schools) == 1) {?>
                <select id="school_id" name="school_id" class="hidden"  disabled>
                    <option value="<?=array_keys($schools)[0]?>"><?=array_values($schools)[0]?></option>
                </select>
            <?} else {?>
                <i class="fa fa-university" aria-hidden="true"></i> School: 
                <select id="school_id" name="school_id">
                    <option value="">All Schools</option>
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
                    <option value="<?= $week_start ?>" <?= $week_start == $last_start_date ? "selected" : "" ;?>>
                        <?= $parsha_name ?>
                    </option>
                <?}?>
            </select>
        </div>
        <div id="action-links">
            <i class="fa fa-bar-chart" aria-hidden="true"></i> Report: 
            <select id="type">
                <option value="combined">Combined Report</option>
                <option value="summary" <?= $get_type == "summary" ? "selected": "";?>>Summary Report</option>
                <option value="form" <?= $get_type == "form" ? "selected": "";?>>Detailed Report</option>
            </select>
            <a class="button" id="refresh">
                <i class="fa fa-spinner" aria-hidden="true"></i> Load
            </a>
            <a class="button" id="csv_export">
                <i class="fa fa-download" aria-hidden="true"></i> Generate CSV (Excel)
            </a>
        </div>
        
        
        <div id="eligible_users_report"></div>
        
        <script>var debug = <?=$debug ? "true" : "false"?></script>
        <script src="../js/eligible_students.php.js?v=5.0"></script>
    </body>
</html>