<?php $debug = false;
// enable debuging
if ($_GET['debug']) {
    //error_reporting(E_ALL);
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
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Accounting Reports Home Page</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/raffles/shared/styles/action-links.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Accounting Reports</h1>
        
        <?if ($admin_user['auth'] == 'super') {?>
        <div id="action-links">
            <a href="registration_status.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="reports"/>
                    <span class="link-text">Base Registration</span>
                </div>
            </a>
            <a href="registration.php">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="reports"/>
                    <span class="link-text">Soldier Registration</span>
                </div>
            </a>
            <a href="registration_charges.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="reports"/>
                    <span class="link-text">Soldier Charges</span>
                </div>
            </a>
        </div>
        <div id="action-links">
            <a href="/reports/schools/registration_info.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="report"/>
                    <span class="link-text">Registration Settings</span>
                </div>
            </a>
            <a href="discounts.php">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="report"/>
                    <span class="link-text">School Discounts</span>
                </div>
            </a>
            <a href="create_user_discount.php">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="report"/>
                    <span class="link-text">Student Discounts</span>
                </div>
            </a>
            <a href="/admin_yearly.php">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="report"/>
                    <span class="link-text">Unregister Schools and Students</span>
                </div>
            </a>
            <a href="/admin_school_register_report.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="report"/>
                    <span class="link-text">Old Registration Report</span>
                </div>
            </a>
        </div>
        <div id="action-links">
            <a href="/types_of_schools.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="report"/>
                    <span class="link-text">School Types</span>
                </div>
            </a>
            <a href="/admin_shipping_report_02.php">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="report"/>
                    <span class="link-text">Registrations by Date/Time</span>
                </div>
            </a>
        </div>
        <?} // end admin only links ?>
        </div>
        
    </body>
</html>