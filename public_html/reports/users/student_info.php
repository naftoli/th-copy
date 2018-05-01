<?php $debug = false; // default debugging is false
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once(dirname(__FILE__).'/../../header.php');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Tzivos Hashem | Student Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link href="/admin_styles.css" rel="stylesheet" type="text/css"/>
    <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
    <link href="/mobile/reg/css/medal-board/medals.css" rel="stylesheet" type="text/css"/>
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <style>
    table {
        width: 100%;
        margin-top: 4px;
    }
    th, td {
        border: 1px solid #888;
        padding: 4px 8px;
    }
    .options {
        text-align: center;
    }
    input#serial_number {
        margin-bottom: 0px;background: none;
        border: none;border-bottom: 1px solid;
        padding: 2px;font-size: 1.2em;width: 12em;
    }
    /* user info */
    div.photo {
        display: inline-block; float: right; position: relative;
    }
    img.rank {
        height: 90px;position: absolute;bottom: -30px;right: -25px;
    }
    img.profile_picture {
        max-height: 175px;max-width: 200px;border-radius: 15px;border: 2px solid;
    }
    /* page info */
    .info {
        display: inline-block;width: 48%;padding: 5px;
        vertical-align: top;border-top: 1px solid #888;
    }
    .inner-info {
        width: 49%; display: inline-block;  box-sizing: border-box; padding: 0px 5px 5px;
        word-wrap: break-word;
    }
    .info-3rd {width: 31.5%;}
    .info-quarter {width: 23%;}
    .primary_info .info {width: 34%;}
    .primary_info h3 {font-size: 1.2em;margin-bottom: 5px;display: inline-block;}
    /* prizes section */
    .prize { box-sizing: border-box; padding: 5px; }
    .prize img { max-height: 50px; }
    .prize span {
        display: inline-block; vertical-align: top; margin-top: 15px; max-width: 80%; margin-left: 2.5%;
    }
    /* Chidon section */
    .centered { text-align: center; }
    /* medal board */
    #medal-board {text-align: center;}
    .medal-status.progress {
        height: 25px;width: 100%;text-indent: 0px;background: #fff;
        display: inline-block;padding: 0px;margin: 0px;float: none;
    }
    .medal-board .medal-subject {height: auto;line-height: 25px;margin-top: -20px;}
    .medal-board .medal-subject span {font-size: .4em;}
    .medal-status span {font-size: 12px;top: 2px;}
    .medal-status.progress {height: 16px;}
    .progress-bar { border-radius: 10px;}
    /* rank board */
    .rank-board > div { 
        position: relative; display: flex; align-items: center; padding: 5px 0px; border-bottom: 1px solid; 
    }
    .rank-logo {
        display: inline-block;text-align: center;width: 30%;
    }
    .rank-logo img {width: 75px;}
    .rank_promoted, span.rank-medal-number { font-size: .6em; display: block; }
    .rank_promoted { margin-top: 4px; }
    .rank_name { font-size: .8em; }
    .rank-medals {display: inline-block;vertical-align: top;width: 70%;}
    .rank-medal {display: inline-block;text-align: center;padding: 2px;}
    .rank-medal img {width: 44px;}
    /* changes when printing this report */
    @media print {
        .medal-board .medal {
            width: 20.3%;
            padding-bottom: 5px;
            padding-top: 5px;
        }
        .medal-status.progress {
            margin-top: 5px;
            border: 1px solid;
        }
        img.rank{ right: -15px; }
    }
    </style>
</head>
<body>
    <?php // load the admin UI and JQuery 1.4
        include(dirname(__FILE__).'/../../admin_header.php');
    ?>
    <h1 class="noprint">Student Report</h1>
    <div class="options noprint">
        <label for="serial_number">Enter Serial Number or Barcode</label>
        <input type="text" id="serial_number" />
        <a class="button" id="generate">Submit</a>
    </div>

    <hr style="display: block;" class="noprint">
    <div id="report"></div>

    <script src="/mobile/reg/js/medal-board.js"></script>
    <script src="/mobile/reg/js/rank-board.js"></script>
    <script>
    $( "a#generate" ).click( generate_report );
    $( "input#serial_number" ).keydown( function( event ) {
        if ( event.keyCode === 13 || event.keyCode === 9 ) {
            generate_report();
        }
    })

    function generate_report() {
        var serial_number = $( "input#serial_number" ).val();
        var postData = {};
        // determine if the input is valid
        if ( serial_number.match(/7{2}\d{4,5}/) ) { // all serial numbers start with 2 7's and are 6-7 digits long
            postData.serial_number = serial_number;
        } else if ( serial_number.match(/3{1}\d{19}/) ) {
            postData.barcode = serial_number;
        } else {
            $( "div#report" ).html( "Please enter a valid serial number or digit barcode." );
            return false;
        }
        $( "div#report" ).html( "<div class='loader'></div>" );

        $.post( "ajax/student_info.php", postData, function( report ) {
            $( "div#report" ).html( report );
            medal_board("#medal-board", $("#user_id").val(), false);
            rank_board("#rank-board", $("#user_id").val(), false )
        });
    }
    </script>
</body>
</html>