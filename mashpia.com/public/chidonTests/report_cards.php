<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

/***************** LOAD SCHOOLS **********************/
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

// get chidon year
require_once $_SERVER["DOCUMENT_ROOT"].'/class.globalSettings.php';
$chidonYear = GlobalSettings::getChidonYear();

$info = [
    'test_1'        =>  'Test 1 Mark',
    'test_2'        =>  'Test 2 Mark',
    'test_3'        =>  'Test 3 Mark',
    'test_4'        =>  'Test 4 Mark',
    'avg'           =>  'Avg Mark (for chosen tests)'
];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Chidon Report Cards</title>
    <link href="/admin_styles.css" rel="stylesheet" type="text/css">
    <link href="../inc/css/report.css" rel="stylesheet" type="text/css">
    <!--    Rotating Spinner, grey dropdowns and fancy checkboxes... -->
    <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
    <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
    <!--    Nice quick icons... -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">

</head>
<body>
<? // load the admin UI and JQuery 1.4
include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
?>
<h1>Chidon Report Cards</h1>
<div id="qryBuilder">
    <? if(count($schools) == 1) {?>
        <select id="school_id" name="school_id" class="hidden" disabled>
            <option value="<?=array_keys($schools)[0]?>"><?=array_values($schools)[0]?></option>
        </select>
    <?} else {?>
        <div class="options">
            <div class="row">
                <i class="fa fa-university" aria-hidden="true"></i> School:
                <select id="school_id" name="school_id">
                    <option value="" disabled selected>Select School</option>
                    <option value="-1">All Schools</option>
                    <? foreach($schools as $school_id => $school_name){?>
                        <option value="<?=$school_id?>"><?=$school_name?></option>
                    <?}?>
                </select>
            </div>
        </div>
    <?}?>
    <div class="options">
        <div class="row">
            <i class="fa fa-university" aria-hidden="true"></i> Grade:
            <select id="class_id" name="class_id">
                <option value="" disabled selected>Select Grade</option>
                <option value="-1">All Grades</option>
            </select>
        </div>
    </div>
    <div class="options">
        <div class="row">
            <i class="fa fa-university" aria-hidden="true"></i> Student:
            <select id="user_id" name="user_id">
                <option value="" disabled selected>Select Student</option>
                <option value="-1">All Students</option>
            </select>
        </div>
    </div>
    <div class="options">
        <div class="row">
            <i class="fa fa-university" aria-hidden="true"></i> After Test #:
            <select id="test_num" name="test_num">
                <?php
                for ($i = 1; $i <= 4; $i++) {
                    echo "<option value=" . $i . ">" . $i . "</option>";
                }
                ?>
            </select>
        </div>
    </div>
</div>
<br />
<div class="options">
    <a class="button" id="generate_report"><i class="fa fa-refresh" aria-hidden="true"></i> Generate Report</a>
</div>

<div id="report"></div>

<script>
    var years = [<?=$chidonYear?>];
    var ajaxData; // need to store data sent in ajax request for printed pages

    $(document).ready(function(){
        $("#generate_csv").click(generate_csv);
        $("#generate_report").click(generate_report);

        function generate_report() {
            var school_id = $("select#school_id").val();

            if (school_id === ""){
                alert("Please select a school"); return false;
            }

            var data = [];

            //$("#qryBuilder").hide();
            $("#report").html("<div class='loader'></div>");
            ajaxData = { school_id: school_id, fields: data };
            $.post("tests_report.php", ajaxData, function( data ) {
                $("#report").html(data);
            });
        }
    });
</script>
</body>
</html>