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
$school = new School($row);

$classes = array();
$sql = "SELECT * FROM classes WHERE school_id=" . $school_id;
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query))
{
	$class = new Platoon($row);
	array_push($classes, $class);
}

$sql = "school_id=" . $_GET["school_id"] . " AND chayolei = 1 ";
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
$year = GlobalSettings::getRegistrationYear( $school->school_id );
// 
$reg_info = $school->getRegInfo( $year );
$reg_fee = $reg_info->getChildFee( true );

// if the GET request has a fee set in it use that fee
if ( isset( $_GET['fee'] ) && $admin_user['auth'] == 'super') $reg_fee = $_GET['fee'];
?>
	
<div class="ui_body">
	<div class="content">
		<h2>Soldiers' Registration</h2>
		<div class="infobox">
			All of your Soldiers are displayed below.<br/>
            Select the Soldiers you are registering.						
		</div>		
        <!-- Generate infobox -->
		<div class="infobox2">
			<p>
				<form method="post" action="admin_users_register.php">
					<input type="hidden" name="hidden_school_id" id="hidden_school_id" value="<?=$school_id;?>">
					
					<label style="white-space: nowrap;">First name: 
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
        <input type="hidden" id="cc_first" value="<?=$school->cc_first?>"/> 
        <input type="hidden" id="cc_last" value="<?=$school->cc_last?>"/> 		    
		<input type="hidden" id="cc_number" value="<?=$school->cc_number;?>"/> 
		<input type="hidden" id="cc_exp" value="<?=$school->cc_exp?>"/> 
		<input type="hidden" id="cc_cvv" value="<?=$school->cc_cvv;?>"/>
        <!-- Registration Rate -->
        <input type="hidden" id="reg_fee" value="<?=$reg_fee?>"/> 
		<!-- Authorize.net credentials for user -->
		<input type="hidden" id="authorize_customer_profile_id" value="<?=$school->authorize_customer_profile_id;?>"/>
		<input type="hidden" id="authorize_payment_profile_id" value="<?=$school->authorize_payment_profile_id;?>"/>
		<? // send billing information in the same refresh page
		if($school->authorize_payment_profile_id && $school->authorize_customer_profile_id){
			$paymentProfile = new PaymentProfile($school->authorize_payment_profile_id, $school->authorize_customer_profile_id);
			$billTo = json_encode($paymentProfile->billTo); // pass it to the client as json
		}
		?>
		<input type="hidden" id="authorize_bill_to" value='<?=$billTo;?>'/> <!-- use single quotes to pass json to client side -->
		<input type="hidden" id="authorize_cc_num" value='<?=$paymentProfile->cardNumber;?>'/>
		<input type="hidden" id="authorize_cc_exp" value='<?=$paymentProfile->expirationDate;?>'/>
		
		<table cellspacing="0" style="font-size: 12px;" class="list list_left" 
                id="students_table" name="students_table" cellpadding="0">
			<thead>
				<tr>					
					<th></th>
					<th>			
						Select All
						<br/>
						<label style="white-space: nowrap;"> 
							<input type="checkbox" name="toggle_registration_fee" id="toggle_registration_fee">
                            Registration fee  <?="$" . $reg_fee . ".00";?>
						</label>
						<br/>
					</th>
					<th>Total</th>
					<th>Name</th>
					<th>Platoon</th>
				</tr>
			</thead>
			<tbody>
            <?php 
            foreach ($users as $user) {
				if ( !$user->chayolei )
					continue;
				$registered = $user->registrationStatus($year, false, true)['chayolei'];
                $class = ($row_no % 2 == 0) ? "even" : "odd"; ?>
				<tr name="student_row" id="<?=$registered ? "registered" : "unregistered" ?>" class="<?=$class;?>" data="<?=$user->user_id;?>">
					<td name="user_registered" id="user_registered" width='20%'>
                        <?php
                            if ($registered) {
                                echo 'Registered';
                                $class = "registered";
                            } else {
                                echo 'Not Yet Registered';
                                $class = "unregistered";
                            }
							?>
					</td>
						
					<td width='25%'>
						<div class="checkboxes" id="<?=$user->user_id;?>" name="<?=$user->user_id;?>">
							<?php if ($registered) { ?>
								<input type="hidden" name="registration_fee" id="registration_fee" value='registered'>
                            <?php } else { ?>
								<input type="checkbox" name="registration_fee" id="registration_fee">
								$<?=$reg_fee?>.00 Registration fee 
								<br />
							<?php } ?>
						</div>
					</td>
					<td id="student_total" name="student_total" width='10%'>0.00</td>
					<td>
						<a href="admin_user.php?action=edit&amp;user_id=<?=$user->user_id;?>&school_id=<?=$user->school_id?>&class_id=<?=$user->class_id?>">
							<?=$user->last;?>, <?=$user->first;?>
						</a>
					</td>
					<td><?=$user->platoon ? $user->platoon->name() : "";?></td>
				</tr>
				<?php $row_no++;
            } ?>			
			</tbody>
		</table>
		<table class="list">
			<tr>
				<td>&nbsp;</td>
				<td>Total</td>
				<td>&nbsp;</td>
				<td id="grand_total_id" name="grand_total_id">0.00</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
		</table>
		<br /><br />
		
		<h2>Credit card approval</h2>

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
		
		<h2>Refund Policy</h2>

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
			<h2>Credit Card Authorization Results</h2>
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
			<input type="button" value="Register Above Soldiers" id='register_students' />
			<input type="button" value="Review school settings" name="school_review" id="school_review" />
			<input type="button" value="Review CC settings" name="school_cc_review" id="school_cc_review" />
		</div>
	</div>
		</div>
				
