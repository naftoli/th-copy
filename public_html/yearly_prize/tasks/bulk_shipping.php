<?
/***************** DEBUGGING SETTINGS **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
    echo "<h2>Debug log:</h2>";
}
if($debug) echo "<pre>";

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
// only superusers can use this page. Non superusers get redirected to the page that they can use
if ($admin_user['auth'] != 'super') {
    header("Location: /admin.php");
}

if($debug) echo "</pre>";
// render the page
?><!DOCTYPE html">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem Tasks | Yearly Prize: Bulk Mark Shipping Task</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="../css/grey_select.css" rel="stylesheet" type="text/css">
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <? include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); // load the basic UI ?>
        <h1>Yearly Prize: Bulk Mark Shipping Task</h1>
        <h2>Please select the start and end registration dates to mark all students between given dates for yearly prize shipping</h2>
        
        <div id="options">
            <label for="start_date">
                <i class="fa fa-calendar" aria-hidden="true"></i> Registered from
            </label>
            <input type="date" name="start_date" id="start_date" placeholder="mm/dd/yyyy" value="2017-07-01" />
            <label for="end_date">untill</label>
            <input type="date" name="end_date" id="end_date" placeholder="mm/dd/yyyy" value="2017-11-10"/>
            <a class="button" id="run">Run</a>
        </div>
        
        <div id="results"></div>
        <script>
            var debug = <?=$debug ? "true" : "false"?>;
            $("#run").click(function(){
                $("#results").html("<div class='loader'></div>");
                var data = {start_date: $("#start_date").val(), end_date: $("#end_date").val()};
                
                $.post("../ajax/tasks/bulk_shipping.php" + (debug ? "?debug=true": ""), data, function(data){
                    $("#results").html(data);
                });
            });
        </script>
        
    </body>
</html>