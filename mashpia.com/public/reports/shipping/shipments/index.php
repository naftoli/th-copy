<?php
// enable debuging
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

/***************** IMPORTS **********************/
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

// only superusers can use this page. Non superusers get redirected to the page that they can use
//if ($admin_user['auth'] != 'super') {
//    header("Location: /raffles/shared/forms/eligible_form.php");
//}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Shipments</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/fancy-checkbox.css" rel="stylesheet" type="text/css"/>
        <link href="/raffles/shared/styles/shipping/grey_slider.css" rel="stylesheet" type="text/css"/>
        <link href="../css/shipping_form.css" rel="stylesheet" type="text/css"/>
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <link href="/styles/admin/modal.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/forms.css" rel="stylesheet" type="text/css"/>
        <style>div#shipments_report h1 {margin-top: 10px;margin-bottom: 15px;} a.button{display: inline-block}</style>
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Tzivos Hashem Shipments</h1>
        
        <h2>Report Options</h2>
        <? if(count($schools) == 1) {?>
            <select id="school_id" name="school_id" class="hidden"  disabled>
                <option value="<?=array_keys($schools)[0]?>"><?=array_values($schools)[0]?></option>
            </select>
        <? } else { ?>
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
        <? } ?>
        
        <div class="options bordered">
            <span id="shipments">
                <i class="fa fa-filter" aria-hidden="true"></i> Status:
                <span class="option_space" style="<?//$admin_user['auth'] == 'super' ? "" : "display: none"?>">
                    <label class="fancy-check-container">
                        <input id="archived" type="checkbox" /> <span class="fancy-check"></span>
                    </label> <i class="fa fa-file-archive-o" aria-hidden="true"></i> Archived
                </span>
                <span class="option_space">
                    <label class="fancy-check-container">
                        <input id="delivered" type="checkbox" checked /> <span class="fancy-check"></span>
                    </label> <i class="fa fa-archive" aria-hidden="true"></i> Delivered
                </span>
                <span class="option_space">
                    <label class="fancy-check-container">
                        <input id="in_transit" type="checkbox" checked/> <span class="fancy-check"></span>
                    </label> <i class="fa fa-truck" aria-hidden="true"></i> In-Transit
                </span>
                <span class="option_space">
                    <label class="fancy-check-container">
                        <input id="planned" type="checkbox" checked/> <span class="fancy-check"></span>
                    </label> <i class="fa fa-clock-o" aria-hidden="true"></i> Planned
                </span>
            </span>
        </div>
        
        <div class="options bordered" id="filters">
            <span class="option_space">
                <i class="fa fa-calendar" aria-hidden="true"></i> Date Shipped:
                From <input type="date" name="start_date" id="start_date" placeholder="mm/dd/yyyy" value="2017-07-01"> 
                To <input type="date" name="end_date" id="end_date" placeholder="mm/dd/yyyy" value="<?//date("Y-m-d")?>">
            </span>
        </div>
        
        <div class="options">
            <span class="option_space">
                <a class="button" id="generate"><i class="fa fa-refresh" aria-hidden="true"></i> Refresh Report</a>
            </span>
<!--            <span class="option_space">
                <a class="button" href="printout.php" id="print"><i class="fa fa-print" aria-hidden="true"></i> Generate and Print</a>
            </span>-->
            <span class="option_space">
                <a class="button" id="create_shipment" data-shipped_only="true">
                    <i class="fa fa-plus" aria-hidden="true"></i> Create Shipment
                    <? if($admin_user['auth'] == 'super'){?>
                        <i class="fa fa-question-circle" aria-hidden="true" data-toggle="tooltip" title="Please note that you have to select a school for this to work"></i>
                    <?}?>
                </a>
            </span>
        </div>
        
        <hr style="display: block;"/>
        
        <div id="shipments_report"></div>
        <script>
            var debug = <?=$debug ? "true" : "false"?>;
            var admin = <?= $admin_user['auth'] == 'super' ? "true" : "false" ?>;// admin mode?
        </script>
        <script src="../js/shipment_report.js"></script>
        <? include(dirname(__FILE__)."/../parts/shipment_modal.php"); ?>
    </body>
</html>