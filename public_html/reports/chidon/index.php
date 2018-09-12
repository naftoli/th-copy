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
// only superusers can use this page. Non superusers get redirected to the page that they can use
//if ($admin_user['auth'] != 'super') {
//    header("Location: /raffles/shared/forms/eligible_form.php");
//}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Chidon Reports Home Page</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/raffles/shared/styles/action-links.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Chidon Reports</h1>
        
        <!--<h2>Documents</h2>
        <p style="text-align: center">
            Please click <a href="https://docs.google.com/document/d/1FKrfoxTUTORwW9lO3Kx0mUY9wVeCBFCn0noQzVoj4q8/edit?usp=sharing">here</a> for the Rewards Manual
        </p>-->
        
        <!--<h2>Forms and Reports</h2>-->
        <?if ($admin_user['auth'] == 'super') {?>
        <h2>Reporting Tools</h2>
        <div id="action-links">
            <a href="/chidon/reports/<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/v2/images/back-end/admin/icon_control.png" height="32" alt="tickets"/>
                    <span class="link-text">Generate (Custom) Reports</span>
                </div>
            </a>
            <a href="/chidon/reports/reports.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_dashboard.png" height="32" alt="tickets"/>
                    <span class="link-text">Pre-Generated Reports</span>
                </div>
            </a>
        </div>
        <h2>Other Reports</h2>
        <div id="action-links">
            <a href="/chidon/reports/registration.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="tickets"/>
                    <span class="link-text">Registration Report</span>
                </div>
            </a>
            <a href="/chidon_purchases.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_auction.png" height="32" alt="tickets"/>
                    <span class="link-text">Purchased Tickets Report</span>
                </div>
            </a>
            <a href="/chidon_report.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="tickets"/>
                    <span class="link-text">Grades / Learning Registered Report</span>
                </div>
            </a>
            <a href="shabbaton_enrollment.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="tickets"/>
                    <span class="link-text">Shabbaton Enrollment Report</span>
                </div>
            </a>
        </div>
        
        <h2>Tools</h2>
        <div id="action-links">
            <a href="/uploadChidonFile.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/v2/images/back-end/admin/icon_install.png" height="32" alt="tickets"/>
                    <span class="link-text">Chidon Uploading</span>
                </div>
            </a>
            <a href="/chidon/upload/<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/v2/images/back-end/admin/icon_install.png" height="32" alt="tickets"/>
                    <span class="link-text">Chidon Sheets Uploading</span>
                </div>
            </a>
            <a href="register_user.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/img_new/boy-color-green-svg.svg" height="32" alt="users"/>
                    <span class="link-text">Register User</span>
                </div>
            </a>
            <a href="staff.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/img_new/boy-color-green-svg.svg" height="32" alt="users"/>
                    <span class="link-text">Attendance Staff</span>
                </div>
            </a>
            <a href="walking_groups.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/img_new/boy-color-green-svg.svg" height="32" alt="users"/>
                    <span class="link-text">Walking Groups</span>
                </div>
            </a>
        </div>

        <h2>Booklets</h2>
        <div id="action-links">
            <a href="booklet_report.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/v2/images/back-end/admin/icon_install.png" height="32" alt="tickets"/>
                    <span class="link-text">Study Guide Report</span>
                </div>
            </a>
        </div>

        <h2>Yahadus Books</h2>
        <div id="action-links">
            <a href="yahadus.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/v2/images/back-end/admin/icon_install.png" height="32" alt="tickets"/>
                    <span class="link-text">Yahadus Book Purchases</span>
                </div>
            </a>
        </div>
        <?} // end admin only links ?>
        </div>
        
    </body>
</html>