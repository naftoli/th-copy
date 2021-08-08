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
        <title>Tzivos Hashem | Promotion | Rallies</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/raffles/shared/styles/action-links.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Rallies</h1>
        
        <h2>Rank Promotions</h2>
        <div id="action-links">
            <a href="/promotion_pics/promotion_pic_sergeant.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/reg/images/medals/Avos.gif" height="32" alt="metal"/>
                    <span class="link-text">Sergeant</span>
                </div>
            </a>
            <a href="/promotion_pics/promotion_pic_sergeant_major.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/reg/images/medals/Avos.gif" height="32" alt="metal"/>
                    <span class="link-text">Sergeant Major</span>
                </div>
            </a>
            <a href="/promotion_pics/promotion_pic_second_lieutenant.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/reg/images/medals/Avos.gif" height="32" alt="metal"/>
                    <span class="link-text">Second Lieutenant</span>
                </div>
            </a>
            <a href="/promotion_pics/promotion_pic_first_lieutenant.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/reg/images/medals/Avos.gif" height="32" alt="metal"/>
                    <span class="link-text">First Lieutenant</span>
                </div>
            </a>
            <a href="/promotion_pics/promotion_pic_captain.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/reg/images/medals/Avos.gif" height="32" alt="metal"/>
                    <span class="link-text">Captain</span>
                </div>
            </a>
            <a href="/promotion_pics/promotion_pic_major.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/reg/images/medals/Avos.gif" height="32" alt="metal"/>
                    <span class="link-text">Major</span>
                </div>
            </a>
            <a href="/promotion_pics/promotion_pic_colonel.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/reg/images/medals/Avos.gif" height="32" alt="metal"/>
                    <span class="link-text">Colonel</span>
                </div>
            </a>
            <a href="/promotion_pics/promotion_pic_general.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/reg/images/medals/Avos.gif" height="32" alt="metal"/>
                    <span class="link-text">General</span>
                </div>
            </a>
            <a href="/promotion_pics/promotion_pic_general1.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/reg/images/medals/Avos.gif" height="32" alt="metal"/>
                    <span class="link-text">1 Star General</span>
                </div>
            </a>
            <a href="/promotion_pics/promotion_pic_general2.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/reg/images/medals/Avos.gif" height="32" alt="metal"/>
                    <span class="link-text">2 Star General</span>
                </div>
            </a>
            <a href="/promotion_pics/promotion_pic_general3.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/reg/images/medals/Avos.gif" height="32" alt="metal"/>
                    <span class="link-text">3 Star General</span>
                </div>
            </a>
            <a href="/promotion_pics/promotion_pic_general4.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/reg/images/medals/Avos.gif" height="32" alt="metal"/>
                    <span class="link-text">4 Star General</span>
                </div>
            </a>
            <a href="/promotion_pics/promotion_pic_general5.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/reg/images/medals/Avos.gif" height="32" alt="metal"/>
                    <span class="link-text">5 Star General</span>
                </div>
            </a>
        </div>
    </body>
</html>