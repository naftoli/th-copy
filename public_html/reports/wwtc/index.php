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
        <title>Tzivos Hashem | WWTC Reports Home Page</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/raffles/shared/styles/action-links.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>WWTC Reports</h1>
        
        <!--<h2>Documents</h2>
        <p style="text-align: center">
            Please click <a href="https://docs.google.com/document/d/1FKrfoxTUTORwW9lO3Kx0mUY9wVeCBFCn0noQzVoj4q8/edit?usp=sharing">here</a> for the Rewards Manual
        </p>-->
        
        <!--<h2>Forms and Reports</h2>-->
        <?if ($admin_user['auth'] == 'super') {?>
        
        <div id="action-links">
            <a href="/choose_sm_report.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/cth_logo.png" height="32" alt="tickets"/>
                    <span class="link-text">Hachayol Report</span>
                </div>
            </a>
            <a href="/shabbos_mevorchim_col.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="tickets"/>
                    <span class="link-text">COL Report</span>
                </div>
            </a>
            <a href="/shabbos_mevorchim_hq.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/parentIcons/Campaigns.gif" height="32" alt="tickets"/>
                    <span class="link-text">Tehillim Stats</span>
                </div>
            </a>
        </div>
        <h2>Whatsapp</h2>
        <div id="action-links">
            <a href="teacher_whatsapp.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/whatsapp.png" height="32" alt="tickets"/>
                    <span class="link-text">Whatsapp Teachers</span>
                </div>
            </a>
            <a href="/tehillim_whatsapp.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/whatsapp.png" height="32" alt="tickets"/>
                    <span class="link-text">Whatsapp Report</span>
                </div>
            </a>
            <a href="/tehillim_whatsapp_summary.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/whatsapp.png" height="32" alt="tickets"/>
                    <span class="link-text">Whatsapp Summary</span>
                </div>
            </a>
        </div>
        <h2>Settings</h2>
        <div id="action-links">
            <a href="/admin_users_track.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="tickets"/>
                    <span class="link-text">View Ladders</span>
                </div>
            </a>
        </div>
        <h2>Special Reports</h2>
        <div id="action-links">
            <a href="shterna_year_long_report.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="tickets"/>
                    <span class="link-text">Shterna Yearly Report</span>
                </div>
            </a>
        </div>
        <?} // end admin only links ?>
        </div>
        
    </body>
</html>