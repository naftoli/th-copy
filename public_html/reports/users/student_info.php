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
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <style>
    .options {
        text-align: center;
    }
    input#serial_number {
        margin-bottom: 0px;
        background: none;
        border: none;
        border-bottom: 1px solid;
        padding: 2px;
        font-size: 1.2em;
    }
    div.photo {
        display: inline-block; float: right; position: relative;
    }
    img.rank {
        height: 90px;
        position: absolute;
        bottom: -30px;
        right: -25px;
    }
    img.profile_picture {
        height: 175px;
        max-width: 200px;
        border-radius: 15px;
        border: 2px solid;
    }
    .info {
        display: inline-block;
        width: 48%;
        vertical-align: top;
        border-top: 1px solid #888;
        padding: 5px;
    }
    .info-3rd {
        width: 31.5%;
    }
    .primary_info .info {
        width: 39%;
    }
    .primary_info h3 {
        font-size: 1.2em;
        margin-bottom: 5px;
        display: inline-block;
    }
    </style>
</head>
<body>
    <?php // load the admin UI and JQuery 1.4
        include(dirname(__FILE__).'/../../admin_header.php');
    ?>
    <h1>Student Report</h1>
    <div class="options">
        <label for="serial_number">Enter Serial Number or Barcode</label>
        <input type="text" id="serial_number" />
        <a class="button" id="generate">Submit</a>
    </div>

    <hr style="display: block;">
    <div id="report"></div>

    <script>
    $( "a#generate" ).click( generate_report );

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
        });
    }
    </script>
</body>
</html>