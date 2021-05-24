<?php $debug = false;
// enable debuging
if ( isset( $_GET['debug'] ) ) {
    //error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
// only superusers can use this page. Non superusers get redirected to the page that they can use
if ($admin_user['auth'] != 'super') {
    header("Location: /new");
    exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Promotion | Hachayol</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/raffles/shared/styles/action-links.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Hachayol</h1>
        
        <h2>Rank Promotions By Date</h2>
        <div id="action-links">
            <a href="/reports/ranks/rank_report.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/reg/images/medals/Avos.gif" height="32" alt="metal"/>
                    <span class="link-text">For Hachayol magazine</span>
                </div>
            </a>
        </div>
    </body>
</html>