<?php
$debug = false; // default debugging is false
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

if ($admin_user['auth'] != 'super') {
    header("Location: /reports/");
}

/***************** IMPORTS **********************/
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Teacher Whatsapp Report</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/fancy-checkbox.css" rel="stylesheet" type="text/css"/>
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <style>
            .options a.button {display: inline-block;}
            .options {text-align: center;}
            table {width: 100%;}
            td, th{padding: 4px 8px;}
        </style>
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Teacher Whatsapp Report</h1>
        
        <h2>Report Options</h2>
        <? if(count($schools) == 1) {?>
            <select id="school_id" name="school_id" class="hidden"  disabled>
                <option value="<?=array_keys($schools)[0]?>"><?=array_values($schools)[0]?></option>
            </select>
        <? } else { ?>
            <div class="options">
                <div class="row">
                    <i class="fa fa-university" aria-hidden="true"></i> School: 
                    <select id="school_id" name="school_id">
                        <option value="" selected disabled>Please Select A School</option>
                        <? foreach($schools as $school_id => $school_name){?>
                            <option value="<?=$school_id?>"><?=$school_name?></option>
                        <?}?>
                    </select>
                </div>
            </div>
        <? } ?>
        
        <div class="options">
            <span class="option_space">
                <a class="button" id="generate"><i class="fa fa-refresh" aria-hidden="true"></i> Generate / Refresh Report</a>
            </span>
        </div>
        
        <hr style="display: block;"/>
        
        <div id="teacher_whatsapp_report"></div>
        <script>
            var debug = <?=$debug ? "true" : "false"?>;
            var admin = <?= $admin_user['auth'] == 'super' ? "true" : "false" ?>;// admin mode?
            
            $(document).ready(function() {
                $("a#generate").click(function(){
                    var school_id = $("select#school_id").val();
                    if (!school_id || school_id === "") {
                        alert("Please Select a school id"); return false;
                    }
                    
                    function update_teacher(data) {
                        $.post("ajax/update_teacher_whatsapp.php", data, function(data){
                            data = JSON.parse(data);
                            if (!data.success) {
                                alert("Could not update teacher at this time. Please try again later");
                            }
                        });
                    }
                    
                    $.post("ajax/teacher_whatsapp.php", {school_id: school_id}, function(data){
                        $("#teacher_whatsapp_report").html(data);
                        $("select.teacher_gender").change(function(event){
                            update_teacher({
                                class_id: $(event.target).parent().parent()[0].dataset.class_id,
                                gender: event.target.value
                            });
                        });
                        $("input.teacher_whatsapp").change(function(event){
                            update_teacher({
                                class_id: $(event.target).parent().parent().parent()[0].dataset.class_id,
                                whatsapp: event.target.checked ? "1" : "0" // one or 0 based on if it is checked or not.
                            });
                        });
                    });
                });
            });
        </script>
    </body>
</html>