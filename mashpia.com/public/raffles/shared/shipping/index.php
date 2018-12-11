<?
// enable debuging
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
}

$admin_auth = array('school'); 
require($_SERVER["DOCUMENT_ROOT"].'/header.php');
// load the required files
require_once(dirname(__FILE__).'/../classes/Raffle.php');
// namespace fixing
use raffles\weekly\Raffle as Raffle; // use the raffle from its namespace
// load the required classes
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/class.schoolsUsers.php';

/***************** LOAD THE DATA **********************/
// get all the schools (should only return 1)
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
// load all the raffles
$raffles = Raffle::loadAll($filter);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Raffle Shipping Report</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>

        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <link href="../styles/shipping/multiselect_dropdown.css" rel="stylesheet" type="text/css"/>
        <link href="../styles/shipping/grey_input.css" rel="stylesheet" type="text/css"/>
        <link href="../styles/shipping/grey_slider.css" rel="stylesheet" type="text/css"/>
        <link href="../styles/shipping/fancy-check.css" rel="stylesheet" type="text/css"/>
        <link href="../styles/shipping/index.php.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        <? include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
        
        <h1>Raffle Shipping Report</h1>
        
        <p>Please click <a target="_blank" href="https://docs.google.com/document/d/1FKrfoxTUTORwW9lO3Kx0mUY9wVeCBFCn0noQzVoj4q8/edit?usp=sharing">here</a> for the Rewards Manual</p>
        
        <h2>Please select one of the following options and generate the report</h2>
        <div class="options">
            <? if(count($schools) == 1) {?>
                <select id="school_id" name="school_id" class="hidden"  disabled>
                    <option value="<?=array_keys($schools)[0]?>"><?=array_values($schools)[0]?></option>
                </select>
            <?} else {?>
                <div class="row">
                    <i class="fa fa-university" aria-hidden="true"></i> School: 
                    <select id="school_id" name="school_id">
                        <option value="">All Schools</option>
                        <? foreach($schools as $school_id => $school_name){?>
                            <option value="<?=$school_id?>"><?=$school_name?></option>
                        <?}?>
                    </select>
                </div>
            <?}?>
            <div class="row">
                <i class="fa fa-filter" aria-hidden="true"></i> Limit Type: 
                <input type="radio" name="limit" value="dates"> Date Range 
                <input type="radio" name="limit" value="raffles"> Raffles
            </div>
            <div class="row">
                <div id="option_dates" class="option">
                    <i class="fa fa-calendar" aria-hidden="true"></i>
                    From <input type="date" id="date_start" placeholder="mm/dd/yyyy" value="2017-07-01"/>
                    To <input type="date" id="date_end" placeholder="mm/dd/yyyy" value="<?=date("Y-m-d")?>"/>
                </div>
                <div id="option_raffles" class="option">
                    <dl class="dropdown"> 
                        <dt><a href="#">
                            <span class="hida">Select Raffles</span><i class="fa fa-chevron-down" aria-hidden="true"></i>  
                            <p class="multiSel"></p>  
                        </a></dt>
                        <dd>
                            <div class="mutliSelect">
                                <input id="raffle_id_list" type="hidden"></input>
                                <ul>
                                <? foreach($raffles as $raffle){ // render an option for each raffle?>
                                    <li id="raffle_li_<?=$raffle->raffle_id?>">
                                        <label class="fancy-check-container">
                                            <input type="checkbox" value="<?=$raffle->raffle_id?>" />
                                            <span class="fancy-check"></span>
                                            <?=$raffle->name?>
                                        </label>
                                    </li>
                                <?}?>
                                </ul>
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
            <a class="button" id="generate"><i class="fa fa-th-list" aria-hidden="true"></i> Generate Report</a>
        </div>
        <div id="report_options" class="options">
            <h1>Raffle Shipping Report</h1>
            <div class="row">
                <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> Sort: 
                <select id="sort">
                    <option value="name">Last, First</option>
                    <option value="prize">Prize, Last, First</option>
                    <option value="grade">Grade, Last, First</option>
                </select>
                <i class="fa fa-filter" aria-hidden="true"></i> Filter: 
                <select id="filter">
                    <option value="">All</option>
                    <option value="shipped">Shipped</option>
                    <option value="not-shipped">Not Shipped</option>
                </select>
            </div>
            <a class="button" id="refresh"><i class="fa fa-refresh" aria-hidden="true"></i> Refresh Report</a>
            
            <a class="button" id="print" href="report_print.php" data-shipped_only="">
                <i class="fa fa-print" aria-hidden="true"></i> Print Report
            </a>
            <a class="button" id="print_shipping" href="report_print.php" data-shipped_only="true">
                <i class="fa fa-print" aria-hidden="true"></i> Print Shipped Only Report
            </a>
        </div>
        <div id="report"></div>
        <script>var debug = <?= $debug ? "true" : "false" ?></script>
        <script src="../js/shipping/index.php.js?v=2.6"></script>
        <script src="../js/shipping/multiselect_dropdown.js"></script>
    </body>
</html>