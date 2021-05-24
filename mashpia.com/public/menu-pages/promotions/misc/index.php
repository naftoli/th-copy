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
        <title>Tzivos Hashem | Promotion | Misc</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/raffles/shared/styles/action-links.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Miscellaneous promotions links</h1>
        
        <div id="action-links">
            <a href="/shimmy_rank_report.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <span class="link-text">Rank Report</span>
                </div>
            </a>
            <a href="/admin_received_stats_all.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <span class="link-text">Soldier's Medal and Rank Report</span>
                </div>
            </a>
            <a href="/school_possible_medals.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <span class="link-text">School Possible Medals</span>
                </div>
            </a>
            <a href="/admin_rank.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <span class="link-text">Ranks</span>
                </div>
            </a>
            <a href="/promotions2.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <span class="link-text">Promotions from 5779</span>
                </div>
            </a>
            <a href="/promotions_shamai.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <span class="link-text">Promotions Shamai (used in automotive system)</span>
                </div>
            </a>
        </div>
    </body>
</html>