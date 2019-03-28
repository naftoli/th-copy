<?
/***************** DEBUGGING SETTINGS **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
    echo "<h2>Debug log:</h2>";
}
if($debug) echo "<pre>";

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

/***************** EXTERNAL DEPENDENCIES **********************/
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/class.schoolsUsers.php';

/***************** GET SOME BASIC INFORMATION **********************/
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
// debugging information
//if($debug) print_r($schools);

// Get the total pages

// end debugging
if($debug) echo "</pre>";
?>
<!DOCTYPE html">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Yearly Prize Shipping Report</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css">
        <link href="../css/grey_select.css?v=1.1" rel="stylesheet" type="text/css">
        <link href="../css/small_tables.css" rel="stylesheet" type="text/css">
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <link href="../css/fancy_checks.css" rel="stylesheet" type="text/css">
        <style>h3 {margin: 2%;border-bottom: 1px solid #aaa;}h4 {font-size: .95em;padding: 1%;}a.button{display: inline-block}
            tr {text-align: left;}.center{text-align: center}.fancy-check-container {font-size: 1.5em;}</style>
    </head>
    <body>
        <? include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); // load the basic UI ?>
        <h1>Yearly Prize Shipping Report</h1>
        <p class="center">Click
            <a target="_blank" href="https://docs.google.com/document/d/1FKrfoxTUTORwW9lO3Kx0mUY9wVeCBFCn0noQzVoj4q8/edit">here</a>
            for the complete rewards manual
        </p>
        <div class="action-links">
            <i class="fa fa-filter" aria-hidden="true"></i> Filter: 
            <select id="filter">
                <option value="">All</option>
                <option value="shipped">Shipped</option>
                <option value="unshipped">Not Shipped</option>
            </select>
            <a class="button" href="../forms/staff_info.php">
                <i class="fa fa-plus" aria-hidden="true"></i> Add/Edit Staff
            </a>
            <a class="button" href="total_prizes_shipping.php" id="print_link">
                <i class="fa fa-print" aria-hidden="true"></i> Print Shipping Report
            </a>
        </div>
        <div id="dropdowns">
            <? if(count($schools) == 1) {?>
                <select id="school_id" name="school_id" class="hidden"  disabled>
                    <option value="<?=array_keys($schools)[0]?>"><?=array_values($schools)[0]?></option>
                </select>
            <?} else {?>
                <select id="school_id" name="school_id">
                    <option value="">All Schools</option>
                    <? foreach($schools as $school_id => $school_name){?>
                        <option value="<?=$school_id?>"><?=$school_name?></option>
                    <?}?>
                </select>
            <?}?>
        </div>
        <div id="options">
            <label for="start_date">
                <i class="fa fa-calendar" aria-hidden="true"></i> Registered from
            </label>
            <input type="date" name="start_date" id="start_date" placeholder="mm/dd/yyyy" value="2017-07-01" />
            <label for="end_date">untill</label>
            <input type="date" name="end_date" id="end_date" placeholder="mm/dd/yyyy" value="<?=date("Y-m-d")?>"/>
        </div>
        <div class="action-links">
            <a class="button" id="refresh">
                <i class="fa fa-refresh" aria-hidden="true"></i> Generate/Refresh
            </a>
            <a class="button" id="ship-all">
                <i class="fa fa-truck" aria-hidden="true"></i> Mark All Shipped
            </a>
        </div>
        
        <div id="total_prize_report"></div>
        
        <script>var debug = <?=$debug ? "true" : "false"?></script>
        <script src="../js/total_prizes.php.js?v=1.8"></script>
    </body>
</html>