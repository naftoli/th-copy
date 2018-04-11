<?php $debug = false;
/***************** DEBUGGING **********************/
if (isset($_GET['debug'])) {
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
<!DOCTYPE html">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Medal and Rank Card Shipping Report</title>
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
            .options label.slider-container {width: 40px;}
            .options label.slider-container span.slider-span{font: normal normal normal 14px/1 FontAwesome;height: 22px;}
            .options label.slider-container span.slider-span:before{content: "\f0a8"; font-size: 1.4em; background: none; color: #fff;top: 1.5px;left:3px;}
            .options label.slider-container input:checked + .slider-span:before{content: "\f0a9"; }
        </style>
        <style>
            .toggle-3rd {width: 49%;display: inline-block;}
        </style>
    </head>
    <body>
        <? include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
        
        <h1>Medal and Rank Card Shipping Report</h1>
        
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
                        <input id="medals" type="checkbox" checked/> <span class="fancy-check"></span>
                    </label> <i class="fa fa-certificate" aria-hidden="true"></i> <!--<i class="fa fa-circle" aria-hidden="true"></i>--> Medals
                </span>
                <span class="option_space">
                    <label class="fancy-check-container">
                        <input id="ranks" type="checkbox" checked/> <span class="fancy-check"></span>
                    </label> <i class="fa fa-id-card" aria-hidden="true"></i> Rank Cards
                </span>
            </span>
            
            <span class="option_space">
                <label class="fancy-check-container sorting-order">
                    <input id="sort-desc" type="checkbox"/> <span class="fancy-check"></span>
                </label>
                Sorting:
                <select id="sort" name="sort">
                    <option value="grade-name">Grade</option>
                    <option value="status">Status</option>
                    <option value="name">Name</option>
                </select>
            </span>
        </div>
        
        <div class="options bordered" id="filters">
            <span class="option_space">
                <i class="fa fa-truck" aria-hidden="true"></i> Shipped:
                <select id="shipping-status" name="shipping-status">
                    <option value="all">All</option>
                    <option value="shipped">Shipped</option>
                    <option value="not-shipped">Not Shipped</option>
                </select>
            </span>
            <span class="option_space">
                <i class="fa fa-calendar" aria-hidden="true"></i> Dates:
                Previous
                <label class="slider-container">
                    <input type="checkbox" class="shipped_toggle_bulk" id="report_dates" checked/>
                    <span class="slider-span"></span>
                </label>
                Current
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
            <span class="option_space">
                <a class="button" id="print_letters" href="medal_letter_printout.php" target="_blank">
                    <i class="fa fa-print" aria-hidden="true"></i> Print Missing Medal Letters
                    <i class="fa fa-question-circle" aria-hidden="true" data-toggle="tooltip" title="This will print a letter for all children who's medals where not shipped"></i>
                </a>
            </span>
        </div>
        
        <hr style="display: block;"/>
        
        <div id="report"></div>
        
        
        <script>
        var debug = <?= $debug ? "true" : "false" ?>;// debug mode?
        var admin = <?= $admin_user['auth'] == 'super' ? "true" : "false" ?>;// admin mode?
        function get_report_options(){ // used in report.js
            return {
                school_id: $("select#school_id").val(),
                shipments: {
                    ranks: $("input#ranks")[0].checked,
                    medals: $("input#medals")[0].checked
                },
                shipping_status: $("select#shipping-status").val(),
                report_dates: $("#report_dates")[0].checked ? "current" : "previous",
                sort: $("select#sort").val(),
                sort_desc: $("input#sort-desc")[0].checked
            };
        }
        $( "#print_letters" ).click( function(event) {
            event.preventDefault(); // prevent the browser from opening the link
            
            var data = {
                shipping_status: "shipped",
                shipments: {
                    medals: true
                },
                report_dates: $("#report_dates")[0].checked ? "current" : "previous"
            };
            // set the data as the get params and open in new tab.
            var params = $.param(data);// paramaratize the data from the generated report
            var url = event.target.href + "?" + (debug ? "debug=true&" : "") + params; // genearate the url
            window.open(url, '_blank'); // open it in a new tab
        });
        </script>
        <script src="../js/shipments.js?v=1.1"></script>
        <script src="../js/report.js?v=1.1.7"></script>
        <? include(dirname(__FILE__)."/../parts/shipment_modal.php"); ?>
    </body>
</html>