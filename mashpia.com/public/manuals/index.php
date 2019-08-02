<?php
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
        <title>Tzivos Hashem | Manuals</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/raffles/shared/styles/action-links.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Manuals</h1>
        
        <h2>Links to Manuals</h2>
        <div id="action-links">
            <a href="https://docs.google.com/document/d/1GIFYrddcmW81NUDuSj5S3eWMQyMl2f2Rm3Ja5mvtwv0/edit?usp=sharing">
                <div class="button">
                    <img src="/images/icon_profile.png" height="32" alt="tickets"/>
                    <span class="link-text">Promotions Pictures & Ceremonies</span>
                </div>
            </a>
            <a href="https://docs.google.com/document/d/1FKrfoxTUTORwW9lO3Kx0mUY9wVeCBFCn0noQzVoj4q8/edit?usp=sharing">
                <div class="button">
                    <img src="/images/icon_profile.png" height="32" alt="tickets"/>
                    <span class="link-text">Mission Rewards & Store</span>
                </div>
            </a>
            <a href="https://docs.google.com/document/d/1u8wDZ3Gi7E-sJiyqj8HGO_jyDc7GjRkp1gsx9fBpMbY/edit?usp=sharing">
                <div class="button">
                    <img src="/images/icon_profile.png" height="32" alt="tickets"/>
                    <span class="link-text">Tanya & Mishnayos Baal Peh</span>
                </div>
            </a>
            <a href="https://docs.google.com/document/d/1qJxAdDrhJ3QIrBixycAPmkwLuskcSRCMRvLpCvAF9vs/edit?usp=sharing">
                <div class="button">
                    <img src="/images/icon_profile.png" height="32" alt="tickets"/>
                    <span class="link-text">Connection Point</span>
                </div>
            </a>
            <a href="https://docs.google.com/document/d/138BVOQ6GeyOxPx18gjkBFasbUuKiz8BW2qjMCaVjlkQ/edit">
                <div class="button">
                    <img src="/images/icon_profile.png" height="32" alt="tickets"/>
                    <span class="link-text">Mivtza Neshek</span>
                </div>
            </a>
            <a href="https://docs.google.com/document/d/1FkSQkRwnBtKXQ_Bbv54MprK9e8fSBRABM6houTMGwKg/edit#heading=h.67mwmp63ouiy">
                <div class="button">
                    <img src="/images/icon_profile.png" height="32" alt="tickets"/>
                    <span class="link-text">Viholachta Bidrachov</span>
                </div>
            </a>
            <a href="https://docs.google.com/document/d/1fGI4Dmd3hoD03dJhrSXOoThzEcb_IjIi22c-vLQorWg/edit#heading=h.1n4ea5c9m44g">
                <div class="button">
                    <img src="/images/icon_profile.png" height="32" alt="tickets"/>
                    <span class="link-text">Shabbos Mevarchim Tehillim</span>
                </div>
            </a>
            <a href="https://docs.google.com/document/d/1h8VTGK-Tj9hC1Wb_jkE4TDmQD3fodXoa0u4X2o8WxAk/edit#heading=h.s7tewwcu2zgr">
                <div class="button">
                    <img src="/images/icon_profile.png" height="32" alt="tickets"/>
                    <span class="link-text">CTH Registration</span>
                </div>
            </a>
            <a href="https://docs.google.com/document/d/1jGVsCyc8F9VY2pXIACFuFcf4xjz4BpSaf8ijRLtYATo/edit#heading=h.otv8xuv33x70">
                <div class="button">
                    <img src="/images/icon_profile.png" height="32" alt="tickets"/>
                    <span class="link-text">Chidon</span>
                </div>
            </a>
            <a href="https://docs.google.com/document/d/1K-h4PaWRFb3y4cCNDpoRBaUngJbTpHR_ucAveA6a-GM/edit#heading=h.3i38t9cowumh">
                <div class="button">
                    <img src="/images/icon_profile.png" height="32" alt="tickets"/>
                    <span class="link-text">Rallies</span>
                </div>
            </a>
            <a href="https://docs.google.com/document/d/1cQWsy5xbKEVXmGZBDovjTx0esRxX8bcFkp4J4VLgx3A/edit">
                <div class="button">
                    <img src="/images/icon_profile.png" height="32" alt="tickets"/>
                    <span class="link-text">Contests</span>
                </div>
            </a>
            <a href="https://docs.google.com/document/d/1Swrhh85DEqtrnCrs6VwQpioL_Jq41j3rhCFbQOairtQ/edit?usp=sharing">
                <div class="button">
                    <img src="/images/icon_profile.png" height="32" alt="tickets"/>
                    <span class="link-text">Mivtza Cheder Tzivos Hashem</span>
                </div>
            </a>
            <a href="https://docs.google.com/document/d/1QCvr2cAdxFFVNhGphnT4DnF9WkSF5RFr8XpXVXyf3Yc/edit?usp=sharing">
                <div class="button">
                    <img src="/images/icon_profile.png" height="32" alt="tickets"/>
                    <span class="link-text">Mivtza Matzah</span>
                </div>
            </a>
        </div>        
    </body>
</html>