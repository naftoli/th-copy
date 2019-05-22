<?php // enable debugging
error_reporting(E_ALL);
ini_set("display_errors", 1);

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once( dirname(__FILE__).'/../../header.php' );

if ( $admin_user['auth'] != 'super' ) {
    header( 'Location: /raffles/' ); die();
}
/***************** EXTERNAL DEPENDENCIES **********************/
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';

// load the schools
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Tzivos Hashem | Yearly Raffle Eligibility</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/admin_styles.css" rel="stylesheet" type="text/css">
    <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css">
    <link href="/styles/admin/grey-select.css" rel="stylesheet" type="text/css">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <style>
        .options { text-align: center; padding: 5px;}
        .option-title { font-weight: bold; }
        a.button { display: inline-block; }
        td, th { padding: 4px 8px; }
    </style>
</head>
<body>
    <?php include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
    <h1>Yearly Raffle: All Eligible Students</h1>
    <p class="info">
        This report will generate a list and total of all eligible students in the current yearly raffle in the same way that the raffle script would.<br/><br/>
        Please note that there are <strong>two types of reports:</strong>
    </p>
    <p class="info">
        <strong>RealTime:</strong> This report will calculate and cache all users in all schools with 180+ days of missions. This report can take several minutes to compleate and your browser may loose connection to the server before it is done.
    </p>
    <p class="info">
        <strong>Cached:</strong> This report will display the cached information from the last time the system generated a realtime report.
            This report is updated if you generate a RealTime report on this page and very night in the background.
            This report also updates every time a <a href="eligibility_report.php">full school eligibility report</a> is generated.
    </p>
    <div class="options">
        <span class="option-title">Report Type: </span>
        <input type="radio" id="report_type" name="report_type" value="cached" checked/> 
        Cached ( faster )
        <input type="radio" id="report_type" name="report_type" value="realtime"/> 
        RealTime <?= $admin_user['auth'] !== "super" ? "( get a coffee and take a nap slow )" : "(slow)" ?>
    </div>
    <div class="options">
        <input type="hidden" id="school_id" value="<?= $admin_user['auth'] !== "super" ? array_keys($schools)[0] : "" ?>" />
        <a class="button" id="generate"><i class="fa fa-refresh" aria-hidden="true"></i> Generate/Refresh Report</a>
    </div>

    <div id="eligible_table"></div>

    <script>
        var generating_report = false;

        $("#generate").click( function() {
            if ( !generating_report ){
                generating_report = true;
                $("#eligible_table").html("<div class='loader'></div>");
                var postData = {
                    report_type: $("input#report_type:checked").val(),
                    school_id: $("input#school_id").val()
                };
                $.post("ajax/eligibility_report_hq.php", postData, function( response ){
                    $("#eligible_table").html( response );
                });
                generating_report = false;
            }
        });
    </script>
</body>
</html>