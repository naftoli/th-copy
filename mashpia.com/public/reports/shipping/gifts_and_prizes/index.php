<?php $debug = false;
/***************** DEBUGGING **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
    echo "<pre>";
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

/***************** IMPORTS **********************/
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/class.schoolsUsers.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

if($debug) echo "</pre>";?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Tehillim and Raffle Shipping Report</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/fancy-checkbox.css" rel="stylesheet" type="text/css"/>
        <link href="/raffles/shared/styles/shipping/grey_slider.css" rel="stylesheet" type="text/css"/>
        <link href="../css/shipping_form.css" rel="stylesheet" type="text/css"/>
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <link href="/styles/admin/modal.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/forms.css" rel="stylesheet" type="text/css"/>
        <style>
            .toggle-3rd {width: 32%;display: inline-block;}
        </style>
    </head>
    <body>
        <? include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
        
        <h1>Tehillim and Raffle Shipping Report</h1>
        
        <?// include(dirname(__FILE__)."/../parts/action_links.php");?>
        
        <h2>Report Options</h2>
        <? if(count($schools) == 1) {?>
            <select id="school_id" name="school_id" class="hidden"  disabled>
                <option value="<?=array_keys($schools)[0]?>"><?=array_values($schools)[0]?></option>
            </select>
        <?} else {?>
            <div class="options">
                <div class="row">
                    <i class="fa fa-university" aria-hidden="true"></i> School: 
                    <select id="school_id" name="school_id">
                        <option value="">All Schools</option>
                        <? foreach($schools as $school_id => $school_name){?>
                            <option value="<?=$school_id?>"><?=$school_name?></option>
                        <?}?>
                    </select>
                </div>
            </div>
        <?}?>
        
        <div class="options bordered">
            <span id="shipments">
                <i class="fa fa-filter" aria-hidden="true"></i> Shipments:
                <span class="option_space">
                    <label class="fancy-check-container">
                        <input id="raffles_weekly" type="checkbox" checked/> <span class="fancy-check"></span>
                    </label> <i class="fa fa-ticket" aria-hidden="true"></i> Weekly Raffles
                </span>
                <span class="option_space">
                    <label class="fancy-check-container">
                        <input id="raffles_monthly" type="checkbox" checked/> <span class="fancy-check"></span>
                    </label> <i class="fa fa-ticket" aria-hidden="true"></i> Monthly Raffles
                </span>
                <br/>
                <span class="option_space">
                    <label class="fancy-check-container">
                        <input id="gifts" type="checkbox" /> <span class="fancy-check"></span>
                    </label> <i class="fa fa-book" aria-hidden="true"></i> Tehillims
                </span>
                <span class="option_space">
                    <label class="fancy-check-container">
                        <input id="auctions" type="checkbox" checked/> <span class="fancy-check"></span>
                    </label> <i class="fa fa-ticket" aria-hidden="true"></i> Auctions
                </span>
            </span>
        </div>
        
        <div class="options bordered">
            <span class="option_space">
                <label class="fancy-check-container sorting-order">
                    <input id="sort-desc" type="checkbox"/> <span class="fancy-check"></span>
                </label>
                Sorting:
                <select id="sort" name="sort">
                    <option value="grade-name">Grade</option>
                    <option value="prize">Raffle Prize</option>
                    <option value="status">Status</option>
                    <option value="name">Name</option>
                </select>
            </span>
            <span class="option_space">
                <i class="fa fa-truck" aria-hidden="true"></i> Shipped:
                <select id="shipping-status" name="shipping-status">
                    <option value="all">All</option>
                    <option value="shipped">Shipped</option>
                    <option value="not-shipped">Not Shipped</option>
                </select>
            </span>
        </div>
        
        <div class="options bordered" id="filters">
            <span class="option_space">
                <i class="fa fa-calendar" aria-hidden="true"></i> Dates:
                <i class="fa fa-question-circle" aria-hidden="true" data-toggle="tooltip" title="Limits Raffles by their Run Date, and Yearly Gifts by User Registration"></i>
                <input type="date" name="start_date" id="start_date" placeholder="mm/dd/yyyy" value="2017-07-01"> To
                <input type="date" name="end_date" id="end_date" placeholder="mm/dd/yyyy" value="<?=date("Y-m-d")?>">
            </span>
        </div>
        
        <!--<div class="options bordered" id="filters">
            
        </div>-->
        
<!--        <h2>Report Actions</h2>-->
        
        <div class="options">
            <span class="option_space">
                <a class="button" id="generate"><i class="fa fa-refresh" aria-hidden="true"></i> Generate/Update Report</a>
            </span>
            <span class="option_space">
                <a class="button" href="printout.php" id="print"><i class="fa fa-print" aria-hidden="true"></i> Generate and Print</a>
            </span>
            <span class="option_space">
                <a class="button" id="print_shipping" href="printout.php" data-shipped_only="true">
                    <i class="fa fa-print" aria-hidden="true"></i> Print Shipped Only Report
                    <i class="fa fa-question-circle" aria-hidden="true" data-toggle="tooltip" title="This action will only print items that where/are marked as shipped"></i>
                </a>
            </span>
        </div>
        
        <hr style="display: block;"/>
        
        <div id="report"></div>
        
        <script>
        var debug = <?= $debug ? "true" : "false" ?>;// debug mode?
        var admin = <?= $admin_user['auth'] == 'super' ? "true" : "false" ?>;// admin mode?
        // var type = "gifts/prizes"; // used in tracking_number.js
        function get_report_options(){
            return {
                school_id: $("select#school_id").val(),
                shipments: {
                    raffles_weekly: $("input#raffles_weekly")[0].checked,
                    raffles_monthly: $("input#raffles_monthly")[0].checked,
                    gifts: $("input#gifts")[0].checked,
                    auctions: $("input#auctions")[0].checked
                },
                shipping_status: $("select#shipping-status").val(),
                start_date: $("input#start_date").val(),
                end_date: $("input#end_date").val(),
                sort: $("select#sort").val(),
                sort_desc: $("input#sort-desc")[0].checked
            };
        };
        </script>
        <script src="../js/shipments.js?v=1.1"></script>
        <script src="../js/report.js?v=1.1.7"></script>
        <? include(dirname(__FILE__)."/../parts/shipment_modal.php"); ?>
    </body>
</html>