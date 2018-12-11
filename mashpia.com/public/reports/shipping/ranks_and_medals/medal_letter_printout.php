<?php
include(dirname(__FILE__)."/../parts/shipping_header.php");
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Missing Medal Letters</title>
        <style>
            .page-break {page-break-after: always;}
            p.large{font-size: 1.3em;line-height: 1.4;}
            p.school-name{margin-bottom: 50px; text-align: center; font-size: .9em;display: none;}
            @media print {
                p.school-name{display: block;}
            }
            div.letter{font-size: 1.5em;}
        </style>
    </head>
    <body>
        <h1>Tzivos Hashem Missing Medals Report</h1>
        <p class="large">
            <strong>Report Dates: </strong>
            From <?=$report->getHeReportDates()['start_he']?> To <?=$report->getHeReportDates()['end_he']?>
        </p>
        <p>
            <strong>Disclaimer:</strong> This report will print all medals for the report generated that are <strong>marked as not shipped</strong>.
            To generate letters for <strong>everyone who did earn a specific medal during this time</strong> please visit <a href="/reports/ranks/missing_medals.php">this report</a>.
        </p>
        <div class="page-break"></div>
<?
/***************** RENDER THE PAGE **********************/
foreach($schools as $school_id => $school_name) {
    // get the  medals and ranks for this school
    $school_medals = $medals[$school_id];
    
    foreach($schoolsUsers[$school_id] as $user) {
        if(!isset($school_medals[$user['user_id']])) continue; // make sure we even have shipments for the user...
        
        $shipments = filter_shipping_status($school_medals[$user['user_id']], "not-shipped");
        // print_r($shipments);
        if (count($shipments) == 0) continue;
        ?>
        
        <p class="school-name">
            <?=$school_name?>, <?=$user['class_grade'].($user['class_sub'] ? " - ". $user['class_sub'] : "")?>
        </p>
        
        <div class="letter">
            <p>Dear <?=$user['first']?> <?=$user['last']?>,</p>
            
            <p>
                This year, you have earned more medals than ever before--a total of 51,913.
                So many medals have been earned that here at Tzivos Hashem Headquarters we can't keep up--we've actually run out of medals.
                New ones are being created right now at our factories in China, and then they'll travel to Headquarters on a huge freight ship.
                Once they arrive, they'll be mailed to your school right away.
                Thanks for your patience, and keep on bringing Moshiach with all of your missions.
            </p>
            
            <p>- Rabbi Shimmy Weinbaum</p>
        </div>
        
        <div class="page-break"></div>
        
    <? }
} ?>