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
        <title>Tzivos Hashem | Birthday Reports Home Page</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/raffles/shared/styles/action-links.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Birthday Reports</h1>
        
        <!--<h2>Documents</h2>
        <p style="text-align: center">
            Please click <a href="https://docs.google.com/document/d/1FKrfoxTUTORwW9lO3Kx0mUY9wVeCBFCn0noQzVoj4q8/edit?usp=sharing">here</a> for the Rewards Manual
        </p>-->
        
        <h2>Reports</h2>
        <div id="action-links">
            <a href="/names_report.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/img_new/cake-color-green-svg.svg" height="32" alt="tickets"/>
                    <span class="link-text">Birthday Report</span>
                </div>
            </a>
            <a href="/find_birthdays_report.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/img_new/calendar-color-gray-svg.svg" height="32" alt="tickets"/>
                    <span class="link-text">Birthdays By Date</span>
                </div>
            </a>
        </div>
        <h2>Tools</h2>
        <div id="action-links">
            <a href="/stickers_report_by_child.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="tickets"/>
                    <span class="link-text">Update Birthdays</span>
                </div>
            </a>
        </div>
        <?if ($admin_user['auth'] == 'super') {?>
        <?} // end admin only links ?>
        </div>
        
    </body>
</html>