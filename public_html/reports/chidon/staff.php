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
        <title>Tzivos Hashem | Chidon Reports | Attendace Staff</title>
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
            table input[type="text"]{
                width: 110px;
            }
            input[type="password"] {
                background: none;
                border: none;
                border-bottom: 1px solid;
            }
            input[type="submit"]{
                margin: 10px auto;
                display: block;
                padding: 5px 10px;
            }
            #new-staff label {
                width: 48%;
                display: inline-block;
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Shabbaton Attendance Staff</h1>
        
        <h2>Create Attendance Staff</h2>
        <strong>Please note that usernames need to be <em>unique</em>.</strong>
        
        <form id="new-staff">
            <label for="name">Name*:<input type="text" name="name" required/></label>
            
            <label for="cell">Cell*:<input type="text" name="cell" required/></label>
            
            <label for="username">Username*: <input type="text" name="username" required/></label>
            
            <label for="password">Password*: <input type="text" name="password" required/></label>
            
            <label for="walking_zone">Walking Zone: <input type="text" name="walking_zone"/></label>
            
            <!--<label for="door_number">Door Number: 
                <select name="door_number"/>
                    <option value="">No Door</option>
                    <optgroup label="Grade 4-5">
                        <option value="1">Door 1</option>
                        <option value="2">Door 2</option>
                        <option value="3">Door 3</option>
                    </optgroup>
                    <optgroup label="Grade 6-8">
                        <option value="4" <?=$user['door_number'] == "4" ? "selected" : ""?>>Door 1</option>
                        <option value="5" <?=$user['door_number'] == "5" ? "selected" : ""?>>Door 2</option>
                        <option value="6" <?=$user['door_number'] == "6" ? "selected" : ""?>>Door 3</option>
                    </optgroup>
                </select>
            </label>
            
            <label for="bus_code">Bus Code: <input type="text" name="bus_code"/> </label>-->
            
            <label for="chidon_type">Chidon Type: 
                <select name="chidon_type"/>
                    <option value="boys">Boys</option>
                    <option value="girls">Girls</option>
                </select>
            </label>
            <br/>
            <input type="submit"/>
        </form>
        
        <h2>Edit Attendance Staff</h2>
        <strong>Please note that staff will update <em>in real time</em> as you type into the table below</strong>
        
        <br/><br/>
        <div class="options">
            Sort By:
            <select id="sort_by">
                <option value="name">Name</option>
                <option value="walking_zone">Walking Zone</option>
            </select>
            Limit To:
            <select id="chidon_type_limit">
                <option value="">None</option>
                <option value="boys">Boys</option>
                <option value="girls">Girls</option>
            </select>
            <br/>
            <a class="button" id="generate_report"><i class="fa fa-refresh" aria-hidden="true"></i> Generate Report</a>
        </div>
        
        <div id="report"></div>
        
        <script>
            $(document).ready(function(){
                $("#generate_report").click(generate_report);
                $("#new-staff").submit(createStaff);
                generate_report(); // get the report on page load...
                // if the school is already selected...
                
                function generate_report() {
                    $("#report").html("<div class='loader'></div>");
                    
                    var data = {
                        sort_by: $("select#sort_by").val(),
                        chidon_type_limit: $("select#chidon_type_limit").val()
                    };
                    
                    $.post("ajax/get_staff.php", data, function(data){
                        $("#report").html(data);
                        $("#report input[type='text'], #report input[type='number']").keyup(updateStaff);
                        $("#report select").change(updateStaff);
                    });
                }
                
                function updateStaff( event ) {
                    var data = Object.assign({},
                        event.target.dataset,
                        { value: event.target.value }
                    );
                    
                    $.post("ajax/update_staff.php", data, function(response) {
                        response = JSON.parse(response);
                        console.log(response);
                    });
                }
                
                function createStaff( event ) {
                    event.preventDefault();
                    
                    var data = {};
                    $.each($(event.target).serializeArray(), function(index, field) {
                        data[field.name] = field.value;
                    });
                    
                    $.post("ajax/create_staff.php", data, function( response ) {
                        response = JSON.parse(response);
                        if (!response.success) {
                            alert(response.error);
                        } else {
                            generate_report();
                        }
                    });
                }
            });
        </script>
    </body>
</html>