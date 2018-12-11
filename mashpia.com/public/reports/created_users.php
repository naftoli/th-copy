<?php $debug = true;
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

/***************** IMPORTS **********************/
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';

/***************** LOAD SCHOOLS **********************/
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

if($debug) echo "</pre>";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Student - Parent Email Report</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
        <link href="shipping/css/shipping_form.css" rel="stylesheet" type="text/css"/>
        <style>
            table {width: 100%;}td{padding: 4px 8px;}
            input[type='number']{border: none; background: none;border-bottom: 1px solid}
        </style>
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    </head>
    <body>
        <? include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
        
        <h1>Created Students Report</h1>
        <? /******************* SHOW THE SCHOOLS DROPDOWN *********************/ ?>
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
                        <? foreach($schools as $school_id => $school_name){?>
                            <option value="<?=$school_id?>"><?=$school_name?></option>
                        <?}?>
                    </select>
                </div>
            </div>
        <?}?>
        
        <div class="options bordered" id="filters">
            <span class="option_space">
                <i class="fa fa-filter" aria-hidden="true"></i> Limit:
                <input type="number" id="limit" />
                
                <i class="fa fa-filter" aria-hidden="true"></i> Offset:
                <input type="number" id="offset" />
            </span>
        </div>
        
        <div class="options">
            <a class="button" id="generate_report"><i class="fa fa-refresh" aria-hidden="true"></i> Generate Report</a>
            <a class="button" id="generate_csv"><i class="fa fa-save" aria-hidden="true"></i> Export to CSV</a>
        </div>
        
        <div id="report"></div>
        
        <script>
        var debug = <?= $debug ? "true" : "false" ?>;
        $(document).ready(function(){
           $("#generate_csv").click(generate_csv);
           $("#generate_report").click(generate_report);
        });
        
        function generate_report() {
            $("#report").html("<div class='loader'></div>");
            var school_id = $("select#school_id").val();
            
            if (school_id === ""){
                alert("Please select a school"); return false;
            }
            
            var limit = $("input#limit").val();
            var offset = $("input#offset").val();
            
            $.post("ajax/created_users_report.php", {school_id: school_id, limit: limit, offset: offset, debug: debug}, function(data){
                $("#report").html(data);
            });
        }
        
        function generate_csv() {
            var rows = []; // the rows for the csv export
            var csvContent = ""; //"Serial,First,Last,Grade,# Created\n"; // the baisc csv file
            var universalBOM = "\uFEFF";
            // TODO add headers
            $.each($("tr"), function(index, item) {
                item = $(item); // cast to jquery;
                var row = [];
                $.each(item.find("th, td"), function(index, item) {
                    row.push('"' + $(item).text() + '\t"');
                });
                rows.push(row); // add the row to the csv export
                row = row.join(",");
                csvContent += row + "\n";
            });
            
            var hiddenElement = document.createElement('a');
            hiddenElement.href = "data:text/csv;charset=utf-8," + encodeURIComponent(universalBOM+csvContent); // set the data
            hiddenElement.target = '_blank'; // in a new tab
            hiddenElement.download = 'created-students-report.csv'; // with this file_name
            hiddenElement.click(); // and click it
        }
        </script>
    </body>
</html>