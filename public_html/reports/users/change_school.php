<?php $debug = false; // default debugging is false
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

if ($admin_user['auth'] != 'super') {
    header("Location: /reports/users/");
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Change User Schools</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/fancy-checkbox.css" rel="stylesheet" type="text/css"/>
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <style>
            .options a.button {display: inline-block;}
            .options {text-align: center;}
            table {width: 100%;}
            td, th{padding: 4px 8px;font-size: 12px;}
            label.fancy-check-container {width: 20%;display: inline-block;text-align: center;margin: 15px;}
            input#last_name {background: none;border: none;border-bottom: 1px solid;font-size: 14px;padding: 2px;}
            tr, th, td {border: 1px dashed black;padding: 6px;}
            .school {width: 200px;}
            .grade {width: 50px;}
            select.school_id {max-width: 175px;}
        </style>
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Change User School</h1>
        
        <strong>Please note that this page <em>will not work in Internet Explorer</em>. If you are using Internet Explorer please use Chrome/Firefox/Safari/Edge for this page</strong>
        
        <h2>Report Options</h2>
        <div class="options">
            <i class="fa fa-user" aria-hidden="true"></i>
            <label for="last_name">Last Name Starts With: </label>
            <input type="text" id="last_name" name="last_name" placeholder="Volovolvitch" />
        </div>
        
        <div class="options">
            <label class="fancy-check-container">
                <input type="checkbox" id="registered" name = "registered" checked/>
                <span class="fancy-check"></span>
                
                <strong>Registered</strong>
            </label>
            
            <label class="fancy-check-container">
                <input type="checkbox" id="not-registered" name = "not-registered"/>
                <span class="fancy-check"></span>
                
                <strong>Not Registered</strong>
            </label>
        </div>
        
        <div class="options">
            <span class="option_space">
                <a class="button" id="generate"><i class="fa fa-refresh" aria-hidden="true"></i> Generate / Refresh Report</a>
            </span>
        </div>
        
        <hr style="display: block;"/>
        
        <div id="student_report">
            
            <table>
                <thead>
                    <tr>
                        <th>Serial Number</th><th>Name</th><th>Registered</th><th>Current School</th><th>Current Class</th><th>Move To School</th><th>Move To Class</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="8" style="text-align: center">No Students Loaded</td>
                    </tr>
                </tbody>
            </table>
            
        </div>
        <script>
            var debug = <?=$debug ? "true" : "false"?>;
            var admin = <?= $admin_user['auth'] == 'super' ? "true" : "false" ?>;// admin mode?
            
            $(document).ready(function() {
                
                var schools = [];
                
                function getOptions() {
                    return {
                        last_name: $("#last_name").val(),
                        registered: $("#registered")[0].checked,
                        not_registered: $("#not-registered")[0].checked
                    };
                }
                
                function generateReport() {
                    var options = getOptions();
                    
                    if (!options.last_name) {
                        alert("Please enter something for the last name"); return false;
                    }
                    
                    if (!options.registered && !options.not_registered) {
                        alert("Children are or are not registered. There is no middle ground ;-)"); return false;
                    }
                    
                    options.debug = true;
                    
                    $.post("ajax/change_school.php?action=report", options, function(response){
                        response = JSON.parse(response);
                        
                        if (!response.success) {
                            alert(response.error); return false;
                        }
                        
                        schools = response.schools;
                        renderTable(response.users);
                    });
                }
                
                function moveUser(event){
                    var data = {
                        user_id: event.target.dataset.user_id,
                        school_id:  $(event.target).parent().parent().find("select.school_id").val(),
                        class_id:   $(event.target).parent().parent().find("select.class_id").val()
                    };
                    
                    $.post("ajax/change_school.php?action=move", data, function(response){
                        response = JSON.parse(response);
                        if (!response.success) {
                            alert(response.error); return false;
                        } else {
                            alert("Successfully moved child, please refresh report to see updated information");
                        }
                    });
                }
                
                function renderTable(users) {
                    var html = "";
                    for (var i = 0; i < users.length; i++) {
                        var user = users[i];
                        var row = "<tr>";
                        row += "<td>" + user.user_serial + "</td>";
                        row += "<td>" + user.last + ", " + user.first + "</td>";
                        row += "<td>" + (user.user_registered ? "Yes" : "No") + "</td>";
                        row += "<td>" + user.school_name + "</td>";
                        row += "<td>" + user.class_grade + (user.class_sub ? " - " + user.class_sub : "") + "</td>";
                        
                        row += "<td>" + renderSchoolOptions() + "</td>";
                        row += "<td class='class_id'>" + renderClassOptions(schools[0].school_id) + "</td>";
                        
                        row += "<td><a class='button move_user' data-user_id='" + user.user_id + "'>Save</a></td>";
                        
                        row += "</tr>";
                        html += row;
                    }
                    $("#student_report table tbody").html(html);
                    
                    $("select.school_id").change(function(event){
                        var class_td = $(event.target).parent().parent().find("td.class_id"); // go to the row and find the td for the class_dropdown
                        class_td.html(renderClassOptions(event.target.value)); // render the new dropdown and put it in the correct location
                    });
                    $("a.button.move_user").click(moveUser);
                }
                
                function renderSchoolOptions() {
                    var dropdown = "<select class='school_id'>";
                    for(var i = 0; i < schools.length; i++){
                        dropdown += "<option value='"+ schools[i].school_id+"'>" + schools[i].name + "</option>";
                    }
                    dropdown    += "</select>";
                    return dropdown;
                }
                
                function renderClassOptions(school_id){
                    var classes = schools.find(function(school){
                        return school.school_id === school_id;
                    }).classes; // find the school by the school_id and get the classes
                    
                    var dropdown = "<select class='class_id'>";
                    for(var i = 0; i < classes.length; i++){
                        dropdown += "<option value='"+ classes[i].class_id+"'>" + classes[i].name + "</option>";
                    }
                    dropdown    += "</select>";
                    return dropdown;
                }
                
                $("a#generate").click(generateReport);
            });
        </script>
    </body>
</html>