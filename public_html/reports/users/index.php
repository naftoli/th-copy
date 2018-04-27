<?php $debug = false;
// enable debuging
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | User Reports Home Page</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/raffles/shared/styles/action-links.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>User Reports</h1>
        
        <!--<h2>Documents</h2>
        <p style="text-align: center">
            Please click <a href="https://docs.google.com/document/d/1FKrfoxTUTORwW9lO3Kx0mUY9wVeCBFCn0noQzVoj4q8/edit?usp=sharing">here</a> for the Rewards Manual
        </p>-->
        <div id="action-links">
            <a href="student_info.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_profile.png" height="32" alt="profile"/>
                    <span class="link-text">Student Info By Serial Number / Barcode</span>
                </div>
            </a>
        </div>
        <!--<h2>Forms and Reports</h2>-->
        <?if ($admin_user['auth'] == 'super') {?>
        <h2>Reports</h2>
        <div id="action-links">
            <a href="/registered_report.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="tickets"/>
                    <span class="link-text">Registered Report</span>
                </div>
            </a>
            <a href="/non_registered_report.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="tickets"/>
                    <span class="link-text">Not Registered Report</span>
                </div>
            </a>
            <a href="/barcodes_report.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/img_new/scanner-1-color-red.svg" height="32" alt="tickets"/>
                    <span class="link-text">Barcode Report</span>
                </div>
            </a>
            <a href="/miles.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/stickers/Sticker-3526731090.gif" height="32" alt="tickets"/>
                    <span class="link-text">Miles Report</span>
                </div>
            </a>
            <a href="/missions_report.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/img_new/square-check-color-green-svg.svg" height="32" alt="tickets"/>
                    <span class="link-text">Missions Report</span>
                </div>
            </a>
            <a href="/parent_report.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_profile.png" height="32" alt="tickets"/>
                    <span class="link-text">Parent Report</span>
                </div>
            </a>
            <a href="../created_users.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="tickets"/>
                    <span class="link-text">Created Users</span>
                </div>
            </a>
        </div>
        
        <h2>Tools</h2>
        <div id="action-links">
            <a href="/mergeAccounts.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="tickets"/>
                    <span class="link-text">Merge Accounts</span>
                </div>
            </a>
            <a href="change_school.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_admin_home.png" height="32" alt="tickets"/>
                    <span class="link-text">Change School</span>
                </div>
            </a>
            <a href="/admin_users_register_hq.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="tickets"/>
                    <span class="link-text">Free Link</span>
                </div>
            </a>
            <a href="/manual_points.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="tickets"/>
                    <span class="link-text">Add / Subtract Points</span>
                </div>
            </a>
            <a href="/add_missions.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/img_new/square-check-color-purple-svg.svg" height="32" alt="tickets"/>
                    <span class="link-text">Add Missions</span>
                </div>
            </a>
            <a href="/add_medals.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/file_view.php?id=2117031709" height="32" alt="tickets"/>
                    <span class="link-text">Add Medals</span>
                </div>
            </a>
            <a href="/parent_children_letter.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="tickets"/>
                    <span class="link-text">Children Letters</span>
                </div>
            </a>
        </div>
        
        <h2>Parents</h2>
        <div id="action-links">
            <a href="parent_accounts.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="tickets"/>
                    <span class="link-text">Search Parent Accounts</span>
                </div>
            </a>
        </div>
        <?} // end admin only links ?>
        
    </body>
</html>