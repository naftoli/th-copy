<?
$admin_auth = array('user'); 
require('header.php');

if (isset($_POST['personal_info'])) {
    update_admins($admin_user['admin_id']);
}
// update admins record
function update_admins($admin_id) {
    $sql = "UPDATE admins  
            SET 
            first = '" . mysql_real_escape_string($_POST['first'])  . "' ,
            last =  '" . mysql_real_escape_string($_POST['last'])  . "' ,
            admin_address1 = '" . mysql_real_escape_string($_POST['admin_address1'])  . "' ,
            admin_address2 = '" . mysql_real_escape_string($_POST['admin_address2'])  . "' ,
            admin_city = '" . mysql_real_escape_string($_POST['admin_city'])  . "' ,
            admin_state = '" . mysql_real_escape_string($_POST['admin_state'])  . "' ,
            admin_postal = '" . mysql_real_escape_string($_POST['admin_postal'])  . "' , 
            admin_country = '" . mysql_real_escape_string($_POST['admin_country']) . "' , 
            admin_phone_home = '" . mysql_real_escape_string($_POST['admin_phone_home'])  . "' ,
            admin_phone_mobile = '" . mysql_real_escape_string($_POST['admin_phone_mobile'])  . "' ,
            admin_email = '" . mysql_real_escape_string($_POST['admin_email'])  . "', 
            username = '" . mysql_real_escape_string( $_POST['username'] ) . "', 
            password = '" . mysql_real_escape_string( $_POST['password'] ) . "' 
        WHERE admin_id = $admin_id" ;       
    $query = mysql_query($sql); 
    if(!$query){ 
        include('constant_file.php');
        @mail($programmers_email2, 'Error in program register_parent.php',  "error in SQL update statement: " , mysql_error() );        
    }   
}

$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
require_once 'classes/admin.php';
$admin = new \classes\admin($row);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Profile</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <script>
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
                    alert("Username cannot be blank.");
                    return false;
				}
				else if (document.getElementById('password').value == '') {
                    document.getElementById('password').focus();
                    alert("Password cannot be blank.");
                    return false;
                else {
                    return true;            
                }
            }
            
            function popUp() {
            	alert("Please Note: If you have changed your username or password you will automatically be logged out and you will need to login with the new username / password that you entered.");
            }
        </script>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1 class="no-print">Profile</h1>
        
		<form method="post" action="parent_profile.php" accept-charset="UTF-8" name="info"  onsubmit="popUp();return validation();">                                 
                                
	        <h2>Personal Info</h2> 
	        <div class="module" id="module-info">
	            <div class="module_content">
	                <div class="lists form">
	                  <ul>
	                      <li>
	                          <span class="label"><label for="first">First Name</label></span>
	                          
	                          <span class="input"><input name="first" type="text" id='first' value='<?=$admin->first?>' /></span>
	                      </li>
	                      <li>
	                          <span class="label"><label for="last">Last Name</label></span>
	                          <span class="input"><input name="last" type="text" id='last' value='<?=$admin->last?>' /></span>
	                      </li>
	                        <li>
	                            <span class="label"><label for="admin_address1">Address</label></span>
	                            <span class="input"><input name="admin_address1" type="text"  value='<?=$admin->admin_address1?>' /></span>
	                            <div class="clear"></div>
	                            
	                            <span class="label"></span>
	                            <span class="input"><input name="admin_address2" type="text"   value='<?=$admin->admin_address2?>' /></span>
	                            <div class="clear"></div>
	                            
	                            <span class="label"><label >City  / State / Zip </label></span>
	                            <span class="input city"><input name="admin_city" type="text"    value='<?=$admin->admin_city?>' /></span>
	                            
	                            <span class="input state"><input name="admin_state" type="text"    value='<?=$admin->admin_state?>' /></span>
	                            
	                            <span class="input zip"><input name="admin_postal" type="text"    value='<?=$admin->admin_postal?>'  /></span>
	                        </li>
	                      <li>
	                      	 <span class="label"><label for="country">Country</label></span>
	                      	 <span class="input country"><input name="admin_country" type="text" value='<?=$admin->admin_country?>' /></span>
	                      </li>  
	                      <li>
	                          <span class="label"><label for="home">Home Phone</label></span>
	                          <span class="input"><input name="admin_phone_home" id="admin_phone_home" type="text" value='<?=$admin->admin_phone_home?>'  /></span>
	                      </li>
	                      <li>
	                          <span class="label"><label for="mobile">Cell Phone</label></span>
	                          <span class="input"><input name="admin_phone_mobile" type="text"   value='<?=$admin->admin_phone_mobile?>' /></span>
	                      </li>
	                      <li>
	                          <span class="label"><label for="email">Email Address</label></span>
	                          <span class="input"><input name="admin_email" id="admin_email" type="text"  value='<?=$admin->admin_email?>' /></span>
	                      </li>
	                    </ul>
	                </div>
	            </div>
	        </div>
	        
	        <h2>Login Info</h2> 
	        <div class="module" id="module-info">
	            <div class="module_content">
	                <div class="lists form">
	                  <ul>
	                      <li>
	                          <span class="label"><label for="first">Username</label></span>
	                          
	                          <span class="input"><input name="username" type="text" id='username' value='<?=$admin->username?>' /></span>
	                      </li>
	                      <li>
	                          <span class="label"><label for="last">Password</label></span>
	                          <span class="input"><input name="password" type="password" id='password' value='<?=$admin->password?>' /></span>
	                      </li>
	                    </ul>
	                </div>
	            </div>
	        </div>
	
	        <div align='center'><input type="submit" value="Update" class="button" name="personal_info"></div>
	        
	    </form>