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
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Base Reports Home Page</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/raffles/shared/styles/action-links.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Base Reports</h1>
        
        <!--<h2>Documents</h2>
        <p style="text-align: center">
            Please click <a href="https://docs.google.com/document/d/1FKrfoxTUTORwW9lO3Kx0mUY9wVeCBFCn0noQzVoj4q8/edit?usp=sharing">here</a> for the Rewards Manual
        </p>-->
        
        <!--<h2>Forms and Reports</h2>-->
        <?if ($admin_user['auth'] == 'super') {?>
        <h2>Base Reports</h2>
        <div id="action-links">
            <a href="/types_of_schools.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_admin_home.png" height="32" alt="tickets"/>
                    <span class="link-text">Base Type</span>
                </div>
            </a>
            <a href="/school_list.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/parentIcons/logout.gif" height="32" alt="tickets"/>
                    <span class="link-text">Base Commander Logins</span>
                </div>
            </a>
            
            <a href="/admin_school_logos.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/img_new/default-boys-school-logo.png" height="32" alt="tickets"/>
                    <span class="link-text">Base Logos</span>
                </div>
            </a>
        </div>

        <h2>Teachers</h2>
        <div id="action-links">
            <a href="/teacher_report.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/v2/images/back-end/admin/Teachers resources icon.jpg" height="32" alt="tickets"/>
                    <span class="link-text">Teacher Report</span>
                </div>
            </a>
            <a href="/teacher_list.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/v2/images/back-end/admin/icon_control.png" height="32" alt="tickets"/>
                    <span class="link-text">Teacher Logins</span>
                </div>
            </a>
        </div>

        <h2>Teacher Certificates</h2>
        <div id="action-links">
            <a href="/rally_certs/certs.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/v2/images/back-end/admin/Teachers resources icon.jpg" height="32" alt="tickets"/>
                    <span class="link-text">Teacher Certificates</span>
                </div>
            </a>
        </div>
        <?} // end admin only links ?>
        </div>
        
    </body>
</html>