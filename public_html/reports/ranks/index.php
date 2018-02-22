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
// only superusers can use this page. Non superusers get redirected to the page that they can use
//if ($admin_user['auth'] != 'super') {
//    header("Location: /raffles/shared/forms/eligible_form.php");
//}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Rank Reports Home Page</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/raffles/shared/styles/action-links.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Rank Reports</h1>
        
        <!--<h2>Documents</h2>
        <p style="text-align: center">
            Please click <a href="https://docs.google.com/document/d/1FKrfoxTUTORwW9lO3Kx0mUY9wVeCBFCn0noQzVoj4q8/edit?usp=sharing">here</a> for the Rewards Manual
        </p>-->
        
        <!--<h2>Forms and Reports</h2>-->
        <?if ($admin_user['auth'] == 'super') {?>
        <h2>Ranks And Medals</h2>
        <div id="action-links">
            <a href="/admin_received_stats.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/box.png" height="32" alt="tickets"/>
                    <span class="link-text">Rank And Medal Status Report</span>
                </div>
            </a>
        </div>
        <h2>Ranks</h2>
        <div id="action-links">
            <a href="/rank_report.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/img_new/achievements-color-orange-svg.svg" height="32" alt="tickets"/>
                    <span class="link-text">Rank Progress Report</span>
                </div>
            </a>
            <a href="/ranks_earned_summary.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="tickets"/>
                    <span class="link-text">Ranks Earned By Year</span>
                </div>
            </a>
            <a href="/student_rank_report.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/img_new/calendar-color-gray-svg.svg" height="32" alt="tickets"/>
                    <span class="link-text">Ranks Earned Between Dates</span>
                </div>
            </a>
        </div>
        <h2>Medals</h2>
        <div id="action-links">
            <a href="/medals_earned_summary.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="tickets"/>
                    <span class="link-text">Medals Earned By Year</span>
                </div>
            </a>
            <a href="missing_medals.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/file_view.php?id=1054359804" height="32" alt="tickets"/>
                    <span class="link-text">Missing Medals Printout Report</span>
                </div>
            </a>
        </div>
        <?} // end admin only links ?>
        </div>
        
    </body>
</html>