<?php
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

if ($admin_user['auth'] != 'super') {
   header("Location: /admin.php");
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Medals Inventory Report</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/fancy-checkbox.css" rel="stylesheet" type="text/css"/>
        <link href="/raffles/shared/styles/shipping/grey_slider.css" rel="stylesheet" type="text/css"/>
        <link href="../css/shipping_form.css" rel="stylesheet" type="text/css"/>
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <link href="/styles/admin/modal.css" rel="stylesheet" type="text/css"/>
        <style>
            .inline-input input[type="number"] { width: 50%; margin: 0px; background: none; border: none; border-bottom: 1px solid; }
            td { text-align: center; }
        </style>
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Medals Inventory Report</h1>

        <div class='options'>
            Medal Type:
            <select id='type'>
                <option value='number_on_back'>Number on Back</option>    
                <option value='picture_on_back'>Logo on Back</option>
            </select>
        </div>

        <div id='report'></div>

        <div class="modal" id="details-modal">
            <div class="modal-content">
                <h1>
                    <span id="modal-title">Medal Inventory Log</span>
                    <span class="close modal_exit">×</span>
                </h1>
                <div id='inventory-details'></div>
            </div>
        </div>
        <script src="index.js"></script>
    </body>
</html>