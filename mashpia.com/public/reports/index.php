<?php $debug = false;
// enable debuging
if ( isset( $_GET['debug'] ) ) {
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
        <title>Tzivos Hashem | Reports Home Page</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/raffles/shared/styles/action-links.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Tzivos Hashem Reports</h1>
        
        <!--<h2>Documents</h2>
        <p style="text-align: center">
            Please click <a href="https://docs.google.com/document/d/1FKrfoxTUTORwW9lO3Kx0mUY9wVeCBFCn0noQzVoj4q8/edit?usp=sharing">here</a> for the Rewards Manual
        </p>-->
        
        <!--<h2>Forms and Reports</h2>-->
        <div id="action-links">
            <?if ($admin_user['auth'] == 'super') {?>
            <a href="wwtc/<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/parentIcons/Campaigns.gif" height="32" alt="tickets"/>
                    <span class="link-text">WWTC (Tehillim)</span>
                </div>
            </a>
            <?} // end admin only links ?>
            <a href="ranks/<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/reg/images/ranklogos/10.png" height="32" alt="tickets"/>
                    <span class="link-text">Ranks / Medals</span>
                </div>
            </a>
            <a href="schools/<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_star.png" height="32" alt="tickets"/>
                    <span class="link-text">Bases / Teachers</span>
                </div>
            </a>
        </div>
        <div id="action-links">
            <a href="shipping/<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/box.png" height="32" alt="tickets"/>
                    <span class="link-text">Shipping</span>
                </div>
            </a>
            <a href="chidon/<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/chidon.png" height="32" alt="tickets"/>
                    <span class="link-text">Chidon</span>
                </div>
            </a>
            <a href="users/<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_profile.png" height="32" alt="tickets"/>
                    <span class="link-text">Users / Parents</span>
                </div>
            </a>
            <a href="stickers/<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/stickers/Sticker-1043634244.gif" height="32" alt="tickets"/>
                    <span class="link-text">Stickers</span>
                </div>
            </a>
            <a href="hachayol/<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/cth_logo.png" height="32" alt="tickets"/>
                    <span class="link-text">Hachayols</span>
                </div>
            </a>
            <a href="birthdays/<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/img_new/cake-color-green-svg.svg" height="32" alt="tickets"/>
                    <span class="link-text">Birthdays</span>
                </div>
            </a>
            <a href="mishnos/<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="tickets"/>
                    <span class="link-text">Mishnos</span>
                </div>
            </a>
            <a href="/mivtzoim/admin/<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="tickets"/>
                    <span class="link-text">Mivtzoim</span>
                </div>
            </a>
        </div>
        <?if ($admin_user['auth'] == 'super') {?>
        <h2>Managment Links</h2>
        <div id="action-links">
            <a href="/blog/wp-admin">
                <div class="button">
                    <img src="/mobile/img_new/news-color-green-svg.svg" height="32" alt="tickets"/>
                    <span class="link-text">News Site Backend</span>
                </div>
            </a>
            <a href="registration">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="reports"/>
                    <span class="link-text">Registration Reports</span>
                </div>
            </a>
            <a href="/raffles/posters/weekly.php">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="reports"/>
                    <span class="link-text">Weekly Raffle Posters</span>
                </div>
            </a>
        </div>
        <?} // end admin only links ?>
        </div>
        
    </body>
</html>