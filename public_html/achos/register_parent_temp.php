<?php
session_start();
$_SESSION['admin_id'] = $_POST['admin_id'];
$admin_id = $_SESSION['admin_id'];
//include("check_admin_id.php");
include("db.php");

$action = isset($_POST['action']) ? $_POST['action'] : ''; 
$submit_form = isset($_POST['submit_form']) ? $_POST['submit_form'] : 0; 

$message = "";
$next_page = "false";


// if form has been submitted, then perform validation, update/adds
if ($submit_form)
{
    // request to add a parent record
    if ($action == 'add') {
        $admin_id = insert_into_admins();
        if($admin_id > 0) {             
            send_parent_registration_confirmation_email(mysql_real_escape_string(trim($_POST['admin_email'])));     
            $_SESSION["admin_id"] = $admin_id;
            $next_page = "true";                                                
            // header("Location: http://www.mashpia.com/register_parent_2.php");            
        }
        else {
            $message = "Insert failed. Please try again.";
        }       
    }
    // request to edit a parent record
    elseif ($action == 'update')    {
        update_admins();
        $next_page = "true";
        //header("Location: register_parent_2.php");
    }
}
    
// on edit - get values  based on admin_id
if ($admin_id > 0) {
    include("camps/includes/classes/admin.php");
    $sql = "SELECT * FROM admins WHERE admin_id='" . mysql_real_escape_string($admin_id) . "'" ;        
    $query = mysql_query($sql);
    $row = mysql_fetch_assoc($query);   
    $admin = new admin($row);
}

// update admins record
function update_admins() {
    $admin_id = 0;
    $sql = "UPDATE admins  
            SET 
            first = '" . mysql_real_escape_string($_POST['first'])  . "' ,
            last =  '" . mysql_real_escape_string($_POST['last'])  . "' ,
            admin_address1 = '" . mysql_real_escape_string($_POST['admin_address1'])  . "' ,
            admin_address2 = '" . mysql_real_escape_string($_POST['admin_address2'])  . "' ,
            admin_city = '" . mysql_real_escape_string($_POST['admin_city'])  . "' ,
            admin_state = '" . mysql_real_escape_string($_POST['admin_state'])  . "' ,
            admin_postal = '" . mysql_real_escape_string($_POST['admin_postal'])  . "' ,
            admin_phone_home = '" . mysql_real_escape_string($_POST['admin_phone_home'])  . "' ,
            admin_phone_mobile = '" . mysql_real_escape_string($_POST['admin_phone_mobile'])  . "' ,
            admin_email = '" . mysql_real_escape_string($_POST['admin_email'])  . "' 
        WHERE admin_id ='" . mysql_real_escape_string($_POST['admin_id']) ."'" ;        
    $query = mysql_query($sql); 
    if($query){ 
    }   
    else{
        include('constant_file.php');
        @mail($programmers_email2, 'Error in program register_parent.php',  "error in SQL update statement: " , mysql_error() );        
    }   
}

// insert admins record
function insert_into_admins() {
    $sql = "INSERT INTO admins  
        (first,
        last, 
        admin_address1,
        admin_address2,
        admin_city,
        admin_state,
        admin_postal,
        admin_phone_home,
        admin_phone_mobile,
        admin_email,        
        username, 
        password, 
        lang,
        reminders,
        is_parent) 
        VALUES( '" . mysql_real_escape_string(trim($_POST['first']))  . "' ,
                '" . mysql_real_escape_string(trim($_POST['last']))  . "'  ,
                '" . mysql_real_escape_string(trim($_POST['admin_address1']))  . "' ,
                '" . mysql_real_escape_string(trim($_POST['admin_address2']))  . "' ,
                '" . mysql_real_escape_string(trim($_POST['admin_city']))  . "' ,
                '" . mysql_real_escape_string(trim($_POST['admin_state']))  . "' ,
                '" . mysql_real_escape_string(trim($_POST['admin_postal']))  . "' ,
                '" . mysql_real_escape_string(trim($_POST['admin_phone_home']))  . "' ,
                '" . mysql_real_escape_string(trim($_POST['admin_phone_mobile']))  . "' ,
                '" . mysql_real_escape_string(trim($_POST['admin_email']))  . "' ,
                '" . mysql_real_escape_string(trim($_POST['username']))  . "' ,
                '" . mysql_real_escape_string(trim($_POST['password']))  . "' ,
                '" . mysql_real_escape_string(trim($_POST['lang']))  . "' ,                 
                '" . mysql_real_escape_string(trim($_POST['reminders']))  . "', 1)" ;

    $query = mysql_query($sql);
    
    if($query){
        $admin_id = mysql_insert_id();
    }   
    else{
        include('constant_file.php');
        @mail($programmers_email2, 'Error in program register_parent.php',  "error in SQL insert statement: " , mysql_error() );        
    }   
    return $admin_id;   
}

function send_parent_registration_confirmation_email($parent_email) {   
    require_once("classes/send_mail.php");
    
    $mail_parms = array();
    $mail_parms['to'] = $parent_email;
    $mail_parms['subject'] = "Login Confirmation";
    $mail_parms['message'] = "Your login to mashpia.com has been confirmed. Your username is " . $_POST['username'] . " and your password is " . $_POST['password'] . ". Thank you." ;  $mail_parms['headers'] = "From: info@mashpia.com\r\nReply-To: info@mashpia.com";
    $mail_parms['headers'] = "From: info@mashpia.com\r\nReply-To: info@mashpia.com";

    $myMailClass = new MailClass();
    $success = $myMailClass->send_mail($mail_parms);
}
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" dir="<?=$dir?>">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=8" />
        <title>Registration Wizard - Tzivos Hashem Management System</title>
        <link rel="alternate" media="print" href="index.php">
        <link href="admin_styles.css" rel="stylesheet" type="text/css" />
        <script src="jquery.js" type="text/javascript"></script>
        <script src="camps/scripts/jquery.tools.min.js"></script>
        <script src="scripts/jquery.placeholder.js"></script>               
        
        <script>
            var next_page = "<?=$next_page;?>";
            var admin_id = "<?=$admin_id;?>";
            
            $(document).ready(function() {
            });

            $(function(){
                    $("#nav").height($("#content").height());
                    $('input').placeholder();
                });

            // validate input   
            function validation(){
            
                var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
                var address = document.getElementById('admin_email').value;
                
                if (document.getElementById('first').value == '') {
                    document.getElementById('first').focus();
                    alert("First Name is mandatory.");
                    return false;
                }   
                else if (document.getElementById('last').value == '') {
                    document.getElementById('last').focus();
                    alert("Last Name is mandatory.");
                    return false;
                }
                else if (document.getElementById('admin_phone_home').value == '') {
                    document.getElementById('admin_phone_home').focus();
                    alert("Home phone is mandatory.");
                    return false;
                }
                else if (document.getElementById('admin_email').value == '') {
                    document.getElementById('admin_email').focus();
                    alert("Email is mandatory.");
                    return false;
                }
                else if  (reg.test(address) != true) {                  
                    document.getElementById('admin_email').focus();
                    alert("Invalid email address.");
                    return false;               
                }
                else if (document.getElementById('username').value == '') {
                    document.getElementById('username').focus();
                    alert("Username is mandatory.");
                    return false;
                }
                else if (!isAlphaNumeric(document.getElementById('username').value)) {
                    document.getElementById('username').focus();
                    alert("Username can only contain letters and numbers.");
                    return false;
                }
                
                // check if username is already used                                                                 
                else if (username_not_duplicate(document.getElementById('username').value) ) {
                    document.getElementById('username').focus();
                    alert("Duplicate username. Please use another username");
                    return false;
                }           
                // check if email address already used                                                               
                else if (email_not_duplicate(document.getElementById('admin_email').value) ) {
                    document.getElementById('username').focus();
                    alert("Duplicate email address. If you already have an account, you may login directly at mashpia.com.");
                    return false;
                }           
                else if (document.getElementById('password').value != document.getElementById('password2').value) {
                    document.getElementById('password').focus();
                    alert("Passwords do not match.");
                    return false;
                }
                else if (document.getElementById('password').value == "") {
                    document.getElementById('password').focus();
                    alert("Password is mandatory");
                    return false;
                } 
                else if (document.getElementById('admin_address1').value == "") {
                    document.getElementById('admin_address1').focus();
                    alert("Address is mandatory");
                    return false;
                } 
                else if (document.getElementById('admin_city').value == "") {
                    document.getElementById('admin_city').focus();
                    alert("City is mandatory");
                    return false;
                } 
                else if (document.getElementById('admin_state').value == "") {
                    document.getElementById('admin_state').focus();
                    alert("State is mandatory");
                    return false;
                } 
                else if (document.getElementById('admin_postal').value == "") {
                    document.getElementById('admin_postal').focus();
                    alert("Zip/Postal code is mandatory");
                    return false;
                } 
                else
                {
                    // document.forms["login"].submit();
                }
            }
                            
            function username_not_duplicate(username) {
               //var function_name = "get_username"; 
               var function_name = "is_username_duplicate"; 
               var parameters = [username]; 
               var url = "camps/includes/get_functions.php?function_name=" + function_name + "&parameters=" + parameters;              
               var rslt = false; 
               $.ajax({ 
                     async: false, 
                     url: url, 
                     dataType: "json", 
                     success: function(data) {                   
                       if (data == true) {                   
                         rslt = true; 
                       }
                    }, 
                });
                return rslt; 
            }
            
            function email_not_duplicate(email) {
               //var function_name = "get_username"; 
               var function_name = "is_email_duplicate"; 
               var parameters = [email]; 
               var url = "camps/includes/get_functions.php?function_name=" + function_name + "&parameters=" + parameters;              
               var rslt = false; 
               $.ajax({ 
                     async: false, 
                     url: url, 
                     dataType: "json", 
                     success: function(data) {                   
                       if (data == true) {                   
                         rslt = true; 
                       }
                    }, 
                });
                return rslt; 
            }
            
            
            function isAlphaNumeric(sText)  {
                var ValidChars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890* ";
                var IsAlphabetic=true;
                var Char;           
                for (i = 0; i < sText.length; i++){
                    Char = sText.charAt(i);
                    if (ValidChars.indexOf(Char) == -1){
                    IsAlphabetic = false;
                    }
                }
                return IsAlphabetic;
            }   
            
            function check_next_page() {
                if (next_page == "true") {
                    var parent_registration = document.forms["parent_registration"];
                    parent_registration.elements["admin_id"].value = admin_id;
                    parent_registration.submit();
                }
            }           
        </script>
        <!--Copyright Ariel Shkedi 2007-2010-->
    </head>

    <body onload="check_next_page();">
    
        <FORM name="parent_registration" method="post" action="register_parent_2.php">
            <input type="hidden" name="admin_id" value="">
        </FORM>
    
    
        <NOSCRIPT>
            <P STYLE="color: red; font-size: larger;">Notice: You have javascript disabled. Some parts of the site will not function without javascript.</P>
        </NOSCRIPT>
        
        <div id="wrapper">
        
            <div id="nav" class="wizard">
                <div class="col_title_bg"></div>
                <div class="col_title">Menu</div>
                <? $curr = 1; ?>
                <? include("register_parent_menu.php"); ?>
            </div>
            
            <div id="content">
            
                <div class="col_title_bg"></div>
                
                <div class="slider_container">
                
                    <div class="slider">
                    
                        <div class="col_title"></div>
                        
                        <div class="col_content">
                            <h1>Step 1 - Parent Registration</h1>
     
                            <p>
                                We are currently experiencing technical difficulties, please try again later. 
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </body>
</html>
