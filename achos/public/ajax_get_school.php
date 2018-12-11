<?
include("db.php");
include("classes/school.php");

$school_id = $_GET['school_id'];

$sql = "SELECT * FROM schools WHERE school_id=" . $school_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$school = new school($row);

$sql = "SELECT * FROM child_types";
$query = mysql_query($sql);
?>

<TABLE>
	<TR>
		<TD>
			Name
		</TD>
		<TD>
			<input type="text" value="<?=$school->school_name;?>" name="school_name">
		</TD>
	</TR>
	
	<TR>
		<TD>
			Hebrew Name 
		</TD>
		</TD>
			<input type="text" value="<?=$school->school_name_he;?>" name="school_name">
		</TD>
	</TR>

	<TR>
		<TD>
			Tzivos Hashem Type
		</TD>
		<TD>
			<select name="child_type_id">
				<? while ($row = mysql_fetch_assoc($query)) : ?>
				<option value="<?=$row['child_type_id'];?>"><?=$row['child_type_name'];?></option>																								
				<? endwhile; ?>
			</select>
		</TD>
	</TR>

	<TR>
		<TD>
			Gender 
		</TD>
		<TD>
			<input type="radio" checked="" value="M" name="school_gender">Boys
			<input type="radio" value="F" name="school_gender">Girls
			<input type="radio" value="B" name="school_gender">Both
		</TD>
	</TR>

	<TR>
		<TD>
			Address 1 
		</TD>
		<TD>
			<input type="text" maxlength="255" value="<?=$school->school_address1;?>" name="address1">
		</TD>
	</TR>	
	
	<TR>
		<TD>
			Address 2 
		</TD>
		<TD>
			<input type="text" maxlength="255" value="<?=$school->school_address2;?>" name="address2">
		</TD>
	</TR>	

	<TR>
		<TD>
			City
		</TD>
		<TD>
			<input type="text" maxlength="255" value="<?=$school->school_city;?>" name="school_city">
		</TD>
	</TR>	

	<TR>
		<TD>
			State/Province
		</TD>
		<TD>
			<input type="text" maxlength="255" value="<?=$school->school_state;?>" name="school_state">
		</TD>
	</TR>	

	<TR>
		<TD>
			Zip/Postal code
		</TD>
		<TD>
			<input type="text" maxlength="255" value="<?=$school->school_postal;?>" name="school_postal">
		</TD>
	</TR>	

	<TR>
		<TD>
			Country
		</TD>
		<TD>
			<input type="text" maxlength="255" value="<?=$school->school_country;?>" name="school_country">
		</TD>
	</TR>	

	<TR>
		<TD>
			Phone
		</TD>
		<TD>
			<input type="text" maxlength="255" value="<?=$school->school_phone;?>" name="school_phone">
		</TD>
	</TR>	

	<TR>
		<TD>
			Our school does not have a school logo
			
		</TD>
		<TD>
			<? if ($school->school_no_logo == 1) : ?>
			<input checked type="checkbox" value="1" class="checkbox" name="school_no_logo">
			<? else : ?>
			<input type="checkbox" value="0" class="checkbox" name="school_no_logo">
			<? endif; ?>
		</TD>
	</TR>	

	<TR>
		<TD>
			Logo - PNG, GIF, or JPEG, but a transparent PNG is strongly recommended
		</TD>
		<TD>
			<? if ($school->school_logo_id > 0) : ?>
			<img height="100" alt="" src="/file_view.php?id=<?=$school->school_logo_id;?>">
			<? endif; ?>
			<input type="file" class="file" name="logo">
		</TD>
	</TR>	

	<TR>
		<TD colspan="2">
			<h2>Shipping Info</h2>
		</TD>
	</TR>	

	<TR>
		<TD>
			Method
		</TD>
		<TD>
			<input type="radio" checked="" value="pickup" name="shipping_method">Pickup
			<input type="radio" value="deliver" name="shipping_method">Deliver
		</TD>
	</TR>	
	
	<TR>
		<TD>
			Shipping First
		</TD>
		<TD>
			<input type="text" maxlength="128" value="<?=$school->shipping_first;?>" name="shipping_first">
		</TD>
	</TR>	
	
	<TR>
		<TD>
			Shipping Last
		</TD>
		<TD>
			<input type="text" maxlength="128" value="<?=$school->shipping_last;?>" name="shipping_last">
		</TD>
	</TR>	
	
	<TR>
		<TD>
			Shipping Phone
		</TD>
		<TD>
			<input type="text" maxlength="255" value="<?=$school->shipping_phone;?>" name="shipping_phone">
		</TD>
	</TR>	
	
	<TR>
		<TD>
			Shipping Address 1
		</TD>
		<TD>
			<input type="text" maxlength="255" value="<?=$school->shipping_address1;?>" name="shipping_address1">
		</TD>
	</TR>	
	
	<TR>
		<TD>
			Shipping Address 2
		</TD>
		<TD>
			<input type="text" maxlength="255" value="<?=$school->shipping_address2;?>" name="shipping_address2">
		</TD>
	</TR>	
	
	<TR>
		<TD>
			Shipping City
		</TD>
		<TD>
			<input type="text" maxlength="255" value="<?=$school->shipping_city;?>" name="shipping_city">
		</TD>
	</TR>	
	
	<TR>
		<TD>
			Shipping State/Province
		</TD>
		<TD>
			<input type="text" maxlength="255" value="<?=$school->shipping_state;?>" name="shipping_state">
		</TD>
	</TR>	
	
	<TR>
		<TD>
			Shipping Zip/Postal code
		</TD>
		<TD>
			<input type="text" maxlength="255" value="<?=$school->shipping_postal;?>" name="shipping_postal">
		</TD>
	</TR>	
	
	<TR>
		<TD>
			Shipping Country
		</TD>
		<TD>
			<input type="text" maxlength="255" value="<?=$school->shipping_country;?>" name="shipping_country">
		</TD>
	</TR>	
	
	<TR>
		<TD colspan="2">
			<h2>File Upload</h2>
		</TD>
	</TR>	

	<TR>
		<TD colspan="2">
			File - Use this to upload a database of your students for us to import, or to send us other files
		</TD>
	</TR>	

	<TR>
		<TD colspan="2">
			<a style="background-color: lightblue;" href="students.xls">Download</a> 
			a spreadsheet template to use when sending us students to import.
		<TD>
	</TR>	

	<TR>
		<TD colspan="2">
			<a style="background-color: lightblue;" href="Uploading_your_School_Database.doc">Instructions</a> 
			for what to send us for the student import.		
		</TD>
	</TR>	

	<TR>
		<TD colspan="2">
			<input type="file" class="file" name="file">
		</TD>
	</TR>	
	
	<TR>
		<TD colspan="2">
			<h2>Kiosk Settings</h2>
		</TD>
	</TR>	

	<TR>
		<TD colspan="2">
			Kiosk Logo - PNG, GIF, or JPEG, but a transparent PNG is strongly recommended.
		</TD>
		<TD>
		</TD>
	</TR>	

	<TR>
		<TD colspan="2">
			<img height="100" alt="" src="/file_view.php?id=<?=$school->school_logo_kiosk_id;?>">
		</TD>
	</TR>	

	<TR>
		<TD colspan="2">
			<input type="file" class="file" name="logo_kiosk">
		</TD>
	</TR>	

	<TR>
		<TD>
			Our kiosks have printers
		</TD>
		<TD>
			<? if ($school->kiosk_print == 1) : ?>
			<input CHECKED type="checkbox" value="1" class="checkbox" name="kiosk_print">
			<? else : ?>
			<input CHECKED type="checkbox" value="0" class="checkbox" name="kiosk_print">
			<? endif; ?>		
		</TD>
	</TR>	

	<TR>
		<TD>
			Our school has a store 
		</TD>
		<TD>
			<? if ($school->school_store == 1) : ?>
			<input CHECKED type="checkbox" value="1" class="checkbox" name="school_store">
			<? else : ?>
			<input type="checkbox" value="0" class="checkbox" name="school_store">
			<? endif; ?>		
		</TD>
	</TR>	

	<TR>
		<TD colspan="2">
			<h2>Other Settings</h2>
		</TD>
	</TR>	

	<TR>
		<TD>
			Home School
		</TD>
		<TD>
			<? if ($school->school_settings == "home_school") : ?>
			<input CHECKED type="checkbox" value="home_school" name="home_school">
			<? else : ?>
			<input type="checkbox" value="home_school" name="home_school">
			<? endif; ?>		
		</TD>
	</TR>	

	<TR>
		<TD>
			CC Number
		</TD>
		<TD>
			<input type="text" maxlength="19" value="<?=$school->cc_number;?>" name="cc_number">
		</TD>
	</TR>	

	<TR>
		<TD>
			Expires MM/YY
		</TD>
		<TD>
			<input type="text" maxlength="5" value="<?=$school->cc_exp;?>" name="cc_exp">
		</TD>
	</TR>	

	<TR>
		<TD>
			CVV
		</TD>
		<TD>
			<input type="text" maxlength="4" value="<?=$school->cc_cvv;?>" name="cc_cvv">
		</TD>
	</TR>	


	<TR>
		<TD>
		</TD>
		<TD>
		</TD>
	</TR>	

	
	
	
</TABLE>
