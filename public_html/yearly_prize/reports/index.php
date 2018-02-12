<? $debug = false;
/***************** DEBUGGING SETTINGS **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
}

header("Location: eligible_students.php".($debug ? "?debug=true" : ""));

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
        <title>Tzivos Hashem | Yearly Prize Home Page</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="../css/action-links.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Tzivos Hashem Yearly Prize System</h1>
        <p class="center" style="text-align: center;">Click
            <a target="_blank" href="https://docs.google.com/document/d/1FKrfoxTUTORwW9lO3Kx0mUY9wVeCBFCn0noQzVoj4q8/edit">here</a>
            for the complete rewards manual
        </p>
        <h2>Reports</h2>
        <div id="action-links">
            <a href="eligible_students.php">
                <div class="button">
                    <img src="/images/icon_report.png" height="32" alt="tickets"/>
                    <span class="link-text">Eligiblity Report</span>
                </div>
            </a>
<!--             <a href="total_prizes.php"> -->
<!--                 <div class="button"> -->
<!--                     <img src="/images/icon_report.png" height="32" alt="medal"/> -->
<!--                     <span class="link-text">Shipping Report</span> -->
<!--                 </div> -->
<!--             </a> -->
        </div>
        
    </body>
</html>
