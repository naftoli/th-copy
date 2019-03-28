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
if ($admin_user['auth'] != 'super') {
    header("Location: /reports/");
}
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
        <h1>Mishnos Reports</h1>
        
        <!--<h2>Forms and Reports</h2>-->
        <?if ($admin_user['auth'] == 'super') {?>
        <h2>Tools</h2>
        <div id="action-links">
            <a href="edit_mishnos.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/v2/images/back-end/admin/icon_control.png" height="32" alt="tickets"/>
                    <span class="link-text">Edit Mishnos</span>
                </div>
            </a>
        </div>
        <?} // end admin only links ?>
    </body>
</html>