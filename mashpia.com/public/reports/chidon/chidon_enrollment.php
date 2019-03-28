<?php
include(dirname(__FILE__)."/../inc/header.php");

/***************** LOAD SCHOOLS **********************/
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
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
        </style>
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Chidon Enrollment Report</h1>
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
        <div class="options">
            <a class="button" id="generate_report"><i class="fa fa-refresh" aria-hidden="true"></i> Generate Report</a>
            <a class="button" id="generate_csv"><i class="fa fa-save" aria-hidden="true"></i> Export to CSV (Excel)</a>
        </div>
        
        <div id="report"></div>
        
        <script>
            $(document).ready(function(){
                $("#generate_csv").click(generate_csv);
                $("#generate_report").click(generate_report);
                // if the school is already selected...
                if ($("select#school_id").val()) {
                    generate_report();
                }
                
                function generate_report() {
                    $("#report").html("<div class='loader'></div>");
                    var school_id = $("select#school_id").val();
                    
                    if (school_id === ""){
                        alert("Please select a school"); return false;
                    }
                    
                    $.post("ajax/chidon_enrollment.php", {school_id: school_id}, function(data){
                        $("#report").html(data);
                    });
                }
                
                function generate_csv() {
                    var rows = []; // the rows for the csv export
                    var csvContent = "Grade,First Name,Last Name,Hebrew First Name,Hebrew Last Name,Father Cell,Mother Cell\n"; //"Serial,First,Last,Grade,# Created\n"; // the baisc csv file
                    var universalBOM = "\uFEFF";
                    // TODO add headers
                    $.each($("tr"), function(index, tr) {
                        tr = $(tr); // cast to jquery;
                        var row = [];
                        $.each(tr.find("td"), function(index, td) {
                            row.push('"' + $.trim($(td).text().replace(/\s\s+/g, ' ')) + '\t"'); // reduce extra whitespace and trim the remaing stuff...
                        });
                        rows.push(row); // add the row to the csv export
                        row = row.join(",");
                        csvContent += row + "\n";
                    });
                    
                    var hiddenElement = document.createElement('a');
                    hiddenElement.href = "data:text/csv;charset=utf-8," + encodeURIComponent(universalBOM+csvContent); // set the data
                    hiddenElement.target = '_blank'; // in a new tab
                    hiddenElement.download = 'shabbaton-enrollment-report.csv'; // with this file_name
                    hiddenElement.click(); // and click it
                }
            });
        </script>
    </body>
</html>