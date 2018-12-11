<?php
session_start();
if (!isset($_SESSION['admin_id']) || !isset($_POST["message"]))
    header("Location: http://www.mashpia.com/admin.php");

$message = "";
$data = explode("\n", $_POST["message"]);

$authorization_code = $data[2];
$transaction_code = $data[3];
$amount = $data[4];
$message = "Your transaction has been approved for the amount of $" . $amount . "<br />The transaction code is " . $transaction_code;

$user_ids = $_POST["user_ids"];

// ***** GET THE ADMIN AND SCHOOL INFO ***** //
include("db.php");
include("camps/includes/classes/admin.php");
include("camps/includes/classes/user.php");
$admin_id = $_SESSION['admin_id'];
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
$admin->get_children();
$admin->get_sponsors();
// ***** GET THE ADMIN AND SCHOOL INFO ***** //
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" dir="<?=$dir?>">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title>Registration Wizard - Tzivos Hashem Management System</title>
		<link rel="alternate" media="print" href="index.php">
		<link href="admin_styles.css" rel="stylesheet" type="text/css" />
		<script src="camps/scripts/jquery.tools.min.js"></script>
		<script>
			$(function() {
				$("#nav").height($("#content").height());
			});
		</script>
	</head>
	<body>	
		<NOSCRIPT>
			<P STYLE="color: red; font-size: larger;">Notice: You have javascript disabled. Some parts of the site will not function without javascript.</P>
		</NOSCRIPT>
		
		<div id="wrapper">		
			<div id="nav" class="wizard">			
				<div class="col_title_bg"></div>				
				<div class="col_title">Menu</div>
				<? $curr = 5; ?>
				<? include 'register_children_menu.php'; ?>				
			</div>		

			<div id="content">
				<div class="col_title_bg"></div>				
				<div class="slider_container">				
					<div class="slider">					
						<div class="col_title"></div>	

						<div class="col_content">						
							<h1>Summary</h1>	 
							<form action="admin.php" method="post" accept-charset="UTF-8" name="login"> 							
							
								<h1><span style="color:blue"><?=$message;?></span></h1>
							
								<h2>Summary</h2> 		
								
								<div class="module" id="module-info">								
									<div class="module_content">									
									
																

										<div class="lists form">
											<ul>											
												<!-- ***** REGISTERED CHILDREN ***** -->
												<? for ($cno = 0; $cno < count($admin->children); $cno++) : ?>
												<? $child = $admin->children[$cno]; ?>
												<? $child_id = $child->user_id; ?>
												<? $strpos = strpos($user_ids, $child_id) ?>
												
												<? if ($strpos > -1) : ?>
												<li>
													<span class="photo"><img src="<?=($child->user_photo_id=='')?'images/generic_user_small.png':'/file_view.php?id='.$child->user_photo_id?>" width="32" height="32" /></span>
													<span class="label large"><?=$child->first;?> <?=$child->last;?></span>
													<div class="box">
														<div class="role">Grade
															<? 
                                                            if (isset($user->school_class->class_grade)) {
                                                                echo $user->school_class->class_grade;?> - <?=$user->school_class->class_teacher;
                                                            }?></div>
                                                            <div class="info"><?=$child->school_name;?></div>
													</div>
													<span class="label price">Registered</span>
												</li>
												<? endif; ?>
												
												<? endfor; ?>
												<!-- ***** REGISTERED CHILDREN ***** -->												
											</ul>
										</div>
									
									</div>
									
								</div>
								
								<!-- ***** SPONSORED CHILDREN ***** -->
								<? if (count($admin->sponsors)>0):?>
								<div class="module" id="module-info">
								
									<div class="module_content">
									
										<div class="lists form">
											<ul>											
												<? for ($sno = 0; $sno < count($admin->sponsors); $sno++) : ?>
												<? $sponsor = $admin->sponsors[$sno]; ?>
												<? if ($sponsor->is_regular == true) { $fee = 36; } else { $fee = 50; } ?>
												<li>
													<span class="photo"><img src="images/generic_user_small.png" width="32" height="32" /></span>
													<span class="label large"><?=$sponsor["name"];?></span>
													<div class="box">
														<div class="role">&nbsp;</div>
														<div class="info">&nbsp;</div>
													</div>
													<span class="label price">Sponsored $<?=number_format($fee, 2, '.', '');?></span>
												</li>
												<? endfor; ?>
											</ul>
										</div>
									</div>
								</div>
								<? endif; ?>
								<!-- ***** SPONSORED CHILDREN ***** -->												
	
								<h2>Thank You</h2>
								<div class="module" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
												<li>
													<div class="box">
														<h4>Thank you for registering your <?=(count($admin->children)<2)?'child':'children'?> in the Tzivos Hashem 5774 Program.</h4>
													</div>
												</li>
												<li>
													<input type="submit" value="Go Home" class="button"> 
												</li>
											</ul>
										</div>
									</div>
								</div>
							</form> 							
						</div>						
					</div>					
				</div>				
			</div>			
		</div>
	</body>
</html>
