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
        th, td { padding: 4px 8px; font-size: 14px; max-width: 130px; }
        td.saved { padding-right: 0px; }
        td:last-child { padding: 4px 0px; }
        input[type="date"] { width: 130px; background: none; border: none; border-bottom: 1px solid; }
        input[type="date"]:disabled { color: #888; }
        input[type="number"] { width: 80px; border: none; border-bottom: 1px solid; background: none; }
        .info { padding: 10px; background: #ccc; margin-bottom: 15px; }
        .info ol, .info ul { list-style: decimal; margin-left: 15px; padding: 5px; }
        .info ul { list-style: disc }
        button.button { transition: .25s; }
        button.button:focus, button.button:hover, select:focus { transform: scale( 1.1 ) }
    </style>
</head>
<body>
    <? // load the admin UI and JQuery 1.4
        include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
    ?>
    <h1>School Registration Settings</h1>

    <div class="info">
        <h3>School Registration Settings for <select id="year" value="<?=$year?>">
            <option value="<?=$year?>"><?=$year?></option>
            <option value="<?=$year + 1?>"><?=$year + 1?></option>
        </select></h3><br/>
        <h4>Settings Explained:</h4><br/>
        <strong>Type:</strong>
        <ol>
            <li><strong>In Tuition:</strong> The base charges parents for registration in Tuition.</li>
            <li><strong>Guaranteed:</strong> 
                Parents are given an additional $<?=GlobalSettings::getGuarenteedDiscount()?> discount as base guarantees all parents will register. 
                If some parents do not register the base is charged for the discount given to all soldiers who registered.</li>
            <li><strong>By Parent:</strong> Parents pay for registration as they wish.</li>
        </ol>
        <p></p>
        <p><strong>Fee:</strong> The fee the base will pay to register.</p>
        <p><strong>Balance:</strong> The balance the base owes to Tzivos Hashem.</p>
        <strong>Soldier Fee:</strong> The registration fee for soldiers in this base.
        <ul>
            <li>Please note that the early bird discount ($<?=GlobalSettings::getEarlyBird()?>) is applied to this amount.</li>
            <li>For example, $60 soldier fee - $5 early bird is $55 for registration.</li>
            <li><em>Set to / leave as 0 for default rates.</em></li>
        </ul>
        <p></p>
        <p><strong>Early Bird:</strong> The date on which the early bird ends for the base.<br/>
            For "guaranteed" bases this is also the deadline to have all children register.
        </p>
    </div>
    <div id="report">
        <div class="loader"></div>
    </div>
    <script src="js/registration_info.js"></script>
</body>
</html>