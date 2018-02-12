<?php 
include ("get_camp_id.php");
$camp_id = get_camp_id();

//This code is from the add camper page.... 
//Both pages (add camper and add staff) should really be consolidated into one but I opted not to do it now b/c of time constraints.
$message = "";

if (isset($_POST['action'])) {
	require_once('db.php');
	require_once('file_save.php');
	
	if (isset($_FILES['photo'])) 
		$user_photo_id = get_user_photo_id();
	else 
		$user_photo_id = "NULL";

	$sql = "INSERT INTO admins SET ";
	$sql .= "first='". mysql_real_escape_string($_POST['first']) . "', ";
	$sql .= "last='" . mysql_real_escape_string($_POST['last']) . "', ";
	$username = mysql_real_escape_string($_POST['first']) . mysql_real_escape_string($_POST['last']);
	$sql .= "username='" . $username . "', ";
	$sql .= "title='Mr.', ";
	$sql .= "admin_email='" . mysql_real_escape_string($_POST['email']) . "', ";
	$sql .= "lang='en', ";
	$sql .= "admin_address1='" . mysql_real_escape_string($_POST['address1']) . "', ";
	$sql .= "admin_address2='" . mysql_real_escape_string($_POST['address2']) . "', ";
	$sql .= "admin_city='" . mysql_real_escape_string($_POST['city']) . "', ";
	$sql .= "admin_state='" . mysql_real_escape_string($_POST['state']) . "', ";
	$sql .= "admin_postal='" . mysql_real_escape_string($_POST['postal']) . "', ";
	$sql .= "admin_country='" . mysql_real_escape_string($_POST['country']) . "', ";
	$phone = mysql_real_escape_string($_POST['phone']);
	$sql .= "admin_phone_home='" . $phone . "', ";
	$sql .= "admin_phone_work='" . $phone . "', ";
	$sql .= "admin_phone_mobile='" . $phone . "', ";
	$sql .= "auth='', ";
	$sql = $sql . "camp_id=" . $camp_id . ", ";
	$password = $username;
	$sql .= "password='" . $password . "'; ";
	
$message = "success";
	$query = mysql_query($sql);
	if (!$query) {
	$message = $sql . '\n\n' . mysql_error();
	}else{
$sql2 = "SELECT admin_id FROM admins WHERE username='$username' LIMIT 1";
$result = mysql_fetch_array(mysql_query($sql2), MYSQL_ASSOC);
$id = $result['admin_id'];
$sql3 = "INSERT INTO admin_auths SET admin_id='$id', auth='camp', id='$camp_id';";
mysql_query($sql3);
}

echo $message;
}else{


?>
<script>
var action = '';
$(function() {
$('form a.submit').click(function(){
$.post("includes/staff_add.php", $("#add_staff_form").serialize(), function(data){
//alert(data);
if(data == 'success'){

alert("Staff Member was added successfully!");
var url = 'content.php?output=staffadd';
$.get(url,'',function(data){
$('.slider_container').append(data);
$('.slider_container .slider:last .col_title').append('<a class="slider_back"></a>');
$('.slider_container').data('url',url);
hideLoader();
initialize();
slide_width=773;
$('.slider_container').animate({'margin-left':parseInt($('.slider_container').css('margin-left')) - slide_width + 'px'}, 500, hideLoader());
});
}else{

alert("Error adding Staff Member, please try again");
}

});
});
});
</script>			
			<div class="slider">
				<div class="col_title"><span>Add a Staff Member</span></div>
				<div class="col_content">
                    <div class="module" id="module-info">
                        <div class="module_content">
                        	<p>Use this form to add individual staff members to the system. <a href="content.php?output=staffbulk" class="link">Add bulk staff</a></p>
                        	<p>Once added to the system you will be able to assign them to a group.</p>
                        </div>
                    </div>
                    								
					<? if ($message != "") : ?>
					<h1><label style="color:red;"><?=$message;?></label></h1>
					<? endif; ?>
					
                    <form name="add_staff_form" id="add_staff_form" action="includes/staff_add.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
                        <input type="hidden" name="action" id="action" value="">
						
                        <div class="module form" id="module-info">
                            <h1>Add Staff Member</h1>
                            <div class="module_content list">
                            	<ul>
                                	<li>
                                    	<span class="label">First Name</span>
                                        <span class="input"><input type="text" name="first" /></span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">Last Name</span>
                                        <span class="input"><input type="text" name="last" /></span>
                                        <div class="clear"></div>
                                    </li>
                                	<!--li>
                                    	<span class="label">Hebrew First Name</span>
                                        <span class="input"><input type="text" name="first_he" /></span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">Hebrew Last Name</span>
                                        <span class="input"><input type="text" name="last_he" /></span>
                                        <div class="clear"></div>
                                    </li-->
                                	<li>
                                    	<span class="label">Photo</span>
                                        <span class="input"><input type="file" name="photo" /></span>
                                    	<span class="tip">Maximum file size: 2MB. Minimum size: 180x225 (Larger is OK, the desired aspect ratio is: 1.25 times as high, as it is wide)</span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">Email</span>
                                        <span class="input"><input type="text" name="email" /></span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">Gender</span>
                                        <span class="input">
                                        	<input type="radio" name="gender" value="NULL"> Unknown
                                            <input type="radio" name="gender" value="M" style="width: auto;" > Male
                                            <input type="radio" name="gender" value="F" style="width: auto;" > Female
                                        </span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">Language</span>
                                        <span class="input">
                                        	<select name="lang" class="select">
                                            	<option>English</option>
                                                <option>עברית</option>
                                                <option>יידיש</option>	
                                            </select>
                                        </span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">Address</span>
                                        <span class="input"><input type="text" name="address1" /><input type="text" name="address2" /></span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">City</span>
                                        <span class="input"><input type="text" name="city" /></span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">State/Province</span>
                                        <span class="input"><input type="text" name="state" /></span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">Zip/Postal code</span>
                                        <span class="input"><input type="text" name="postal" /></span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">Country</span>
                                        <span class="input"><input type="text" name="country" /></span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">Phone</span>
                                        <span class="input"><input type="text" name="phone" /></span>
                                        <div class="clear"></div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <a href="#" class="button submit">Save</a>
                    </form>	
				</div>
			</div> <?php } ?>
