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
        <title>Tzivos Hashem | Chidon Reports | Shabbaton Walking Groups</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="../inc/css/report.css" rel="stylesheet" type="text/css">
<!--        Rotating Spinner, grey dropdowns and fancy checkboxes... -->
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
<!--        Nice quick icons... -->
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
            .options{
                margin-bottom: 10px;
            }
            @media print {
                .options, .no-print { display: none !important;}
                div#content{ width: 100% !important; }
/*                #report {
                    width: 80%;
                    margin: 0 auto;
                }*/
                h2 {
                    margin-top: 70px;
                }
                input[type="text"] {
                    border-bottom: none;
                }
            }
            
            .page-break {
                page-break-after: always;
            }
            
            input[type="text"]:disabled {
                border-bottom: none;
            }
            
            input[type="number"]{
                border: none; background: none;
                border-bottom: 1px solid;
                padding: 5px;
            }
            
            div#wrapper {
                width: 1300px;
            }
            div#content, #content .slider {
                width: 1049px;
            }
/*            input.td-text {
                max-width: 70px;
            }*/
        </style>
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1 class="no-print">Shabbaton Walking Report</h1>
        <? if(count($schools) == 1) {?>
            <select id="school_id" name="school_id" class="hidden" disabled>
                <option value="<?=array_keys($schools)[0]?>"><?=array_values($schools)[0]?></option>
            </select>
        <?} else {?>
            <div class="options">
                <div class="row">
                    <i class="fa fa-university" aria-hidden="true"></i> Limit To School: 
                    <select id="school_id" name="school_id">
                        <option value="" selected>All Schools</option>
                        <? foreach($schools as $school_id => $school_name){?>
                            <option value="<?=$school_id?>"><?=$school_name?></option>
                        <?}?>
                    </select>
                </div>
            </div>
        <?}?>
        <div class="options">
             <div class="row">
                <i class="fa fa-university" aria-hidden="true"></i> Walking Zone 
                <input type="number" id="walking_zone_start" min="1" max="100" value="1"/>
                To
                <input type="number" id="walking_zone_end" min="1" max="100" value="100"/>
                For the 
                <select id="gender">
                    <option value="F">Girls Chidon</option>
                    <option value="M">Boys Chidon</option>
                </select>
            </div>
        </div>
        <br/>
        <div class="options">
            <a class="button" id="generate_report"><i class="fa fa-refresh" aria-hidden="true"></i> Generate Report</a>
<!--            <a class="button" id="generate_csv"><i class="fa fa-save" aria-hidden="true"></i> Export to CSV (Excel/Sheets)</a>-->
        </div>
        
        <div id="report"></div>
        
        <script>
            $(document).ready(function(){
                //$("#generate_csv").click(generate_csv);
                $("#generate_report").click(generate_report);
                // if the school is already selected...
                if ($("select#school_id").val()) {
                    generate_report();
                }
                
                function generate_report() {
                    $("#report").html("<div class='loader'></div>");
                    var school_id = $("select#school_id").val();
                    
                    var data = {
                        school_id:  school_id,
                        gender:     $("select#gender").val(),
                        start:      $("input#walking_zone_start").val(),
                        end:        $("input#walking_zone_end").val()
                    };
                    
                    $.post("ajax/walking_groups.php", data, function(data){
                        $("#report").html(data);
                        $(".move").click(moveZone);
                        $(".save").click(updateHost);
                    });
                }
                
                function moveZone( event ) {
                    var walking_zone = prompt("Please enter the walking zone which you would like to move to.");
                    // make sure that they did not press cancel or ok with a blank option...
                    if (!walking_zone) return false;
                    
                    var data = Object.assign({walking_group: walking_zone}, event.target.dataset); // add the data from the dataset to the equation...
                    console.log(data);
                    
                    $.post("ajax/move_walking_group.php", data, function( response) {
                        response = JSON.parse( response );
                        if (!response.success) {
                            alert( response.error );
                        } else {
                            generate_report();
                        }
                    });
                }
                
                function updateHost( event ) {
                    $(event.target).text("Saving...");
                    
                    var data = {
                        th_chidon_id : event.target.dataset.id
                    };
                    
                    $.each( $(this).parent().parent().find("input.td-text"), function( index, item ) {
                        data[item.name] = item.value;
                    } );
                    
                    $.post( "ajax/update_user_host.php", data, function( response ) {
                        response = JSON.parse( response );
                        if (!response.success) {
                            $(event.target).text("Save");
                            alert(response.error);
                        } else {
                            $(event.target).text("Saved!");
                        }
                    });
                };
                
                //function generate_csv() {
                //    var rows = []; // the rows for the csv export
                //    var csvContent = "Grade,Name,Hebrew Name,Host Name,Host Address,Host Cross Streets,Host Phone,Father Cell,Mother Cell,Allergies,Walk (day),Walk (night)\n"; //"Serial,First,Last,Grade,# Created\n"; // the baisc csv file
                //    var universalBOM = "\uFEFF";
                //    // TODO add headers
                //    $.each($("tr"), function(index, tr) {
                //        tr = $(tr); // cast to jquery;
                //        var row = [];
                //        $.each(tr.find("td"), function(index, td) {
                //            if ($(td).hasClass( "host_info" )) {
                //                $.each($(td).find("span.host_info_item"), function( index, info_item ){
                //                     row.push('"' + $.trim($(info_item).text().replace(/\s\s+/g, ' ')) + '\t"'); // reduce extra whitespace and trim the remaing stuff...
                //                });
                //            } else {
                //                row.push('"' + $.trim($(td).text().replace(/\s\s+/g, ' ')) + '\t"'); // reduce extra whitespace and trim the remaing stuff...
                //            }
                //        });
                //        rows.push(row); // add the row to the csv export
                //        row = row.join(",");
                //        csvContent += row + "\n";
                //    });
                //    
                //    var hiddenElement = document.createElement('a');
                //    hiddenElement.href = "data:text/csv;charset=utf-8," + encodeURIComponent(universalBOM+csvContent); // set the data
                //    hiddenElement.target = '_blank'; // in a new tab
                //    hiddenElement.download = 'shabbaton-enrollment-report.csv'; // with this file_name
                //    hiddenElement.click(); // and click it
                //}
            });
        </script>
    </body>
</html>