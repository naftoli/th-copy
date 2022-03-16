<?php
ini_set('display_errors', 1);
ini_set('error_reporting', 1);

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
    'header1'       => "Children's Info",
    'user_serial'   => 'Serial Number',
    'admin_id'      => 'Family ID',
    'name'          => 'Name',
    'school_name'   => 'School Name',
    'class'         => 'Class',
    'admin_email'   => "Parent's Email",
    'header2'       => 'Tests',
    'test_type'     => 'Chosen Track',
    'highest'       => 'Highest Track Passed',
    'avg'           => 'Average of Highest Track Passed',
    'reward_type'   => 'Reward Type',
    'avgPerTest'    => 'Avg of each Part of Tests',
    'test_1'        => 'Test 1 Mark',
    'test_2'        => 'Test 2 Mark',
    'test_3'        => 'Test 3 Mark',
    'test_4'        => 'Test 4 Mark',
    'header3'       => 'The Final',
    'final_mark'    => 'Final Mark',
    'final_award'   => 'Final Award',
    'header4'       => 'Kol Hatorah Kulah',
    'khk_1'         => 'Test 1 Mark',
    'khk_2'         => 'Test 2 Mark',
    'khk_3'         => 'Test 3 Mark',
    'khk_4'         => 'Test 4 Mark',
    'khk_avg'       => 'KHK Avg',
    'khk_final'     => 'Final Mark'
];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Chidon Tests Report</title>
    <link href="/admin_styles.css" rel="stylesheet" type="text/css">
    <link href="../inc/css/report.css" rel="stylesheet" type="text/css">
    <!--    Rotating Spinner, grey dropdowns and fancy checkboxes... -->
    <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
    <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
    <!--    Nice quick icons... -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <style>
        th, td {
            padding: 10px;
            font-size: 12px;
        }
        tr {
            border-bottom: 1px solid #888;
        }
        #report { margin-top: 15px; }
        .no-report {text-align: center;}
        .no-report > .fa {font-size: 3em;}
        span.host_info_item {display: inline-block;}

        fieldset {
            font-size: 14px;
            padding: 20px;
        }

        legend {
            border-bottom: 1px solid #9B9B9B;
            width: 20%;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
<? // load the admin UI and JQuery 1.4
include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
?>
<h1>Chidon Tests Report</h1>
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
    <br/>

    <fieldset>
        <?php
        foreach ( $info as $key => $value ) {
            if (strpos($key, 'header') !== false) {
                if ($key != 'header1') echo "</fieldset><fieldset>";
                echo "<legend>$value</legend>";
            } else {
                echo "<input type='checkbox' id='$key' />$value<br />";
            }
        }
        ?>
    </fieldset>

    <fieldset>
        <legend>Limit to</legend>
        <input type="radio" name="gender" class="gender" value="M" /> Boys<br />
        <input type="radio" name="gender" class="gender" value="F" /> Girls<br />
    </fieldset>
</div>

<div class="options">
    <a class="button" id="generate_report"><i class="fa fa-refresh" aria-hidden="true"></i> Generate Report</a>
    <a class="button" id="generate_csv"><i class="fa fa-save" aria-hidden="true"></i> Export to CSV (Excel)</a>
</div>

<div id="report"></div>

<script>
    var years = [<?=$chidonYear?>];
    var ajaxData; // need to store data sent in ajax request for printed pages

    $(document).ready(function(){
        $("#generate_csv").click(generate_csv);
        $("#generate_report").click(generate_report);

        // if the school is already selected...
        if ($("select#school_id").val()) {
            generate_report();
        }

        function generate_report() {
            var school_id = $("select#school_id").val();

            if (school_id === ""){
                alert("Please select a school"); return false;
            }

            var data = [];
            // make sure we have checked options
            if ( !$("fieldset input:checked").length ) {
                alert('You must choose what to show on the report.');
                return false;
            } else {
                // get all fields to show
                let info_arr = []
                info_arr.push(<?=json_encode($info)?>);

                for (let arr of info_arr) {
                    for (let val in arr) {
                        let id = "#" + val;
                        if ($(id).is(":checked")) {
                            data.push(val)
                        }
                    }
                }
            }
            console.log(data)
            $(".gender").each( function() {
                if ($(this).is(":checked")) {
                    data.push($(this).val())
                }
            })

            //$("#qryBuilder").hide();
            $("#report").html("<div class='loader'></div>");
            ajaxData = { school_id: school_id, fields: data };
            $.post("tests_report_new.php", ajaxData, function( data ) {
                $("#report").html(data);
            });
        }

        function generate_csv() {
            const universalBOM = "\uFEFF";
            let csvContent = '';

            // add headers
            let row = [];
            $.each($("tr").eq(0).find("th"), function(index, td) {
                row.push('"' + $.trim($(td).text().replace(/\s\s+/g, ' ')) + '\t"'); // reduce extra whitespace and trim the remaining stuff...
            });
            row = row.join(",");
            csvContent += row + "\n";

            // add body
            $.each($("tr"), function(index, tr) {
                tr = $(tr); // cast to jquery;
                let row = [];
                $.each(tr.find("td"), function(index, td) {
                    row.push('"' + $.trim($(td).text().replace(/\s\s+/g, ' ')) + '\t"'); // reduce extra whitespace and trim the remaing stuff...
                });
                row = row.join(",");
                csvContent += row + "\n";
            });

            const hiddenElement = document.createElement('a');
            hiddenElement.href = "data:text/csv;charset=utf-8," + encodeURIComponent(universalBOM+csvContent); // set the data
            hiddenElement.target = '_blank'; // in a new tab
            hiddenElement.download = 'chidon-report.csv'; // with this file_name
            hiddenElement.click(); // and click it
        }
    });
</script>
</body>
</html>