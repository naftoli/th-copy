<?php
require '../../../db.php';
$user = mysql_real_escape_string($_POST['user']);
$admin = mysql_real_escape_string($_POST['admin']);
$card = mysql_real_escape_string($_POST['card']);

require '../../reg/ajax/encrypt.php';
$admin = encrypt_decrypt('decrypt', $admin);

// make sure user is part of admin account if not in kiosk account
$authorized = false;
if (!isset($_COOKIE['kiosk']) && !isset($_COOKIE['user'])) {
    $sql = "select * from admin_auths where id = " . $user . " and admin_id = " . $admin . " and role_id = 1";
    $result = mysql_query($sql);
    if (mysql_num_rows($result) > 0) {
        $authorized = true;
    }
} else {
    $authorized = true;
}
if ($authorized) {
    $intUser = intval($user);
    $strSql = "
        SELECT
            user_code, school_id  
        FROM
            users
        WHERE
            user_id = " . $intUser;
    $objResult = mysql_query($strSql);
    $strBarCode = "3" . mysql_result($objResult, 0);
    $school_id = mysql_result($objResult, 1);
    $objCurl = curl_init();
    curl_setopt($objCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($objCurl, CURLOPT_COOKIESESSION, TRUE); 
    curl_setopt($objCurl, CURLOPT_HEADER, 0);
    curl_setopt($objCurl, CURLOPT_COOKIEFILE, "cookiefile");
    curl_setopt($objCurl, CURLOPT_COOKIEJAR, "cookiefile");
    curl_setopt($objCurl, CURLOPT_FOLLOWLOCATION, 1); 
    
    //if (in_array($school_id, $australian)) $strURL = "https://v2.mashpia.com/v2/kiosk/auto-login/uc/" . $strBarCode;
    //else $strURL = "https://mashpia.com/v2/kiosk/auto-login/uc/" . $strBarCode;
    $strURL = "https://mashpia.com/v2/kiosk/auto-login/uc/" . $strBarCode . "/encrypted/0";
    curl_setopt($objCurl, CURLOPT_URL, $strURL);
    curl_exec($objCurl);
    
    ob_start();
    //if (in_array($school_id, $australian)) $strURL = "https://v2.mashpia.com/kiosk-main/cardpop/card_id/" . $_GET["card_id"];
    //else $strURL = "https://mashpia.com/v2/kiosk-main/cardpop/card_id/" . $_GET["card_id"];
    //echo $strURL; exit;
    $strURL = "https://mashpia.com/v2/kiosk-main/cardpopmobile/card_id/" . $card;
    curl_setopt($objCurl, CURLOPT_URL, $strURL);
    
    if (isset($_POST["control"]))
    {
        curl_setopt($objCurl, CURLOPT_POST, 1);
        curl_setopt($objCurl, CURLOPT_POSTFIELDS, $_POST);
    }
    print curl_exec($objCurl);
    
    $strResult = ob_get_contents();
    curl_close($objCurl);
    ob_end_clean();
    print $strResult;
}
?>