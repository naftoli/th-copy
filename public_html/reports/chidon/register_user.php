<?php
include(dirname(__FILE__)."/../inc/header.php");

if ($admin_user['auth'] != 'super') {
    header("Location: /reports/chidon/");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Chidon Reports | Register User</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="../inc/css/report.css" rel="stylesheet" type="text/css">
<!--        Rotating Spinner, grey dropdowns and fancy checkboxes... -->
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/fancy-checkbox.css" rel="stylesheet" type="text/css"/>
<!--        Nice quick icons... -->
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <style>
            div#register, #student-info, div#t_shirt_update, div#update_host_box { display: none; }
            .option.half {
                width: 48%;
                display: inline-block;
            }
            .option{padding: 5px;}
        </style>
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Register User For Chidon</h1>
        
        <div class="options">
            <label for="user_serial">
                <i class="fa fa-barcode" aria-hidden="true"></i>
                Serial Number:
            </label>
            <input type="text" name="user_serial" id="user_serial" />
            OR
            <label for="chidon_id">
                Chidon ID:
            </label>
            <input type="text" name="chidon_id" id="chidon_id" />
            <a id="user_load" class="button">
                <i class="fa fa-spinner" aria-hidden="true"></i>
                Load Info
            </a>
        </div>
        
        <div id="error"></div>
        <div id="msg"></div>
        
        <div id="student-info">
            <h2>Chayol Info</h2>
            <table>
                <tr>
                    <th>First</th><th>Last</th><th>Registered in CTH</th><th>Registered in Chidon</th><th>Has Parent Account</th>
                </tr>
                <tr>
                    <td id="first"></th>
                    <td id="last"></td>
                    <td id="registered_cth"></td>
                    <td id="registered_chidon"></td>
                    <td id="parent"></td>
                </tr>
            </table>
        </div>
        
        <div id="register">
            <h2>Register For Chidon</h2>
            <div class="options">
                <input type="hidden" id="user_id"/>
                <input type="hidden" id="school_id"/>
                <label for='size'>T-Shirt: </label>
                <select id="size" name='size'>
                    <optgroup label="Children">
                        <option value="children xs">Children Extra Small</option>
                        <option value="children s">Children Small</option>
                        <option value="children m">Children Medium</option>
                        <option value="children l">Children Large</option>
                        <option value="children xl">Children Extra Large</option>
                    </optgroup>
                    <optgroup label="Adult">
                        <option value="adult s"> Adult Small</option>
                        <option value="adult m"> Adult Medium</option>
                        <option value="adult l"> Adult Large</option>
                        <option value="adult xl">Adult XL</option>
                    </optgroup>
                </select>
                
                <a class="button" id="register">
                    Register
                </a>
            </div>
        </div>
        
        <div id="t_shirt_update">
            <h2>Update T-Shirt Size</h2>
            <div class="options">
                <input type="hidden" id="th_chidon_id"/>
                <label for='size_update'>T-Shirt: </label>
                <select id="size_update" name='size_update'>
                    <optgroup label="Children">
                        <option value="children xs">Children Extra Small</option>
                        <option value="children s">Children Small</option>
                        <option value="children m">Children Medium</option>
                        <option value="children l">Children Large</option>
                        <option value="children xl">Children Extra Large</option>
                    </optgroup>
                    <optgroup label="Adult">
                        <option value="adult s"> Adult Small</option>
                        <option value="adult m"> Adult Medium</option>
                        <option value="adult l"> Adult Large</option>
                        <option value="adult xl">Adult XL</option>
                    </optgroup>
                </select>
                
                <a class="button" id="t_shirt_update">
                    Update
                </a>
            </div>
        </div>
        
        <div id="update_host_box">
            <h2>Update Host Information</h2>
            
            <form id="update_host_form">
                <div class="option half">
                    <input type="hidden" name="th_chidon_id" id="th_chidon_id"/>
                    
                    <label for="host">Family Name: </label>
                    <input type="text" id="host" name="host" />
                </div>
                <div class="option half">
                    <label for="host_number">Phone Number: </label>
                    <input type="text" id="host_number" name="host_number" />
                </div>
                <div class="option half">
                    <label for="host_address1">House Number: </label>
                    <input type="text" id="host_address1" name="host_address1" />
                </div>
                <div class="option half">
                    <label for="host_address2">House Street: </label>
                    <input type="text" id="host_address2" name="host_address2" />
                </div>
                <div class="option">
                    <label for="between_streets">Cross Streets (e.g. Crown and Kingston): </label>
                    <input type="text" id="between_streets" name="between_streets" style="width: 390px;"/>
                </div>
                
                <input type="submit" value="update" id="update_host_submit" />
            </form>
        </div>
        
        <script>
            $(document).ready(function(){
                $('#user_load').click(function(){
                    $("#student-info").hide();
                    $("div#register").hide();
                    $("div#update_host_box").hide();
                    $("div#t_shirt_update").hide();
                    // parse the data
                    var data = {
                        user_serial: $("#user_serial").val(),
                        chidon_id: $("#chidon_id").val()
                    };
                    // submit the request...
                    $.post("ajax/get_user.php", data, function(response){
                        response = JSON.parse(response);
                        // make sure there is no error in the response
                        if (!response.success) {$("#error").text("Server Error: " + response.error); return false;}
                        else {$("#error").text("");} // clear the error message if request was good...
                        
                        var user = response.user;
                        
                        $("td#first").text(user.first);     $("td#last").text(user.last);
                        $("td#registered_cth").text(user.user_registered ? "Yes" : "No");
                        $("td#registered_chidon").text(user.th_chidon_id ? "Yes (" + user.th_chidon_id + ")" : "No");
                        $("td#parent").text(user.admin_id ? "Yes" : "No");
                        $("#user_id").val(""); // clear the user_id on content refresh
                        
                        $("#student-info").show(); // show the new content...
                        
                        if (!user.th_chidon_id && user.admin_id) {
                            $("#user_id").val(user.user_id);
                            $("#school_id").val(user.school_id);
                            $("div#register").show();
                        } else if(user.th_chidon_id) {
                            $("#error").text("Chayol is already Registered in Chidon");
                            $("#th_chidon_id").val(user.th_chidon_id);
                            $("select#size_update").val(user.size);
                            $("div#t_shirt_update").show();
                        } else {
                            $("#error").text("Chayolim in the Chidon must have a parent account");
                        } // end client side validations
                        
                        if (user.date_paid) {
                            $("form#update_host_form input#th_chidon_id").val(user.th_chidon_id);
                            $("form#update_host_form input#host").val(user.host);
                            $("form#update_host_form input#host_number").val(user.host_number);
                            $("form#update_host_form input#host_address1").val(user.host_address1);
                            $("form#update_host_form input#host_address2").val(user.host_address2);
                            $("form#update_host_form input#between_streets").val(user.between_streets);
                            $("#update_host_submit").val("Update");
                            $("div#update_host_box").show();
                        }
                    }); // and ajax response
                }); // end user_load
                
                $('a#register').click(function(){
                    // submit the data to the server...
                    var data = {
                        user_id:    $("input#user_id").val(),
                        school_id:  $("input#school_id").val(),
                        t_shirt:    $("select#size").val(),
                    };
                    // submit the request...
                    $.post("ajax/register_user.php", data, function(response){
                        response = JSON.parse(response);
                        if (!response.success) {
                            $("#error").text("Server Error: " + response.error); return false;
                        } else {
                            $("#error").text("");
                            $("#msg").text("User registered in Chidon. Chidon ID is: " + response.chidon_id);
                        } // clear the error message if request was good...
                    }); // and AJAX request
                }); // end registration
                
                $('a#t_shirt_update').click(function(){
                    // submit the data to the server...
                    var data = {
                        th_chidon_id:   $("input#th_chidon_id").val(),
                        size:           $("select#size_update").val(),
                    };
                    // submit the request...
                    $.post("ajax/update_user_size.php", data, function(response){
                        response = JSON.parse(response);
                        if (!response.success) {
                            $("#error").text("Server Error: " + response.error); return false;
                        } else {
                            $("#error").text("");
                            $("#msg").text("User T-Shirt Size Updated");
                        } // clear the error message if request was good...
                    }); // and AJAX request
                }); // end registration
                
                $("form#update_host_form").submit( function( event ){
                    event.preventDefault();
                    $("#update_host_submit").val("Updating....");
                    
                    var data = {};
                    $.each($(event.target).serializeArray(), function(index, field) {
                        data[field.name] = field.value;
                    });
                    
                    $.post("ajax/update_user_host.php", data, function( response ) {
                        response = JSON.parse(response);
                        if ( !response.success ) {
                            alert(response.error);
                            $("#update_host_submit").val("Update Failed. Try again.");
                        }
                        else {
                            $("#update_host_submit").val("Updated!");
                        }
                    });
                });
            }); // end on document load
        </script>
    </body>
</html>