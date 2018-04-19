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
            .options {text-align: center;margin-bottom: 15px;}
            table {width: 100%;}
            td, th{padding: 4px 8px;font-size: 12px;}
            label.fancy-check-container {width: 20%;display: inline-block;text-align: center;margin: 15px;}
            input[type="text"] {background: none;border: none;border-bottom: 1px solid;font-size: 14px;padding: 2px;}
            tr, th, td {border: 1px dashed black;padding: 6px;}
            .school {width: 200px;}
            .grade {width: 50px;}
            select.school_id {max-width: 175px;}
            .half {width: 45%;display: inline-block;}
            .parent {text-align: center;margin: 15px;}
            .parent_input {text-align: center;display: inline-block;padding: 5px;}
            .child {display: inline-block; width: 44%;padding: 3%;}
            button.button {
                padding: 6px; 10px;
                background: url("/images/bg_smallButton.png") repeat-x scroll 0 0 #D1D1D1;
                border: 1px solid;
                border-color: #D3D3D3 #AAAAAA #888888;
                color: #222222;
                font-weight: normal;
                font-size: 13.33px;
                font-family: "Trebuchet MS",Arial,Helvetica,sans-serif;
                text-shadow: 0 1px 0 #FFFFFF;
                white-space: nowrap;
                width: auto;
                cursor: pointer;
                margin: 3px 0;
            }
            button.button:hover, button.button:focus {
                background-position: bottom;
                border-color: #888888 #AAAAAA #D3D3D3;
            }
        </style>
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Search Parent Accounts</h1>
        
        <!--<strong>Please note that this page <em>will not work in Internet Explorer</em>. If you are using Internet Explorer please use Chrome/Firefox/Safari/Edge for this page</strong>-->
        
        <h2>Report Options</h2>
        <div class="options">
            <div class="half">
                <i class="fa fa-user" aria-hidden="true"></i>
                <label for="username">Username: </label>
                <input type="text" id="username" name="username" placeholder="admin" />
            </div>
            <strong>OR</strong>
            <div class="half">
                <i class="fa fa-envelope" aria-hidden="true"></i>
                <label for="email">Email Address: </label>
                <input type="text" id="email" name="email" placeholder="test@test.com" />
            </div>
            <br/><br/>
            <strong>OR</strong>
            <div class="half">
                <label for="last_name">Last Name Starts: </label>
                <input type="text" id="last_name" name="last_name" placeholder="Goldb" />
            </div>
            <strong>OR</strong>
            <div class="half">
                <i class="fa fa-key" aria-hidden="true"></i>
                <label for="parent_id">Parent ID: </label>
                <input type="text" id="parent_id" name="parent_id" placeholder="5678" />
            </div>
        </div>
        
        <div class="options">
            <span class="option_space">
                <button class="button" id="generate"><i class="fa fa-refresh" aria-hidden="true"></i> Generate / Refresh Report</button>
            </span>
        </div>
        
        <hr style="display: block;">
        
        <div id="parent_report">
            
            <h3>No Accounts Found</h3>
            
        </div>
        <script>
            var debug = <?=$debug ? "true" : "false"?>;
            var admin = <?= $admin_user['auth'] == 'super' ? "true" : "false" ?>;// admin mode?
            
            $(document).ready(function() {
                
                function getOptions() {
                    return {
                        username:   $("#username").val(),
                        email:      $("#email").val(),
                        last_name:  $("#last_name").val(),
                        parent_id:  $("#parent_id").val()
                    };
                }
                
                function generateReport() {
                    var options = getOptions();
                    
                    if (!options.username && !options.email && !options.last_name && !options.parent_id) {
                        alert("Please enter something in one of the options"); return false;
                    }
                    
                    $.post("ajax/parent_accounts.php?action=report", options, function(response){
                        response = JSON.parse(response);
                        
                        if (!response.success) {
                            alert(response.error); return false;
                        }
                        var html = "";
                        for (var i = 0; i < response.parents.length; i++){
                            html += renderParent(response.parents[i]);
                        }
                        $("#parent_report").html(html);
                    });
                }
                
                function renderParent(parent) {
                    var html = "<div class='parent'>";
                    html    +=  "<h2>Parent ID: "+(parent.admin_id ? parent.admin_id : "")+"</h2>";
                    
                    html    +=  "<div class='parent_input'>";
                    html    +=      "<strong>Username: </strong>";
                    html    +=      "<input type='text' disabled id='username' value='"+ (parent.username ? parent.username : "") +"'/>";
                    html    +=  "</div>";
                    
                    html    +=  "<div class='parent_input'>";
                    html    +=      "<strong>Password: </strong>";
                    html    +=      "<input type='text' disabled id='password' value='"+ (parent.password ? parent.password : "") +"'/>";
                    html    +=  "</div>";
                    
                    html    +=  "<div class='parent_input'>";
                    html    +=      "<strong>Tatty: </strong>";
                    html    +=      "<input type='text' disabled id='tatty' value='"+ (parent.tatty ? parent.tatty : "") +"'/>";
                    html    +=  "</div>";
                    
                    html    +=  "<div class='parent_input'>";
                    html    +=      "<strong>Mommy: </strong>";
                    html    +=      "<input type='text' disabled id='mommy' value='"+ (parent.mommy ? parent.mommy : "") +"'/>";
                    html    +=  "</div>";
                    
                    html    +=  "<div class='parent_input'>";
                    html    +=      "<strong>E-Mail: </strong>";
                    html    +=      "<input type='text' disabled id='email' value='"+ (parent.admin_email ? parent.admin_email : "") +"'/>";
                    html    +=  "</div>";
                    
                    html    +=  "<div class='parent_input'>";
                    html    +=      "<strong>Cell Phone: </strong>";
                    html    +=      "<input type='text' disabled id='admin_phone_mobile' value='"+ (parent.admin_phone_mobile ? parent.admin_phone_mobile : "") +"'/>";
                    html    +=  "</div>";
                    
                    html    +=  "<h3>Children: (" + parent.children.length + "/" + (parent.children.length + parent.other_children) +")</h3>";
                    html    +=  "<div class='children'>";
                    for(var i = 0; i < parent.children.length; i++){
                        var child = parent.children[i];
                        html    +=  "<div class='child'>";
                        html    +=      "<strong>Name: </strong>" + child.first + " " + child.last + "<br/>";
                        html    +=      "<strong>Serial #: </strong>" + child.user_serial;
                        html    +=  "</div>";
                    }
                    html    +=  "</div>";
                    html    += "</div>";
                    return html;
                }
                
                $("#generate").click(generateReport);
            });
        </script>
    </body>
</html>