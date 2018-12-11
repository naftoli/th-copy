<?php
include(dirname(__FILE__)."/../inc/header.php");

/***************** LOAD SCHOOLS **********************/
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

// get chidon year
require_once $_SERVER["DOCUMENT_ROOT"].'/class.globalSettings.php';
$chidonYear = GlobalSettings::getChidonYear();

$info = [
    'first_name'	=>	'First Name',
    'last_name'		=>	'Last Name',
    'he_first_name'	=>	'Hebrew First Name',
    'he_last_name'	=>	'Hebrew Last Name',
    'gender'		=>	'Gender',
    'dob'			=>	'Date of Birth',
    'class'			=>	'Class/Grade',
    // 'winner_type'	=>	'Contestant / School Rep.',
    // 'test1a'		=>	'Test 1 Part 1',
    // 'test1b'		=>	'Test 1 Part 2',
    // 'test2a'		=>	'Test 2 Part 1',
    // 'test2b'		=>	'Test 2 Part 2',
    // 'test3a'		=>	'Test 3 Part 1',
    // 'test3b'		=>	'Test 3 Part 2', 
    // 'history'		=>	'Previous history (years enrolled prior to 5777)',
    // 'date_paid'		=>	'Enrolled To Shabbaton',
    // 'paid'			=>	'Amount Paid',
    // 'cert_number'	=>  'Certificate Code'
];

// required fields for qry to work
$required = ['first_name', 'last_name', 'class', 'school'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Chidon Enrollment Report</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="../inc/css/report.css" rel="stylesheet" type="text/css">
<!--    Rotating Spinner, grey dropdowns and fancy checkboxes... -->
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
<!--    Nice quick icons... -->
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <style>
            th, td {
                padding: 5px;
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
        </style>
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Chidon Enrollment Report</h1>
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
                <legend>Which fields you would like to see on your report?</legend>
                <?php foreach ( $info as $key => $value ) : ?>
                    <input type="checkbox" id="<?=$key?>"
                    <?php if ( in_array( $key, $required ) ) echo " checked='checked' disabled "; ?>
                    /> <?=$value?><br />
                <?php endforeach; ?>
            </fieldset>

            <!-- <fieldset>
                <legend>Choose the years:</legend>
                <?php
                for ($i = 5777; $i <= $chidonYear; $i++) {
                    echo "<input type='checkbox' class='year' value='" . $i . "'";
                    if ( $i == $chidonYear ) echo " checked='checked'";
                    echo " /> " . $i . "<br />";
                }
                ?>
            </fieldset> -->

            <fieldset>
                <legend>Options</legend>
                <input type="checkbox" id="onlyCTH" /> Include children that are enrolled into CTH but not Chidon.<br />
                <input type="checkbox" id="unregistered" /> Include children that are not enrolled into CTH or Chidon.<br />
            </fieldset>
            <br />
        </div>

        <div class="options">
            <a class="button" id="generate_report"><i class="fa fa-refresh" aria-hidden="true"></i> Generate Report</a>
            <a class="button" id="generate_csv"><i class="fa fa-save" aria-hidden="true"></i> Export to CSV (Excel)</a>
            <a class="button" id="generate_print" style="display:none"><i class="fa fa-print" aria-hidden="true"></i> Print for Teachers</a>
        </div>
        
        <div id="report"></div>
        
        <script>
            $(document).ready(function(){
                $("#generate_csv").click(generate_csv);
                $("#generate_report").click(generate_report);
                $("#generate_print").click(generate_print);

                // if the school is already selected...
                if ($("select#school_id").val()) {
                    generate_report();
                }
                
                var years = [<?=$chidonYear?>];
                var ajaxData; // need to store data sent in ajax request for printed pages

                function generate_report() {
                    var school_id = $("select#school_id").val();
                    
                    if (school_id === ""){
                        alert("Please select a school"); return false;
                    }

                    // make sure at least one year was selected
                    // if ( !$("input.year:checked").length ) {
                    //     alert("You must choose at least one year.");
                    //     return false;
                    // } else {
                    //     var years = [];
                    //     $(".year").each( function() {
                    //         if ($(this).is(":checked")) {
                    //             years.push( $(this).val() );
                    //         }
                    //     });
                    // }

                    // make sure we have checked options
                    if ( !$("fieldset input:checked").length ) {
                        alert('You must choose what to show on the report.');
                        return false;
                    } else {
                        // get all fields to show
                        var data = [];
                        var info = <?=json_encode( $info )?>;
                        for (var val in info) {
                            var id = "#" + val;
                            if ($(id).is(":checked")) {
                                data.push( val );
                            }
                        }
                    }
                    data.push('teacher'); // show teacher name

                    var showCTH = $("#onlyCTH").is(":checked");
                    var showUnreg = $("#unregistered").is(":checked");
                    
                    //$("#qryBuilder").hide();
                    $("#report").html("<div class='loader'></div>");
                    ajaxData = { school_id: school_id, years: years, fields: data, options: [showCTH, showUnreg] };
                    $.post("ajax/snapshot.php", ajaxData, function( data ) {
                        $("#report").html(data);
                        //$("#generate_report").hide();
                        $("#generate_print").show();
                    });
                }
                
                function generate_csv() {
                    var universalBOM = "\uFEFF";
                    var csvContent = '';
                    
                    // add headers
                    var row = [];
                    $.each($("tr").eq(0).find("th"), function(index, td) {
                        row.push('"' + $.trim($(td).text().replace(/\s\s+/g, ' ')) + '\t"'); // reduce extra whitespace and trim the remaing stuff...
                    });
                    row = row.join(",");
                    csvContent += row + "\n";

                    // add body
                    $.each($("tr"), function(index, tr) {
                        tr = $(tr); // cast to jquery;
                        var row = [];
                        $.each(tr.find("td"), function(index, td) {
                            row.push('"' + $.trim($(td).text().replace(/\s\s+/g, ' ')) + '\t"'); // reduce extra whitespace and trim the remaing stuff...
                        });
                        row = row.join(",");
                        csvContent += row + "\n";
                    });
                    
                    var hiddenElement = document.createElement('a');
                    hiddenElement.href = "data:text/csv;charset=utf-8," + encodeURIComponent(universalBOM+csvContent); // set the data
                    hiddenElement.target = '_blank'; // in a new tab
                    hiddenElement.download = 'chidon-report.csv'; // with this file_name
                    hiddenElement.click(); // and click it
                }

                function generate_print() {
                    window.open('snapshot_print.php?data=' + JSON.stringify(ajaxData));
                }
            });
        </script>
    </body>
</html>