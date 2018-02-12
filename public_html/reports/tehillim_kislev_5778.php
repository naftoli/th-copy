<?php
/***************** DEBUGGING **********************/
// enable debuging
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
    echo "<pre>";
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
// only superusers can use this page
if ($admin_user['auth'] != 'super') {
    echo "Sorry you don't have the privilege(s) necessary to view this page.";
    exit;
}

/***************** IMPORTS **********************/
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/class.schoolsUsers.php';

/***************** LOAD USERS **********************/
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
$schoolsUsers = array();
// for each school get its users
foreach ( $schools as $id => $school ) {
    $s = new SchoolsUsers( $id );
    $schoolsUsers[$id] = $s->getUsers(true, true);
}

if($debug) echo "</pre>";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Kislev 5778 Tehillim Migration Report</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/fancy-checkbox.css" rel="stylesheet" type="text/css"/>
        <style>
            .options{text-align: center;} .options a{display: inline-block;}
            td {text-align: center;}table {width: 100%;}
        </style>
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    </head>
    <body>
        <? include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
        
        <h1>Kislev 5778 Tehillim Migration Report</h1>
        
        <h2>Please select one of the following options and generate the report</h2>
        <div class="options">
            <? if(count($schools) == 1) {?>
                <select id="school_id" name="school_id" class="hidden"  disabled>
                    <option value="<?=array_keys($schools)[0]?>"><?=array_values($schools)[0]?></option>
                </select>
            <?} else {?>
                <div class="row">
                    <i class="fa fa-university" aria-hidden="true"></i> School: 
                    <select id="school_id" name="school_id">
                        <option value="">All Schools</option>
                        <? foreach($schools as $school_id => $school_name){?>
                            <option value="<?=$school_id?>"><?=$school_name?></option>
                        <?}?>
                    </select>
                </div>
            <?}?>
            <a class="button" id="generate"><i class="fa fa-refresh" aria-hidden="true"></i> Generate/Refresh Report</a>
        </div>
        <div id="report"></div>
        <script>
        var debug = <?= $debug ? "true" : "false" ?>;
        $(document).ready(function(){
           $("#generate").click(fetch_report);
        });
        
        function fetch_report() {
            var school_id = $("select#school_id").val();
            var data = {};
            if (school_id) {data.school_id = school_id;} // add the school_id to the post request if it is set
            $("#report").html("<div class='loader'></div>");
            $.post("ajax/tehillim_kislev_5778.php" + (debug ? "?debug=true" : "" ), data, function(succes){
                $("#report").html(succes);
            });
        }
        </script>
    </body>
</html>