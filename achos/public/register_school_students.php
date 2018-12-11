<?php
include ("db.php");
include ("classes/school.php");
include ("classes/user.php");
include ("classes/school_class.php");

if (isset($_GET['school_id'])) 
	$school_id = $_GET["school_id"];

if (isset($_GET["class_id"]))
	$class_id = $_GET["class_id"];
else
	$class_id = 0;

if (isset($_GET["first"]))
	$first = $_GET["first"];
else
	$first = "";

if (isset($_GET["last"]))
	$last = $_GET["last"];
else
	$last = "";

$sql = "SELECT * FROM schools WHERE school_id=" . $school_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$school = new school($row);

$classes = array();
$sql = "SELECT * FROM classes WHERE school_id=" . $school_id;
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query))
{
	$class = new school_class($row);
	array_push($classes, $class);
}


$users = array();

$sql = "SELECT * FROM users WHERE school_id=" . $_GET["school_id"] . " ";
if ($class_id > 0)
	$sql = $sql . "AND class_id=" . $class_id . " ";
if ($first != "")
	$sql = $sql . "AND first LIKE '%" . $first . "%' ";
if ($last != "")
	$sql = $sql . "AND last LIKE '%" . $last . "%' ";
$sql = $sql . " ORDER BY last, first";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query))
{
	$user = new user($row);
	$user->get_school_class();
	array_push($users, $user);
}

$row_no = 0;
?>

<DIV class="ui_body">

	<DIV class="content">
	
		<h2>Soldiers' Registration</h2>
		
		<div class="infobox">
			All of your Soldiers are displayed below. Select the Soldiers you are registering and their registration levels. (For your records: At the end of each line you can input if registration fees were collected and how much.)						
		</div>		
		
		<div class="infobox2">
			<p>
				<form method="post" action="admin_users_register.php">
					<input type="hidden" name="hidden_school_id" id="hidden_school_id" value="<?=$school_id;?>">
					
					<label style="white-space: nowrap;">
						First name: 
						<input type="text" value="<?=$first;?>" name="first" id="first">
					</label> 
					
					<label style="white-space: nowrap;">Last name: 
						<input type="text" value="<?=$last;?>" name="last" id="last">
					</label> 
					
					<label style="white-space: nowrap;">Platoon: 
						<select name="class_id" id="class_id">
							<option value="-1">&lt;All&gt; </option>
							<? foreach ($classes as $class) : ?>
							<? if ($class->class_sub != "") $class_sub = "-" . $class->class_sub; else $class_sub = ""; ?>
							
								<? if ($class->class_id == $class_id) : ?>
								<option selected value="<?=$class->class_id;?>"><?=$class->class_grade . $class_sub;?></option>
								<? else : ?>
								<option value="<?=$class->class_id;?>"><?=$class->class_grade . $class_sub;?></option>
								<? endif; ?>
							
							<? endforeach; ?>
						</select>
					</label> 
										
					<input type="button" class="button" name="search_button" id="search_button" value="GO">
				</form>
			</p>
		</div>
		
		<INPUT type="hidden" id="cc_first" value="<?=$school->cc_first?>"> 
		<INPUT type="hidden" id="cc_number" value="<?=$school->cc_number;?>"> 
		<INPUT type="hidden" id="cc_exp" value="<?=$school->cc_exp?>"> 
		<INPUT type="hidden" id="cc_cvv" value="<?=$school->cc_cvv;?>"> 
									
		<TABLE cellspacing="0" cellpadding="0" style="font-size: 12px;" class="list list_left" id="students_table" name="students_table">

			<THEAD>
			
				<TR>
				
					<TH>
					</TH>
					
					<TH>			
						Package (note that base package is mandatory if options are selected)
						<BR>
						<BR>
						Select All
						<BR>
						<LABEL style="white-space: nowrap;"> 
							<INPUT type="checkbox" name="toggle_registration_fee" id="toggle_registration_fee">Registration fee 
						</LABEL>
						<BR>
						
						<LABEL>
							<INPUT type="checkbox" name="toggle_add_on_one" id="toggle_add_on_one">School store
						</LABEL>
						<BR>
						
						<LABEL>
							<INPUT type="checkbox" name="toggle_add_on_two" id="toggle_add_on_two">770 album
						</LABEL>
						<BR>
					</TH>
						
					<TH>
						Total
					</TH>
					
					<TH>
						Name
					</TH>
					
					<TH>
						Platoon
					</TH>
					
					<TH>
						Registration Fee (Optional, for your records only)
					</TH>
					
				</TR>

			</THEAD>

			<TBODY>
					
			
			<? foreach ($users as $user) :?>
				
				<? if ($user->user_registered > 0) $registered = "registered"; else $registered = "unregistered"; ?>
				
				<? if ($row_no % 2 == 0) $class = "even"; else $class = "odd"; ?>
				
				<TR name="student_row" id="<?=$registered;?>" class="<?=$class;?>" data="<?=$user->user_id;?>">
				
					<TD name="user_registered" id="user_registered" width='20%'>
						<? if ($user->user_registered > 0) : ?>
							Registered
							<?
								//find which add-ons student is registered for
								if ($user->add_on_one == 1) echo "<br />Store";
								if ($user->add_on_two == 1) echo "<br />Album";
							?>
							<? $class = "registered"; ?>
						<? else : ?>
							<? $class = "unregistered"; ?>
						<? endif; ?>
					</TD>
					
					<TD width='25%'>
					
						<div class="checkboxes" id="<?=$user->user_id;?>" name="<?=$user->user_id;?>">
							
							<? if ($user->user_registered > 0) : ?>
							
								<? if ($user->add_on_one == 0 && ($school->add_on_one == 2 || $school->add_on_one == 3 || $school->add_on_one == 4)) : ?>
									<input type="checkbox" name="add_on_one" id="add_on_one">
									$14.00 School store 
									Sweatshirt size:
									<select id="shirt_size" name="shirt_size">
										<option>S</option>
										<option selected >M</option>
										<option>L</option>
										<option>XL</option>
									</select>
									<br />
								<? endif; ?>
								
								<? if ($user->add_on_two == 0 && ($school->add_on_two == 2 || $school->add_on_two == 3 || $school->add_on_two == 4)) : ?>
									<input type="checkbox" name="add_on_two" id="add_on_two">
									$24.00 Album 								
								<? endif; ?>							
								
							<? else : ?>
								<input type="checkbox" name="registration_fee" id="registration_fee">
								$36.00 Registration fee 
								
								<br />
								
								<input type="checkbox" name="add_on_one" id="add_on_one">
								$14.00 School store 
								Sweatshirt size:
								<select id='shirt_size_<?=$row['user_id']?>'>
									<option>S</option>
									<option selected >M</option>
									<option>L</option>
									<option>XL</option>
								</select>
								
								<br />
								
								<input type="checkbox" name="add_on_two" id="add_on_two">
								$24.00 Album 
							<? endif; ?>
							
						</div>
						
					</TD>
					
					<TD id="student_total" name="student_total" width='10%'>
						0.00
					</TD>
					
					<TD>
						<A HREF="admin_user.php?action=edit&amp;user_id=<?=$user->user_id;?>&school_id=<?=$user->school_id?>&class_id=<?=$user->class_id?>">
							<?=$user->last;?>, <?=$user->first;?>
						</A>
					</TD>
					
					<TD>
						<?=$user->school_class->class_grade;?>
						<? if ($user->school_class->class_sub != "") : ?>
							<?="-" . $user->school_class->class_sub;?>
						<? endif; ?>
					</TD>
		
					<TD>
						$<INPUT type="text" value="<?=$user->user_registration_fee;?>" maxlength="7" size="5">
					</TD>
		
				</TR>
				
				<? $row_no++; ?>
				
			<? endforeach; ?>
							
			</TBODY>
			
		</TABLE>
		
		<TABLE class="list">
			<TR>
				<TD>&nbsp;</TD>
				<TD>Total</TD>
				<TD>&nbsp;</TD>
				<TD id="grand_total_id" name="grand_total_id">0.00</TD>
				<TD>&nbsp;</TD>
				<TD>&nbsp;</TD>
			</TR>
		</TABLE>
		
		<br />
		<br />
		
		<h2>
			Credit card approval
		</h2>

		<div class="module" id="module-info">
			<div class="module_content">
				<div class="lists form">
					<ul>
						<li>
							<span class="box">
								<p class="input">
									<input type="checkbox" name="cc_card_check" id="cc_card_check"> 
									<label for="cc_card_check">
										I agree that the above amount will be credited to the credit card entered at the time of school registration
									</label>
								</p>
							</span>
						</li>
					</ul>
				</div>
			</div>
		</div>
		
		<br />
		
		<div id="box_cc_auth" style="display:none;">
			<h2>
				Credit Card Authorization Results
			</h2>
			
			<div class="module" id="module-info">
				<div class="module_content">
					<div class="lists form">
						<ul>
							<li>
								<div id='credit_card_approval_results'></div>
							</li>							
						</ul>						
					</div>
				</div>
			</div>	
		</div>		

		<br />
		
		<div style="text-align:center">
			<INPUT type="button" value="Review school settings" name="school_review" id="school_review" >
			<INPUT type="button" value="Register Above Soldiers" id='register_students' >
		</div>
				
	</DIV>
	
	
</DIV>	
