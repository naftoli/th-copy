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
if (isset($_GET['registered']))
	$sql = $sql . " and (user_registered is null or user_registered = 0) ";
$sql .= " and class_id is not null ";
$sql = $sql . " ORDER BY last, first";
//echo $sql;
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query))
{
	$user = new user($row);
	$user->get_school_class();
	array_push($users, $user);
}

$row_no = 0;

$s = "select year from school_add_ons group by year desc limit 1";
$r = mysql_query($s);
$y = mysql_fetch_row($r);
$year = $y[0];
//get id of first add on
$s = "select school_add_on_id from school_add_ons where year = $year limit 1";
$r = mysql_query($s);
$id = mysql_fetch_row($r);
$add_on_start = $id[0];
$num_add_ons = 0;

$add_ons = array();
$sql = "select * from school_add_ons where year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$add_ons[] = $row;
	$num_add_ons++;
}
//get number of last add on
$add_on_end = ($add_on_start + $num_add_ons);

/*
//set registration fees
if (in_array($school_id, array(79, 82, 177)))
    $reg_fee = 12;
else 
    $reg_fee = 50;
 * 
 */
 require_once 'fees.php';
 
$h_school = false;
//if school flagged as hebrew school set fee to 10   
if ( $school_id > 0 ) {
    $sql = "select inst_id from schools where school_id = " . $school_id;
    $result = mysql_query( $sql );
    $row = mysql_fetch_assoc( $result );
    $inst_id = $row['inst_id'];
    if ( $inst_id == 4 ) {
        //$reg_fee = 6;
        $h_school = true;
    }
}
?>

<DIV class="ui_body">

	<DIV class="content">
	
		<h2>Soldiers' Registration</h2>
		
		<div class="infobox">
			All of your Soldiers are displayed below. Select the Soldiers you are registering and their registration levels. (For your records: At the end of each line you can input if registration fees were collected and how much.)						
		</div>		
		
		<div class="infobox2">
			<p>
				<form method="post" action="admin_users_register_new.php">
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
        <INPUT type="hidden" id="cc_last" value="<?=$school->cc_last?>"> 		    
		<INPUT type="hidden" id="cc_number" value="<?=$school->cc_number;?>"> 
		<INPUT type="hidden" id="cc_exp" value="<?=$school->cc_exp?>"> 
		<INPUT type="hidden" id="cc_cvv" value="<?=$school->cc_cvv;?>"> 
									
		<TABLE cellspacing="0" cellpadding="0" style="font-size: 12px;" class="list list_left" id="students_table" name="students_table">

			<THEAD>
			
				<TR>
					
					<script type="text/javascript">
					function popup(link) {
						window.open(link, 'sizes', 'width=200,height=200');
						return false;
					}
					</script>
					
					<TH>
						<? if ( !$h_school ) { ?>
						<a href="suggested_sizes.php" onClick="return popup(this)">View Suggested Sizes</a>
					    <? } ?>
					</TH>
					
					<TH>			
						Select All
						<BR>
						<LABEL style="white-space: nowrap;"> 
							<INPUT type="checkbox" name="toggle_registration_fee" id="toggle_registration_fee">Registration fee 
							<?
							echo "$" . $reg_fee . ".00";
							?>
						</LABEL>
						<BR>
						
						<?
							if ( !$h_school ) {
    							$i = $add_on_start;
    							foreach ($add_ons as $add_on) {
    								echo "<label><input type='checkbox' name='toggle_add_on_" . $i . "' id='toggle_add_on_" . $i . "'>" . 
    									$add_on['title'] . " $" . $add_on['price'] . "</label><br />";
    								$i++;
    							}
    						}
						?>

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
			
				<?
				//get add-ons for user
				if ( !$h_school ) {
    				$user_add_ons = array();
    				$add_ons_qry = "select * from user_add_ons as ua 
    							join school_add_ons as sa on (ua.school_add_on_id = sa.school_add_on_id) 
    							where sa.year = $year and user_id = " . $user->user_id;
    				$res = mysql_query($add_ons_qry);
    				while ($row = mysql_fetch_assoc($res)) {
    					if ($row['needs_size']) 
    						$user_add_ons[$row['school_add_on_id']] = $row['title'] . " (" . strtoupper($row['size']) . ")";
    					else
    						$user_add_ons[$row['school_add_on_id']] = $row['title'];
    				}
                }
				?>
				
				<? if ($user->user_registered > 0) $registered = "registered"; else $registered = "unregistered"; ?>
				
				<? if ($row_no % 2 == 0) $class = "even"; else $class = "odd"; ?>
				
				<TR name="student_row" id="<?=$registered;?>" class="<?=$class;?>" data="<?=$user->user_id;?>">
				
					<TD name="user_registered" id="user_registered" width='20%'>
						<? if ($user->user_registered > 0) : ?>
							Registered
							<?
								//find which add-ons student is registered for
								if ( !$h_school ) {
    								foreach ($user_add_ons as $user_add_on) {
    									echo "<br />" . $user_add_on;
    								}
                                }
							?>
							<? $class = "registered"; ?>
						<? else : ?>
							<? $class = "unregistered"; ?>
						<? endif; ?>
					</TD>
					
					<TD width='25%'>
					
						<div class="checkboxes" id="<?=$user->user_id;?>" name="<?=$user->user_id;?>">
							
							<? if ($user->user_registered > 0) : ?>
							
								<input type="hidden" name="registration_fee" id="registration_fee" value='registered'>
							
								<?
									if ( !$h_school ) {
    									$i = $add_on_start;
    									foreach ($add_ons as $add_on) {
    										if (!array_key_exists($add_on['school_add_on_id'], $user_add_ons)) {
    											echo "<input type='checkbox' name='add_on_" . $i . "' id='add_on_" . $i . "'> ";
    											echo "$" . $add_on['price'] . " " . $add_on['title'];
    											if ($add_on['needs_size'] == 1) {
    												$size = $add_on['title'] . "_size";
    												switch ($add_on['title']) {
    													case 'Sweatshirt':
    														echo " <select id='" . $size . "' name='". $size . "'>
    															<option value='s'>S</option>
    															<option value='m' selected >M</option>
    															<option value='l'>L</option>
    															<option value='xl'>XL</option>
    															</select>";
    														break;
    													case 'Cap':
    														echo " <select id='" . $size . "' name='". $size . "'>
    															<option value='s'>S</option>
    															<option value='l'>L</option>
    															</select>";
    														break;
    													case 'Yarmulka':
    														echo " <select id='" . $size . "' name='". $size . "'>
    															<option value='4'>4</option>
    															<option value='5'>5</option>
    															</select>";
    														break;
    												}											
    											}
    											echo "<br />";
    											$i++;
    										}
    										else {
    											echo "<input type='hidden' name='add_on_" . $i . "' id='add_on_" . $i . "'> ";
    											$i++;
    											//echo "$" . $add_on['price'] . " " . $add_on['title'];
    											//echo "<br />";
    										}
    									}
    								}
								?>
								
							<? else : ?>
								<input type="checkbox" name="registration_fee" id="registration_fee">
								<?
                                echo "$" . $reg_fee . ".00";
								?>
								Registration fee 
								<br />
								
								<?
									if ( !$h_school ) {
    									$i = $add_on_start;
    									foreach ($add_ons as $add_on) {
    										echo "<input type='checkbox' name='add_on_" . $i . "' id='add_on_" . $i . "'> ";
    										echo "$" . $add_on['price'] . " " . $add_on['title'];
    										if ($add_on['needs_size'] == 1) {
    												$size = $add_on['title'] . "_size";
    												switch ($add_on['title']) {
    													case 'Sweatshirt':
    														echo " <select id='" . $size . "' name='". $size . "'>
    															<option value='s'>S</option>
    															<option value='m' selected >M</option>
    															<option value='l'>L</option>
    															<option value='xl'>XL</option>
    															</select>";
    														break;
    													case 'Cap':
    														echo " <select id='" . $size . "' name='". $size . "'>
    															<option value='s'>S</option>
    															<option value='l'>L</option>
    															</select>";
    														break;
    													case 'Yarmulka':
    														echo " <select id='" . $size . "' name='". $size . "'>
    															<option value='4'>4</option>
    															<option value='5'>5</option>
    															</select>";
    														break;
    												}
    												
    											}
    										echo "<br />";
    										$i++;
    									}
    								}
								?>
								
								<!--
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
								-->
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
						$<INPUT type="text" name="optional_fee" id="optional_fee" value="<?=$user->user_registration_fee;?>" maxlength="7" size="5">
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
		
		<h2>
			Refund Policy
		</h2>

		<div class="module" id="module-info">
			<div class="module_content">
				<div class="lists form">
					<ul>
						<li>
							<span class="box">
								<p class='input'>
								Registration:
								We will not refund any legitimate registration even if the program was not used on your end.
								<br />
								Processing errors:
								For any overcharge of registration due to technical errors we will fully refund. 
								Credit card transactions will be credited to the original card used. 
								This process may take up to two weeks.
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
			<INPUT type="button" value="Register Above Soldiers" id='register_students' >
			<INPUT type="button" value="Review school settings" name="school_review" id="school_review" >
		</div>
				
	</DIV>
	
	
</DIV>	
