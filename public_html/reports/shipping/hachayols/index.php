<?php
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

// get the current year...
require_once ($_SERVER["DOCUMENT_ROOT"].'/class.globalSettings.php');
$year = GlobalSettings::getCurrentYear();

// get the parshios for the current year...
require_once(dirname(__FILE__)."/../functions/get_parshos.php");
$parshos = get_parshos($year, false, (unixtojd() + 28)); // get all the parshios untill next month...

if($debug) echo "</pre>";?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Hachayol Shipping Report</title>
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
            td, th{min-width: 0px;}
            input.hachayol_shipped {width: 90px;text-align: center;background: no-repeat;border: none;border-bottom: 1px solid;font-size: 1.1em;}
        </style>
    </head>
    <body>
        <? include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
        
        <h1>Hachayol Shipping Report</h1>
        
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
        
        <!--<div class="options bordered" id="filters">
            <span class="option_space">
                <i class="fa fa-calendar" aria-hidden="true"></i> Registered From 
                <input type="date" name="start_date" id="start_date" placeholder="mm/dd/yyyy" value="2017-07-01"> To
                <input type="date" name="end_date" id="end_date" placeholder="mm/dd/yyyy" value="<?=date("Y-m-d")?>">
            </span>
        </div>-->
        
        <div class="options bordered" id="filters">
            <span class="option_space">
                <i class="fa fa-calendar" aria-hidden="true"></i> From Parsha: 
                <select id="start_date" name="start_date">
                    <? foreach ($parshos as $index => $parsha) { ?>
                    <option value="<?=date("Y-m-d", jdtounix($parsha['start']))?>" <?=$index + 1 == count($parshos) - 3 ? "selected" : ""?>><?=$parsha['name']?></option>
                    <? } // end foreach parsha ?>
                </select>
                To Parshas:
                <select id="end_date" name="end_date">
                    <? foreach ($parshos as $index => $parsha) { ?>
                    <option value="<?=date("Y-m-d", jdtounix($parsha['end']))?>" <?=$index + 1 == count($parshos) ? "selected" : ""?>><?=$parsha['name']?></option>
                    <? } // end foreach parsha ?>
                </select>
            </span>
        </div>
        
        <div class="options bordered">
            <span class="option_space">
                <i class="fa fa-truck" aria-hidden="true"></i> Shipped:
                <select id="shipping-status" name="shipping-status">
                    <option value="all">All</option>
                    <option value="shipped">Shipped</option>
                    <option value="not-shipped">Not Shipped</option>
                </select>
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
        function get_report_options(){
            return {
                school_id: $("select#school_id").val(),
                shipments: {
                    hachayols: true
                },
                shipping_status: $("select#shipping-status").val(),
                start_date: /*"2017-07-01",*/$("select#start_date").val(),
                end_date: $("select#end_date").val()
                //sort: $("select#sort").val(),
                //sort_desc: $("input#sort-desc")[0].checked
            };
        }
        </script>
        <script src="../js/shipments.js?v=2.0"></script>
        <script src="../js/report.js?v=2.0"></script>
        <? include(dirname(__FILE__)."/../parts/shipment_modal.php"); ?>
    </body>
</html>