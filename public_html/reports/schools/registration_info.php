<?php

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

if ($admin_user['auth'] != 'super') {
   header("Location: /admin.php");
}

require_once( $_SERVER['DOCUMENT_ROOT'] .'/class.globalSettings.php' );
$year = GlobalSettings::getRegistrationYear();
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>School Registration Settings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/admin_styles.css" rel="stylesheet" type="text/css">
    <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
    <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <style>
        div#wrapper { width: 1101px; }
        #content .col_content { padding: 20px 10px; }
        #content .slider, #content { width: 850px; }
        th, td { padding: 4px 8px; font-size: 14px; max-width: 120px; }
        td.saved { padding-right: 0px; }
        td:last-child { padding: 4px 0px; }
        input[type="date"] { width: 128px; background: none; border: none; border-bottom: 1px solid; }
        input[type="date"]:disabled { color: #888; }
        input[type="number"] { width: 80px; border: none; border-bottom: 1px solid; background: none; }
        .info { padding: 10px; background: #ccc; margin-bottom: 15px; }
        ol { list-style: decimal; margin-left: 15px; padding: 5px; }
        button.button { transition: .25s; }
        button.button:focus, button.button:hover, select:focus { transform: scale( 1.1 ) }
    </style>
</head>
<body>
    <? // load the admin UI and JQuery 1.4
        include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
    ?>
    <h1>School Registration Settings</h1>
    <input type="hidden" id="year" value="<?=$year?>" />

    <div class="info">
        <h3>School Registration Settings for <?=$year?></h3><br/>
        <h4>Settings Explained:</h4><br/>
        <strong>Type:</strong>
        <ol>
            <li><strong>In Tuition:</strong> Parents do not pay for registration. The base is billed directly.</li>
            <li><strong>Guaranteed:</strong> Parents pay for registration. However, the base guarantees that they will do so by the <strong>Deadline</strong> and will be billed for the rest.</li>
            <li><strong>By Parent:</strong> Parents do pay for registration. The base is never billed.</li>
        </ol>
        <p><strong>Fee:</strong> This is the fee the base will pay to register.</p>
        <p><strong>Balance:</strong> This is the balance owed to Tzivos Hashem from previous years.</p>
        <p><strong>Deadline:</strong> For <em>Guaranteed</em> bases, this is the date on which we bill them for the remaining children.</p>
        <p><strong>Early Bird:</strong> This is when the early bird discount ends for this school.</p>
    </div>
    <div id="report">
        <div class="loader"></div>
    </div>
    <script src="js/registration_info.js"></script>
</body>
</html>