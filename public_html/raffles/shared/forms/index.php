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
        <title>Tzivos Hashem | Raffles Home Page</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/raffles/shared/styles/action-links.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Tzivos Hashem Raffle System</h1>
        
        <h2>Documents</h2>
        <p style="text-align: center">
            Please click <a href="https://docs.google.com/document/d/1FKrfoxTUTORwW9lO3Kx0mUY9wVeCBFCn0noQzVoj4q8/edit?usp=sharing">here</a> for the Rewards Manual
        </p>
        
        <h2>Weekly / Monthly Raffles</h2>
        <div id="action-links">
            <a href="eligible_form.php">
                <div class="button">
                    <img src="/images/icon_profile.png" height="32" alt="tickets"/>
                    <span class="link-text">Eligible Students</span>
                </div>
            </a>
            <a href="winners_form.php">
                <div class="button">
                    <img src="/images/icon_admin_medal.png" height="32" alt="medal"/>
                    <span class="link-text">Winners</span>
                </div>
            </a>
        </div>
        <h2>Yearly Raffle</h2>
        <div id="action-links">
            <a href="../../yearly/eligibility_report.php">
                <div class="button">
                    <img src="/images/icon_profile.png" height="32" alt="tickets"/>
                    <span class="link-text">Student Eligibility Report</span>
                </div>
            </a>
            <a href="../../yearly/eligibility_report_hq.php">
                <div class="button">
                    <img src="/images/icon_auction.png" height="32" alt="tickets"/>
                    <span class="link-text">Eligible Students only</span>
                </div>
            </a>
            <a href="../../yearly/printout/">
                <div class="button">
                    <img src="/images/parentIcons/Printer.gif" height="32" alt="tickets"/>
                    <span class="link-text">Print Posters</span>
                </div>
            </a>
        </div>
        <?if ($admin_user['auth'] == 'super') {?>
            <h2>Administration Forms</h2>
            <div id="action-links">
                <a href="../../weekly/forms/prize_form.php">
                    <div class="button">
                        <img src="/images/icon_admin_medal.png" height="32" alt="medal"/>
                        <span class="link-text">Weekly Prizes</span>
                    </div>
                </a>
                <a href="/admin_prize_auction.php">
                    <div class="button">
                        <img src="/images/icon_admin_medal.png" height="32" alt="medal"/>
                        <span class="link-text">Monthly Prizes</span>
                    </div>
                </a>
                <br/>
                <a href="raffle_form.php">
                    <div class="button">
                        <img src="/images/icon_auction.png" height="32" alt="tickets"/>
                        <span class="link-text">Raffles</span>
                    </div>
                </a>
            </div>
            <h2>Administration Reports</h2>
            <div id="action-links">
                <a href="winners_hachayol_form.php">
                    <div class="button">
                        <img src="/images/icon_admin_medal.png" height="32" alt="medal"/>
                        <span class="link-text">Hachayol Winners</span>
                    </div>
                </a>
                <a href="winners_shamai_form.php">
                    <div class="button">
                        <img src="/images/icon_admin_medal.png" height="32" alt="medal"/>
                        <span class="link-text">Video Winners</span>
                    </div>
                </a>
            </div>
        <?} // end admin only links ?>
        </div>
        
    </body>
</html>