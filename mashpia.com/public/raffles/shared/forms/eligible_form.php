<?
// enable debuging
if ($_GET['debug']) {
    //error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
}
$admin_auth = array('school'); 
require($_SERVER["DOCUMENT_ROOT"].'/header.php');

/***************** EXTERNAL DEPENDENCIES **********************/
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
// load the required files
require_once(dirname(__FILE__).'/../classes/Raffle.php');
require_once(dirname(__FILE__).'/../classes/Constants.php');

// load the schools
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

use raffles\weekly\Raffle as Raffle; // use the raffle from its namespace
use raffles\shared\Constants as Constants;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Raffles: Manually Eligible Students</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css">
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <style type='text/css'>
            table {font-size: 12px; width: 100%;}
            tr {border-bottom: 1px solid #aaa;}
            th, td {padding: 3px 10px;white-space: nowrap;}
            select {
                background: url(/images/bg_smallButton.png) repeat-x scroll 0 0 #e6e5e5;
                border-color: #D3D3D3 #AAAAAA #888888;
                padding: 4px 8px;
                box-shadow: 0px 4px 10px #aaa;
                transition: box-shadow .1s linear;
                width: 32%;
            }
            select:hover{
                box-shadow: 0px 2px 8px #555;
                cursor: pointer;
            }
            div.dropdowns {
                text-align: center;
                margin-bottom: 10px;
            }
            div.dropdowns a {
                display: inline-block;
            }
            td.green{ color: green; }
            td.red{ color: red; }
            @media print {
                .col_content > p, .col_content > h1, .col_content > h2, .col_content > .dropdowns, .col_content > div.print {
                    display: none;
                }
            }
            img.flag {
                margin-top: -80px;
                margin-bottom: -80px;
                width: 250px;
                pointer-events: none;
            }
            img.marathonLogo {
                margin-top: -100px; 
                margin-bottom: -100px; 
                width: 650px;
                pointer-events: none;
            }
        </style>
    </HEAD>

    <BODY>
        <? include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
        <h1>Raffles: Manually Eligible Students</h1>

        <div class="print" align="center">
            <a class="button" onclick="window.print();"><i class="fa fa-print" aria-hidden="true"></i> Print Report</a>
        </div>
        <br /><br />
        
        <p>Please click <a href="https://docs.google.com/document/d/1FKrfoxTUTORwW9lO3Kx0mUY9wVeCBFCn0noQzVoj4q8/edit?usp=sharing">here</a> for the Rewards Manual</p>
        
        <h2>Please select the raffle and manually override the students that are eligible</h2>
        <p>Please note that checking off a student will make them eligible regardless of missions achieved unless they have already won.</p>
        
        <div class="dropdowns">
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
        </div>
        <div class="dropdowns">
            <select id="type" name="type">
                <option value="" disabled selected >Select a Raffle Type</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
            </select>
            <span id="raffle_select_container"></span>
        </div>
        <div class="dropdowns">
            <strong>Form Type: </strong>
            <input type="radio" name="report_type" value="override" checked/> Override
            <input type="radio" name="report_type" value="report"/> Report
        </div>
        <div class="dropdowns">
            <a class="button" id="generate"><i class="fa fa-refresh" aria-hidden="true"></i> Generate/Refresh Report</a>
        </div>
        <div id="eligible_list_container"></div>
        
        
        <script>
            var test_mode = <?=isset($_GET['test']) ? "true" : "false";?>;
            var debug_mode = <?=isset($_GET['debug']) ? "true" : "false";?>;
        </script>
        <script src="/raffles/shared/js/user_eligible.js"></script>
    </body>
</html>