<?php
$admin_auth = array('school');
require_once( __DIR__ . '/header.php');
require_once( __DIR__ . '/api/header/db.php' );

// load up authorize.net api
require_once(  __DIR__ . "/classes/authorize/PaymentProfile.php");
use \classes\authorize\PaymentProfile;

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
	$class = new Platoon($row);
	array_push($classes, $class);
}

$sql = "school_id=" . $_GET["school_id"] . " ";
if ($class_id > 0)
	$sql = $sql . "AND class_id=" . $class_id . " ";
if ($first != "")
	$sql = $sql . "AND first LIKE '%" . $first . "%' ";
if ($last != "")
	$sql = $sql . "AND last LIKE '%" . $last . "%' ";
if (isset($_GET['registered']))
	$sql = $sql . " AND (user_registered is null or user_registered = 0) ";

$users = User::all( [ 
    'conditions' => [$sql], 'include' => ['platoon'],
    'order' => 'last, first'
]);
$users = is_array( $users ) ? $users : [ $users ];

$row_no = 0;

require_once( __DIR__ . '/class.globalSettings.php' );
$year = GlobalSettings::getRegistrationYear();
$registration_info_query = mysql_query( 
    "SELECT * from school_registrations where school_id = $school_id and year = $year"
);
$reg_info = mysql_fetch_assoc( $registration_info_query );
// check for updated data
if ( $reg_info ) {
    $early_bird = new DateTime( $reg_info['early_bird'] ) > new DateTime();
    $reg_fee = GlobalSettings::calculateChildFee( 
        $reg_info['type'], $reg_info['child_fee'], true, $early_bird, false
    );
// fallback to old system
} else {
    require_once __DIR__ . '/mobile/reg/ajax/regFeeSchools.php';
    $reg_fee = $userFee;
    if ( in_array($school_id, $tuitionSchools ) || in_array( $school_id, $tuitionSchoolsNoPay ) ) $reg_fee = 45;
    if ($reg_fee == 55 && unixtojd() < 2458018 && in_array($school_id,  array_keys($extended))) {
        $reg_fee = $extended[$school_id];
        if ($reg_fee == 0) {
            $reg_fee = 45;
        }
    }
    // myshliach is always 45
    if ($school_id == 61) $reg_fee = 45;
}
// if the GET request has a fee set in it use that fee
if ( isset( $_GET['fee'] ) && $admin_user['auth'] == 'super') $reg_fee = $_GET['fee'];
?>

<DIV class="ui_body">

	<DIV class="content">
	
		<h2>Soldiers' Registration</h2>
		
		<div class="infobox">
			All of your Soldiers are displayed below. Select the Soldiers you are registering and their registration levels.						
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
								<option <?= $class->class_id == $class_id ? 'selected' : ''?> 
                                    value="<?=$class->class_id;?>">
                                    <?=$class->name();?>
                                </option>
							<? endforeach; ?>
						</select>
					</label> 
										
					<input type="button" class="button" name="search_button" id="search_button" value="GO">
				</form>
			</p>
		</div>
		
		<!--Embedding CC information in the page when loaded over ajax.-->
        <INPUT type="hidden" id="cc_first" value="<?=$school->cc_first?>"> 
        <INPUT type="hidden" id="cc_last" value="<?=$school->cc_last?>"> 		    
		<INPUT type="hidden" id="cc_number" value="<?=$school->cc_number;?>"> 
		<INPUT type="hidden" id="cc_exp" value="<?=$school->cc_exp?>"> 
		<INPUT type="hidden" id="cc_cvv" value="<?=$school->cc_cvv;?>">
        <!-- Registration Rate -->
        <INPUT type="hidden" id="reg_fee" value="<?=$reg_fee?>"> 
		<!-- Authorize.net credentials for user -->
		<INPUT type="hidden" id="authorize_customer_profile_id" value="<?=$school->authorize_customer_profile_id;?>">
		<INPUT type="hidden" id="authorize_payment_profile_id" value="<?=$school->authorize_payment_profile_id;?>">
		<? // send billing information in the same refresh page
		if($school->authorize_payment_profile_id && $school->authorize_customer_profile_id){
			$paymentProfile = new PaymentProfile($school->authorize_payment_profile_id, $school->authorize_customer_profile_id);
			$billTo = json_encode($paymentProfile->billTo); // pass it to the client as json
		}
		?>
		<INPUT type="hidden" id="authorize_bill_to" value='<?=$billTo;?>'> <!-- use single quotes to pass json to client side -->
		<INPUT type="hidden" id="authorize_cc_num" value='<?=$paymentProfile->cardNumber;?>'>
		<INPUT type="hidden" id="authorize_cc_exp" value='<?=$paymentProfile->expirationDate;?>'>
		
		<TABLE cellspacing="0" cellpadding="0" style="font-size: 12px;" class="list list_left" id="students_table" name="students_table">

			<THEAD>
			
				<TR>					
					<TH></TH>
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
					<!--
					<TH>
						Registration Fee (Optional, for your records only)
					</TH>
					-->
				</TR>

			</THEAD>

			<TBODY>
					
			
			<? foreach ($users as $user) :?>
				
				<? if ($user->user_registered) $registered = "registered"; else $registered = "unregistered"; ?>
				
				<? if ($row_no % 2 == 0) $class = "even"; else $class = "odd"; ?>
				
				<TR name="student_row" id="<?=$registered;?>" class="<?=$class;?>" data="<?=$user->user_id;?>">
				
					<TD name="user_registered" id="user_registered" width='20%'>
						<? if ($user->user_registered) : ?>
							Registered
							<? $class = "registered"; ?>
						<? else : ?>
							Not Yet Registered
							<? $class = "unregistered"; ?>
						<? endif; ?>
					</TD>
					
					<TD width='25%'>
					
						<div class="checkboxes" id="<?=$user->user_id;?>" name="<?=$user->user_id;?>">
							
							<? if ($user->user_registered) : ?>
								<input type="hidden" name="registration_fee" id="registration_fee" value='registered'>
							<? else : ?>
								<input type="checkbox" name="registration_fee" id="registration_fee">
								<? echo "$" . $reg_fee . ".00"; ?>
								Registration fee 
								<br />
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
						<?=$user->platoon ? $user->platoon->name() : "";?>
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
			<INPUT type="button" value="Register Above Soldiers" id='register_students' />
			<INPUT type="button" value="Review school settings" name="school_review" id="school_review" />
			<INPUT type="button" value="Review CC settings" name="school_cc_review" id="school_cc_review" />
		</div>
				
	</DIV>
	
	
</DIV>	
