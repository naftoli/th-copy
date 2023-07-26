<?php
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Rank Promotion Report | Tzivos Hashem</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
        <style>
            input[type="date"] {
                border: none; background: none;
                border-bottom: 1px solid #000; font-size: 18px;
            }
            hr {display: block;}
            .name.general { font-weight: bold; }
            span.school.general { text-decoration: underline }
            span.rank { color: red; margin-bottom: 4px; display: inline-block;}
            span.school {font-style: italic; margin-bottom: 4px; display: inline-block;}
            span.school.top { margin-top: 4px; margin-bottom: 0px;}
            a.btn.button { display: inline-block; margin-bottom: 15px; }
            img.profile { height: 35px; width: 35px; float: left; margin-right: 10px; }
            .clearfix { clear: both; margin-bottom: 3px; }
            div#totals, div#breakdown { background: #fff; padding: 15px; }
            a.name {color: #000;font-weight: normal;}
        </style>
    </head>
    <body>
        <?php include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
        <h1>Rank Promotion Report</h1>
        <form id="options">
            <label for="from">From</label>
            <input type="date" placehoder="YYYY-MM-DD" id="from" name="from" required />

            <label for="from">To</label>
            <input type="date" placehoder="YYYY-MM-DD" id="to" name="to" required />

            <input type="submit" />
        </form>
        <hr>
        <div id="report"></div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.0/clipboard.min.js"></script>
        <script src="js/jszip.min.js"></script>
        <script src="js/rank_report.js?v=3.2"></script>
    </body>
</html>