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
        <title>Tzivos Hashem | Hachayol Reports Home Page</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/raffles/shared/styles/action-links.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Hachayol Reports</h1>
        
        <?if ($admin_user['auth'] == 'super') {?>
        <h2>Hachayol Shipping Reports</h2>
        <div id="action-links">
            <a href="/hachayol_report.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/cth_logo.png" height="32" alt="tickets"/>
                    <span class="link-text">Hachayol Report</span>
                </div>
            </a>
            <a href="/hachayol_report_details.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/cth_logo.png" height="32" alt="tickets"/>
                    <span class="link-text">Hachayol Detail Report</span>
                </div>
            </a>
            <a href="/reports/shipping/hachayols/<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/box.png" height="32" alt="tickets"/>
                    <span class="link-text">Hachayol Shipping Report</span>
                </div>
            </a>
        </div>
        <h2>MyShliach</h2>
        <div id="action-links">
            <a href="/myShliachHachayolReport.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/myshliach.png" height="32" alt="tickets"/>
                    <span class="link-text">MyShliach Hachayol Report</span>
                </div>
            </a>
            <a href="/myShliachHachayolLabels.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/myshliach.png" height="32" alt="tickets"/>
                    <span class="link-text">MyShliach Label Report</span>
                </div>
            </a>
        </div>
        <h2>Other</h2>
        <div id="action-links">
            <a href="/raffles/shared/forms/winners_hachayol_form.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_auction.png" height="32" alt="tickets"/>
                    <span class="link-text">Raffle Winners</span>
                </div>
            </a>
            <a href="hachayol_names.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_admin_home.png" height="32" alt="tickets"/>
                    <span class="link-text">Hachayol School Names</span>
                </div>
            </a>
        </div>
        <?} // end admin only links ?>
        </div>
        
    </body>
</html>