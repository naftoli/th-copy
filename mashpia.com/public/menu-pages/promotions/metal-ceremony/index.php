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
        <title>Tzivos Hashem | Promotion | Medal Ceremony</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/raffles/shared/styles/action-links.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Medal Ceremony</h1>
        
        <h2>Medal Packing & Labels</h2>
        <div id="action-links">
            <a href="/medals_summary_report.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/reg/images/medals/Avos.gif" height="32" alt="metal"/>
                    <span class="link-text">Medals Summary Report by Base</span>
                </div>
            </a>    
            <a href="/school_labels.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/reg/images/medals/Avos.gif" height="32" alt="metal"/>
                    <span class="link-text">Base Labels</span>
                </div>
            </a>
            <a href="/medals_labels.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/reg/images/medals/Avos.gif" height="32" alt="metal"/>
                    <span class="link-text">Chayolim Medals Labels</span>
                </div>
            </a>
            <a href="/myShliachShipLabels.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/mobile/reg/images/medals/Avos.gif" height="32" alt="metal"/>
                    <span class="link-text">MyShliach Medals Labels</span>
                </div>
            </a>
            <a href="/anashShipLabels.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                <img src="/mobile/reg/images/medals/Avos.gif" height="32" alt="metal"/>
                    <span class="link-text">Anash Kinder Medals Labels</span>
                </div>
            </a>
        </div>

        <h2>Video</h2>
        <div id="action-links">
            <a href="/rank_ceremony/<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="report"/>
                    <span class="link-text">Individual Rank Ceremony for All Bases</span>
                </div>
            </a>
            
            <div class="button" style="display: inline-flex;">
                <a href="/rank_ceremony?<?=$debug ? "debug=true&": "";?>id=" id="rank_celemony_base_link" style="color: black; padding-right: 4px;">
                        <img src="/images/icon_report.png" height="32" alt="report"/>
                        <span class="link-text">Individual Rank Ceremony for Base ID</span>
                </a>
                <input id="rank_celemony_base_input" type="number" min="0" max="10000" oninput="
                    document.getElementById('rank_celemony_base_link').href = `/rank_ceremony?<?=$debug ? "debug=true&": "";?>id=${document.getElementById('rank_celemony_base_input').value}`;
                "/>
            </div>
        </div>

        <h2>Shipping</h2>
        <div id="action-links">
            <a href="/ranks_shipping.php/<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/box.png" height="32" alt="shipping-box"/>
                    <span class="link-text">Ranks Shipping Report</span>
                </div>
            </a>
            <a href="/medals_shipping.php<?=$debug ? "?debug=true": "";?>">
                <div class="button">
                    <img src="/images/box.png" height="32" alt="shipping-box"/>
                    <span class="link-text">Medals Shipping Report</span>
                </div>
            </a>
            <span>
                <a href="/isserRanks.php<?=$debug ? "?debug=true": "";?>">
                    <div class="button" style="display: inline-flex; align-items: center; column-gap: 4px">
                        <img src="/images/icon_report.png" height="32" alt="report"/>
                        <span class="link-text" style="margin-top: 0">New Promotions
                            <br>
                            <span style="color: #666; font-size: x-small;">All Ranks earned during most recent promotion dates <br>(formerly Isser’s Rank Report)</span>
                        </span>
                    </div>
                    <br>
                </a>
            </span>
        </div>
        
    </body>
</html>