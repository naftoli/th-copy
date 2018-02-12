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
        <title>Tzivos Hashem | Sticker Reports Home Page</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/raffles/shared/styles/action-links.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Sticker Reports</h1>
        
        <div id="action-links">
            <a href="/stickers_report.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/stickers/Sticker-3353255578.gif" height="32" alt="tickets"/>
                    <span class="link-text">Stickers Earned</span>
                </div>
            </a>
            <a href="/stickers_report_by_week.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/img_new/calendar-color-gray-svg.svg" height="32" alt="tickets"/>
                    <span class="link-text">Stickers Earned By Week</span>
                </div>
            </a>
            <a href="/stickers_report_by_child.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_profile.png" height="32" alt="tickets"/>
                    <span class="link-text">Stickers Earned By Child</span>
                </div>
            </a>
        </div>
        <?if ($admin_user['auth'] == 'super') {?>
        <?} // end admin only links ?>
        </div>
        
    </body>
</html>