<?
//$admin_auth = array('school'); 
require('../db.php');

$school = array();
$reg = array();
$year = 5776;
if (isset($_GET['id']) && !isset($_GET['edit'])) {
	$action = "add";
	$sql = "select * from chidon_schools where year = $year and chidon_schools_id = " . mysql_real_escape_string($_GET['id']);
	$result = mysql_query($sql);
	$school = mysql_fetch_assoc($result);
	
	$sql = "select * from chidon_reg where chidon_schools_id = " . mysql_real_escape_string($_GET['id']);
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$reg[] = $row;
	}
} else if (isset($_GET['id']) && isset($_GET['edit'])) {
	$action = "edit";
	$sql = "select * from chidon_reg where chidon_reg_id = " . mysql_real_escape_string($_GET['id']);
	$result = mysql_query($sql);
	$regCurrent = mysql_fetch_assoc($result);
	
	$sql = "select * from chidon_schools where year = $year and chidon_schools_id = " . 
		mysql_real_escape_string($regCurrent['chidon_schools_id']);
	$result = mysql_query($sql);
	$school = mysql_fetch_assoc($result);
	
	$sql = "select * from chidon_reg where chidon_schools_id = " . mysql_real_escape_string($regCurrent['chidon_schools_id']);
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$reg[] = $row;
	}
}

$sql = "select chidon_schools_id, school_name from chidon_schools 
		where year = $year 
		and gender = '$gender' 
		order by school_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$schools[$row['chidon_schools_id']] = $row['school_name'];
}

if (isset($regCurrent['chidon_schools_id'])) {
	$schoolID = $regCurrent['chidon_schools_id'];
} else if (isset($_GET['id'])) {
	$schoolID = $_GET['id'];
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
<title>Chidon</title>
<link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="js/jquery.min.js"></script>

<!-- Custom Theme files -->
<!--theme-style-->
<link href="css/style.css" rel="stylesheet" type="text/css" media="all" />	
<!--//theme-style-->
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1255" />
<meta name="keywords" content="" />
<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
<!--fonts-->

<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Sanchez" />
<!--//fonts-->
<script type="text/javascript" src="js/move-top.js"></script>
<script type="text/javascript" src="js/easing.js"></script>
				<script type="text/javascript">
					jQuery(document).ready(function($) {
						$(".scroll").click(function(event){		
							event.preventDefault();
							$('html,body').animate({scrollTop:$(this.hash).offset().top},1000);
						});
					});
				</script>
<link href="css/nav.css" rel="stylesheet" type="text/css" media="all"/>
<style type="text/css">
body,td,th {
	font-family: Sanchez, "Sanchez Slab";
}
</style>
<script src="js/easyResponsiveTabs.js" type="text/javascript"></script>
	    <script type="text/javascript">
		    $(document).ready(function () {
		        $('#horizontalTab,#horizontalTab1,#horizontalTab2').easyResponsiveTabs({
		            type: 'default', //Types: default, vertical, accordion           
		            width: 'auto', //auto or any width like 600px
		            fit: true   // 100% fit in a container
		        });
		    });
		   </script>
	
	
	<script src="js/main.js"></script> <!-- Resource jQuery -->
	
	<style type='text/css'>
	    .my table {
	        font-size: 12px;
	    }
	    .my th, td {
	    	padding: 3px;
	    	text-align: left;
	    }
	    .my th {
	    	vertical-align: top;
	    	width: 75px;
	    }
	    .my th:first-child {
	    	width: 100px;
	    }
	    .my td:first-child {
	    	text-align: left;
	    }
	    .my .school table {
	    	font-size: 14px;
	    }
	    .my .school td {
	    	text-align: left;
	    }
	    .my .school input[type='text'] {
	    	width: 200px;
	    }
	    .my fieldset {
			margin-bottom: 20px;
			-moz-border-radius: 20px;
	        -webkit-border-radius: 20px;
	        border-radius: 20px;
	        width: auto;
		}
		.my fieldset#cc, fieldset#terms {
			width: 400px;
		}
		.my #form {
			float: left;
		}
		.my #regList {
			width: auto;
			float: left;
		} 
		.my #regList fieldset {
			clear: right;
		}
		.my #regList th {
			width: 100px;
		}
		.my legend {
	        padding: 6px;
	        font-size: 16px;
	        font-family: 'Sanchez';
		}
		.my #cc table {
			font-size: 14px;
		}
		.my #cc td {
			text-align: left;
			min-width: 150px;
		}
		.my input[type='text'] {
			width: 150px;
		}
		.my p {
			font-size: 14px;
		}
		.my #payment {
			text-align: right;
			color: red;
		}
		.my #photo {
			float: right;
		}
		.my #form {
			width: 420px;
		}
		.my small {
			font-size: 10px;
			font-style: italic;
		}
		.my .ctr {
			text-align: center;
		}
		.my .green {
			color: green;
		}
		.my .red {
			color: red;
		}
		.my .avg {
			font-weight: bold;
		}
		
		.my .img-people {
		    z-index: 5;
		    width: 160px;
		    height: 160px;
		    border-radius: 50%;
		    border: 2px solid;
		    border-color: rgb(177, 177, 177);
		    margin-left: auto;
		    margin-right: auto;
		    display: block;
		    margin-bottom: 10px;
		}
	</style>
    
    <link rel="stylesheet" href="cropper/dist/cropper.css">	
	<link rel="stylesheet" href="cropper/avatar/css/main.css">
	<script src="js/bootstrap.js"></script>
	<script src="cropper/dist/cropper.js"></script>
	<script src="cropper/avatar/js/main.js"></script>
	
</head>
<body>
<!--header-->

<div class="container">

<div class="main-top">
	<div class="main">
		<div class="header">
			<div class="header-top">
				<div class="header-in">
					<div class="logo"> <img src="images/topheader.png" alt="" >
					</div>
					
				  <div class="clearfix"> </div>
				</div>
				
				<div class="clearfix"> </div>
			</div>
			<!---->
			
	</div>
		
	<?php require 'menu.php' ?>
	
<div class="content">

	<div class="col-md-9 content-top">
		<div class="number">
				
    		
    		<div class="row_8">
   		       
			  
               	
               
		<div class='my'>
			<div style='float: right;'>
				<img src="../images/Chidon-Logo.jpg" />
			</div>
			
	        <h1>Chidon Registration Form</h1>
	        
	        <div><br /><b>PARENTS PLEASE CLICK <a href="http://mashpia.com/mobile/chidon">HERE</a></b></div>
	        
	        <? //if (!isset($_GET['debug'])) : ?>
	        <? if (false) : ?>
	        <p><?=ucfirst($gender)?>’s Chidon is now closed for registration.</p>
	        <? else : ?>
	        <?
	        if (isset($_GET['error'])) {
	        	echo "<br /><div style='color:red'>" . urldecode($_GET['error']) . "</div>";
	        } else if (isset($_GET['success'])) {
	        	if (isset($_GET['paid'])) {
	        		echo "<br /><div style='color:red'>Thank you for your payment. You will receive an email confirmation shortly.</div>";
	        	} else {
	        		echo "<br /><div style='color:red'>You have successfully added a participant.</div>";
				}
	        }
	        ?>
	        
	        <?=$text?>
	        
	        <? //if ($gender == 'girls') exit; ?>
	        
	        <div>
	        	<br />
	        	You must enter your username and choose your school name.
	        	Then click on "Load Saved Info".
	        	<br /><br />
	        </div>
	        
        	<div class='school'>
        		<table>
        			<tr>
        				<td>Username</td>
        				<td><input type="text" name="username" size="40" id="username" 
        					<? if (!empty($school)) echo "value='" . $school['username'] . "'" ?>
        					/></td>
        			</tr>
        			<tr>
        				<td>School Name</td>
        				<td>
        					<select name="school" id="school">
        						<?
        						foreach ($schools as $sid => $name) {
        							echo "<option value='$sid' ";
									if (!empty($school) && $school['school_name'] == $name) {
										echo "selected='selected'";
									}
									echo ">" . $name . "</option>";
        						}
        						?>
        					</select>
        				</td>
        			</tr>
        			<? if (isset($_GET['id'])) { ?>
        			<tr>
        				<td>Chaperone Name</td>
        				<td><input type='text' name='ch_name' size='40' class="chaperone" 
        					<? if (!empty($school)) echo "value='" . $school['chaperone_name'] . "'" ?>
        					/></td>
        			</tr>
        			<tr>
        				<td>Chaperone Cell</td>
        				<td><input type='text' name='ch_number' size='40' class="chaperonePhone" 
        					<? if (!empty($school)) echo "value='" . $school['chaperone_phone'] . "'" ?>
        					/></td>
        			</tr>
        			<tr>
        				<td>Chaperone Name</td>
        				<td><input type='text' name='ch_name2' size='40' class="chaperone" 
        					<? if (!empty($school)) echo "value='" . $school['chaperone_name2'] . "'" ?>
        					/></td>
        			</tr>
        			<tr>
        				<td>Chaperone Cell</td>
        				<td><input type='text' name='ch_number2' size='40' class="chaperonePhone" 
        					<? if (!empty($school)) echo "value='" . $school['chaperone_phone2'] . "'" ?>
        					/></td>
        			</tr>
        			<tr>
        				<td>Chaperone Name</td>
        				<td><input type='text' name='ch_name3' size='40' class="chaperone"  
        					<? if (!empty($school)) echo "value='" . $school['chaperone_name3'] . "'" ?>
        					/></td>
        			</tr>
        			<tr>
        				<td>Chaperone Cell</td>
        				<td><input type='text' name='ch_number3' size='40' class="chaperonePhone" 
        					<? if (!empty($school)) echo "value='" . $school['chaperone_phone3'] . "'" ?>
        					/></td>
        			</tr>
        			<tr>
        				<td>Chaperone Name</td>
        				<td><input type='text' name='ch_name4' size='40' class="chaperone"  
        					<? if (!empty($school)) echo "value='" . $school['chaperone_name4'] . "'" ?>
        					/></td>
        			</tr>
        			<tr>
        				<td>Chaperone Cell</td>
        				<td><input type='text' name='ch_number4' size='40' class="chaperonePhone" 
        					<? if (!empty($school)) echo "value='" . $school['chaperone_phone4'] . "'" ?>
        					/></td>
        			</tr>
        			<tr>
        				<td></td>
        				<td><input type="button" id="update" value="Update" /></td>
        			</tr>
        			<!--
        			<tr>
        				<td colspan="2">To participate in the full program as a chaperone and purchase a Chidon Sweater please click <a href="#chap">here</a></td>
        			</tr>
        			-->
        			<? } ?>
        			<tr>
        				<td colspan="2">
        					<? if (!isset($_GET['id'])) { ?>
        					<button id="loadSaved">Load Saved Info</button>
        					<? } else { echo "&nbsp;"; } ?>
        				</td>
        			</tr>
        		</table>
			</div>
			<? endif; ?>
			
			<? if (isset($_GET['id'])) { ?>	
				
				<? if ($action == 'add') { ?>
				
					<fieldset>
						<legend><b>Add Participants in Bulk</b></legend>
						<? 
						if (isset($_GET['uerror'])) {
							echo "<div style='color:red'>" . urldecode($_GET['uerror']) . "</div>";
						}
						?>
						<div>
							1. Click <a href="ChidonReg.xlsx" style="color: blue">here</a> to download Excel Spreadsheet Template.<br />
							2. Fill in Spreadsheet.<br />
							3. Upload your spreadsheet.<br />
							<form action="chidon_reg_upload.php" method="post" enctype="multipart/form-data">
								<input type="hidden" name="school" value="<?=$_GET['id']?>" />
								<input type="hidden" name="gender" value="<?=$gender?>" />
								<input type="file" name="file" />
								<input type="submit" name="submit" value="Upload" />
							</form>
						</div>
					</fieldset>
					
					<p>OR</p>
					
					<div id="crop-avatar">
						<!-- Cropping modal -->
						
					    <div class="modal fade" id="avatar-modal" aria-hidden="true" aria-labelledby="avatar-modal-label" role="dialog" tabindex="-1">
					      <div class="modal-dialog modal-lg">
					        <div class="modal-content">
					          <form class="avatar-form" action="crop.php" enctype="multipart/form-data" method="post">
					            <div class="modal-header">
					              <button type="button" class="close" data-dismiss="modal">&times;</button>
					              <h4 class="modal-title" id="avatar-modal-label">Change Avatar</h4>
					            </div>
					            <div class="modal-body">
					              <div class="avatar-body">
					
					                <!-- Upload image and data -->
					                
					                <div class="avatar-upload">
					                  <input type="hidden" class="avatar-src" name="avatar_src">
					                  <input type="hidden" class="avatar-data" name="avatar_data">
					                  <label for="avatarInput">Local upload</label>
					                  <input type="file" class="avatar-input" id="avatarInput" name="avatar_file">
					                </div>
					
									<div class="row avatar-btns">
							<div class="col-md-9">
					        <div class="btn-group">
					          <button type="button" class="btn btn-primary" data-method="zoom" data-option="0.1" title="Zoom In">
					            <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;zoom&quot;, 0.1)">
					              <span class="fa fa-search-plus"></span>
					            </span>
					          </button>
					          <button type="button" class="btn btn-primary" data-method="zoom" data-option="-0.1" title="Zoom Out">
					            <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;zoom&quot;, -0.1)">
					              <span class="fa fa-search-minus"></span>
					            </span>
					          </button>
					        </div>
					
					                    <div class="btn-group">
					          <button type="button" class="btn btn-primary" data-method="rotate" data-option="-15" title="Rotate Left">
					            <span class="docs-tooltip" data-toggle="tooltip" title="">
					              <span class="fa fa-rotate-left"></span>
					            </span>
					          </button>
					          <button type="button" class="btn btn-primary" data-method="rotate" data-option="15" title="Rotate Right">
					            <span class="docs-tooltip" data-toggle="tooltip" title="">
					              <span class="fa fa-rotate-right"></span>
					            </span>
					          </button>
					                    </div>
					        <div class="btn-group">
					          <button type="button" class="btn btn-primary" data-method="reset" title="Reset">
					            <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;reset&quot;)">
					              <span class="fa fa-refresh"></span>
					            </span>
					          </button>
					                    </div>
					                  </div>
					</div>
					                <!-- Crop and preview -->
					                
					                <div class="row">
					                  <div class="col-md-9">
					                    <div class="avatar-wrapper"></div>
					                  </div>
					                <!--  <div class="col-md-3">
					                    <div class="avatar-preview preview-lg"></div>
					                    <div class="avatar-preview preview-md"></div>
					                    <div class="avatar-preview preview-sm"></div>
					                  </div> -->
					                
					                </div>
					
					                <div class="row avatar-btns">
					                  <div class="col-md-3">
					                    <button type="submit" class="btn btn-primary btn-block avatar-save">Done</button>
					                  </div>
					                </div>
					              </div>
					            </div>
					            <!-- <div class="modal-footer">
					              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					            </div> -->
					            
					          </form>
					        </div>
					      </div>
					    </div><!-- /.modal -->
					   
					<form action="register_post.php" method="post" id="regForm" enctype="multipart/form-data" onsubmit="addFilePic(this)">
						<input type='hidden' name='schoolID' value='<?=$_GET['id']?>' />
						<input type="hidden" name="filePic" value="" />
						<input type="hidden" name="action" value="add" />
						<div id="form">
			        		<input type='hidden' name='gender' value='<?=$gender?>' />			
							<fieldset>
								<legend><b>Add Single Participant</b></legend>				
								<table>
									<tr>
										<td>Grade</td>
										<td>
											<select name="grade" id="grade">
												<? for ($grade = 4; $grade < 9; $grade++) { ?>
													<option value="<?=$grade?>"><?=$grade?></option>
												<? } ?>
											</select>
										</td>
									</tr>
									<tr>
										<td>Type</td>
										<td>
											<select name="type" id="type">
												<option value='winner'>Winner</option>
												<option value='runnerUp'>Runner up</option>
												<option value='parent'>Parent</option>
											</select>
										</td>
									</tr>
									<tr>
										<td>Sweater Size</td>
										<td>
											<select name="size" id="size">
												<option value="0">Please Choose One</option>
												<option value="children xs">Children XS</option>
												<option value="children s">Children S</option>
												<option value="children m">Children M</option>
												<option value="children l">Children L</option>
												<option value="children xl">Children XL</option>
												<option value="adult s">Adult S</option>
												<option value="adult m">Adult M</option>
												<option value="adult l">Adult L</option>
												<option value="adult xl">Adult XL</option>
											</select>
										</td>
									</tr>
									<tr>
										<td>First Name</td>
										<td><input type='text' name='name' id="name" /></td>
									</tr>
									<tr>
										<td>Last Name</td>
										<td><input type='text' name='lname' id="lname" /></td>
									</tr>
									<tr>
										<td colspan="2">
											The Hebrew Name is needed for Plaque and Certificate.
										</td>
									</tr>
									<tr>
										<td></td>
										<td>
											HEBREW LETTERS ONLY.
										</td>
									</tr>
									<tr>
										<td>Hebrew First Name</td>
										<td>
											<input type='text' name='hname' id="hname" /><br />
										</td>
									</tr>
									<tr>
										<td>Hebrew Last Name</td>
										<td>
											<input type='text' name='hname_last' id="hname_last" />
										</td>
									</tr>
									<tr>
										<td>Book Number</td>
										<td>
											<select name="book" id="book" disabled="disabled">
												<option value='1'>1</option>
												<option value='2'>2</option>
												<option value='3'>3</option>
												<option value='4'>4</option>
											</select>
										</td>
									</tr>
									<tr>
										<td colspan="2">
											Please enter the student's marks for Test 1, Replacement Questions, and Test 2.
										</td>
									</tr>
									<tr>
										<td>Test 1</td>
										<td><input type='text' name='mark1' id="mark1" size='4' /></td>
									</tr>
									<tr>
										<td>Replacement Questions</td>
										<td><input type='text' name='bonus' id="bonus" size='4' /></td>
									</tr>
									<tr>
										<td>Test 2</td>
										<td><input type='text' name='mark2' id="mark2" size='4' /></td>
									</tr>
									<tr> 
										<td>Test 3</td>
										<td><input type="text" name="mark3" id="mark3" size="4" /></td>
									</tr>
									<tr>
										<td><span class="avg">Average Mark</span></td>
										<td id="avg"><span class="avg"></span></td>
									</tr>
									<tr>
										<td>Couvert Fee</td>
										<td>$115</td>
									</tr>
									<tr>
										<td>Parent Name</td>
										<td><input type='text' name='parentName' id='parentName' /></td>
									</tr>
									<tr>
										<td>Parent Email</td>
										<td><input type='text' name='parentEmail' id='parentEmail' /></td>
									</tr>
									<tr>
										<td>Father Cell</td>
										<td><input type='text' name='parentCell' id='parentCell' /></td>
									</tr>
									<tr>
										<td>Mother Cell</td>
										<td><input type='text' name='motherCell' id='motherCell' /></td>
									</tr>
									<tr>
										<td colspan="2">We will not be providing airport runs this year.
											If you would like help with arranging airport transportation please 
											contact us and we will do our best to find you the cheapest rate.</td>
									</tr>
									<tr>
										<td>Arrival Airport</td>
										<td><input type='text' name='arrAirport' /></td>
									</tr>
									<tr>
										<td>Airline / Flight Number</td>
										<td><input type='text' name='arrNumber' /></td>
									</tr>
									<tr>
										<td>Arrival Time</td>
										<td><input type='text' name='arrTime' /></td>
									</tr>
									<tr>
										<td>Departure Airport</td>
										<td><input type='text' name='depAirport' /></td>
									</tr>
									<tr>
										<td>Airline / Flight Number</td>
										<td><input type='text' name='depNumber' /></td>
									</tr>
									<tr>
										<td>Departure Time</td>
										<td><input type='text' name='depTime' /></td>									
									</tr>
									<tr>
										<td>Needs CH Accomodation</td>
										<td>
											<select name='help' id='help'>
												<option value='n'>no</option>
												<option value='y'>yes</option>
											</select>
										</td>
									</tr>
									<tr class="accomodation">
										<td>CH Family Name:</td>
										<td><input type='text' name='family' /></td>
									</tr>
									<tr class="accomodation">
										<td>CH Address</td>
										<td><input type='text' name='address' /></td>
									</tr>
									<tr class="accomodation">
										<td>CH Phone</td>
										<td><input type='text' name='phone' /></td>
									</tr>
									
									<tr>
										
										<td>Upload Photo</td>
										<td><input type="file" name="photo" /></td>
										
										
										<div class="image-upload" id="crop-avatar-child">
											<div class="avatar-view" title="Add Photo">
												<img src="/mobile/reg/images/addphoto.png" class="img-people" id="imgchild" />
											</div>
				                        </div>
									</tr>
									
									<tr>
										<td width="150">Child is allowed to walk alone during day time.</td>
										<td>
											<select name="walk">
												<option value='0'>No</option>
												<option value='1'>Yes</option>
											</select>
										</td>
									</tr>
									
									<tr>
										<td>If Yes, enter cross streets.</td>
										<td><input type="text" name="streets" /></td>
									</tr>
									
									<tr>
										<td>Allergies</td>
										<td><textarea rows="3" cols="19" name="allergy"></textarea></td>
									</tr>
									
									<tr>
										<td>Shoe Size</td>
										<td><input type="text" name="shoeSize" /></td>
									</tr>
									
									<tr>
										<td>Notes</td>
										<td>
											<textarea rows="5" cols="19" name="notes"></textarea>
										</td>
									</tr>
									<tr>
										<td></td>
										<td id="addRow">
											<input type="submit" name="submit" value="Add Participant" id="add" />
										</td>
									</tr>
								</table>
							</fieldset>
						</div>
					</form>
					</div>
					-->
				<? } else if ($action == 'edit') { ?> 
					<div id="crop-avatar">
						<!-- Cropping modal -->
					    <div class="modal fade" id="avatar-modal" aria-hidden="true" aria-labelledby="avatar-modal-label" role="dialog" tabindex="-1">
					      <div class="modal-dialog modal-lg">
					        <div class="modal-content">
					          <form class="avatar-form" action="crop.php" enctype="multipart/form-data" method="post">
					            <div class="modal-header">
					              <button type="button" class="close" data-dismiss="modal">&times;</button>
					              <h4 class="modal-title" id="avatar-modal-label">Change Avatar</h4>
					            </div>
					            <div class="modal-body">
					              <div class="avatar-body">
					
					                <!-- Upload image and data -->
					                <div class="avatar-upload">
					                  <input type="hidden" class="avatar-src" name="avatar_src">
					                  <input type="hidden" class="avatar-data" name="avatar_data">
					                  <label for="avatarInput">Local upload</label>
					                  <input type="file" class="avatar-input" id="avatarInput" name="avatar_file">
					                </div>
					
									<div class="row avatar-btns">
							<div class="col-md-9">
					        <div class="btn-group">
					          <button type="button" class="btn btn-primary" data-method="zoom" data-option="0.1" title="Zoom In">
					            <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;zoom&quot;, 0.1)">
					              <span class="fa fa-search-plus"></span>
					            </span>
					          </button>
					          <button type="button" class="btn btn-primary" data-method="zoom" data-option="-0.1" title="Zoom Out">
					            <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;zoom&quot;, -0.1)">
					              <span class="fa fa-search-minus"></span>
					            </span>
					          </button>
					        </div>
					
					                    <div class="btn-group">
					          <button type="button" class="btn btn-primary" data-method="rotate" data-option="-15" title="Rotate Left">
					            <span class="docs-tooltip" data-toggle="tooltip" title="">
					              <span class="fa fa-rotate-left"></span>
					            </span>
					          </button>
					          <button type="button" class="btn btn-primary" data-method="rotate" data-option="15" title="Rotate Right">
					            <span class="docs-tooltip" data-toggle="tooltip" title="">
					              <span class="fa fa-rotate-right"></span>
					            </span>
					          </button>
					                    </div>
					        <div class="btn-group">
					          <button type="button" class="btn btn-primary" data-method="reset" title="Reset">
					            <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;reset&quot;)">
					              <span class="fa fa-refresh"></span>
					            </span>
					          </button>
					                    </div>
					                  </div>
					</div>
					                <!-- Crop and preview -->
					                <div class="row">
					                  <div class="col-md-9">
					                    <div class="avatar-wrapper"></div>
					                  </div>
					                <!--  <div class="col-md-3">
					                    <div class="avatar-preview preview-lg"></div>
					                    <div class="avatar-preview preview-md"></div>
					                    <div class="avatar-preview preview-sm"></div>
					                  </div> -->
					                </div>
					
					                <div class="row avatar-btns">
					                  <div class="col-md-3">
					                    <button type="submit" class="btn btn-primary btn-block avatar-save">Done</button>
					                  </div>
					                </div>
					              </div>
					            </div>
					            <!-- <div class="modal-footer">
					              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					            </div> -->
					          </form>
					        </div>
					      </div>
					    </div><!-- /.modal -->
					    
					<form action="register_post.php" method="post" id="regForm" enctype="multipart/form-data" onsubmit="return addFilePic(this);">
						<input type='hidden' name="schoolID" value="<?=$school['chidon_schools_id']?>" />
						<input type='hidden' name='regID' value='<?=$_GET['id']?>' />
						<input type="hidden" name="gender" value="<?=$school['gender']?>" />
						<input type="hidden" name="filePic" value="" />
						<input type="hidden" name="action" value="edit" />
						<div id="form">
							<fieldset>
								<legend>Edit Participant</legend>				
								<table>
									<tr>
										<td id="payment">
											<?
											if ($regCurrent['paid']) echo "<i>Paid For</i>";
											else echo "<i>Not Yet Paid For</i>";
											?>
										</td>
									</tr>
									<tr>
										<td>Grade</td>
										<td>
											<select name="grade" id="grade">
												<? 
												for ($grade = 4; $grade < 9; $grade++) {
													echo "<option value='" . $grade . "'";
													if ($regCurrent['grade'] == $grade) echo " selected='selected'";
													echo ">" . $grade . "</option>";
												}
												?> 
											</select>
										</td>
									</tr>
									<div id='photo'>
										<? 
										if (!empty($regCurrent['file'])) {
											if (strpos($regCurrent['file'], 'img/') !== false) {
												if (file_exists($regCurrent['file'])) {
													echo "<img width='80' src='" . $regCurrent['file'] . "' />";
												} else if (file_exists('../mobile/chidon' . $regCurrent['file'])) {
													echo "<img width='80' src='../mobile/chidon/" . $regCurrent['file'] . "' />";
												} 
											} else {
												if (strpos($regCurrent['file'], '/chidon/') !== false)
													echo "<img width='80' src='" . $regCurrent['file'] . "' />";
												else 
													echo "<img width='80' src='photos/" . $regCurrent['file'] . "' />";
											}
										}
										?>
									</div>
									<tr>
										<td>Type</td>
										<td>
											<select name="type" id="type">
												<option value='winner'
												<? if ($regCurrent['type'] == 'winner') echo " selected='selected'"; ?>
												>Winner</option>
												<option value='runnerUp'
												<? if ($regCurrent['type'] == 'runnerUp') echo " selected='selected'"; ?>
												>Runner up</option>
												<option value='parent'
												<? if ($regCurrent['type'] == 'parent') echo " selected='selected'"; ?>
												>Parent</option>
											</select>
										</td>
									</tr>
									<? $size = strtolower($regCurrent['size']); ?>
									<tr>
										<td>Sweater Size</td>
										<td>
											<select name="size" id="size">
												<option value="0">Choose Size</option>
												<option value="children xs" 
												<? if ($size == 'children xs') echo "selected='selected'"; ?>
												>Children XS</option>
												<option value="children s" 
												<? if ($size == 'children s') echo "selected='selected'"; ?>
												>Children S</option>
												<option value="children m" 
												<? if ($size == 'children m') echo "selected='selected'"; ?>
												>Children M</option>
												<option value="children l" 
												<? if ($size == 'children l') echo "selected='selected'"; ?>
												>Children L</option>
												<option value="children xl" 
												<? if ($size == 'children xl') echo "selected='selected'"; ?>
												>Children XL</option>
												<option value="adult s" 
												<? if ($size == 'adult s') echo "selected='selected'"; ?>
												>Adult S</option>
												<option value="adult m" 
												<? if ($size == 'adult m') echo "selected='selected'"; ?>
												>Adult M</option>
												<option value="adult l" 
												<? if ($size == 'adult l') echo "selected='selected'"; ?>
												>Adult L</option>
												<option value="adult xl" 
												<? if ($size == 'children xl') echo "selected='selected'"; ?>
												>Adult XL</option>
											</select>
										</td>
									</tr>
									<tr>
										<td>First Name</td>
										<td><input type='text' name='name' id="name" value="<?=$regCurrent['name']?>" /></td>
									</tr>
									<tr>
										<td>Last Name</td>
										<td><input type='text' name='lname' id="lname" value="<?=$regCurrent['last_name']?>" /></td>
									</tr>
									<tr>
										<td colspan="2">
											The Hebrew Name is needed for Plaque and Certificate.
										</td>
									</tr>
									<tr>
										<td></td>
										<td>
											HEBREW LETTERS ONLY.
										</td>
									</tr>
									<tr>
										<td>Hebrew First Name</td>
										<td>
											<input type='text' name='hname' id="hname" value="<?=$regCurrent['hfname']?>" /><br />
										</td>
									</tr>
									<tr>
										<td>Hebrew Last Name</td>
										<td>
											<input type='text' name='hname_last' id="hname_last" value="<?=$regCurrent['hlname']?>"	/>
										</td>
									</tr>
									<tr>
										<td>Book Number</td>
										<td>
											<select name="book" id="book" disabled="disabled">
												<option value='1' 
												<? if ($regCurrent['grade'] == 4) echo "selected='selected'"?>>1</option>
												<option value='2' 
												<? if ($regCurrent['grade'] == 5) echo "selected='selected'"?>>2</option>
												<option value='3' 
												<? if ($regCurrent['grade'] == 6) echo "selected='selected'"?>>3</option>
												<option value='4' 
												<? if ($regCurrent['grade'] == 7 || $regCurrent['grade'] == 8) echo "selected='selected'"?>
												>4</option>
											</select>
										</td>
									</tr>
									<tr>
										<td>Test 1</td>
										<td><input type='text' name='mark1' id="mark1" size='4' value="<?=$regCurrent['mark1']?>" /></td>
									</tr>
									<tr>
										<td>Replacement Questions</td>
										<td><input type='text' name='bonus' id="bonus" size='4' value="<?=$regCurrent['bonus']?>" /></td>
									</tr>
									<tr>
										<td>Test 2</td>
										<td><input type='text' name='mark2' id="mark2" size='4' value="<?=$regCurrent['mark2']?>" /></td>
									</tr>
									<tr>
										<td>Test 3</td>
										<td><input type="text" name="mark3" id="mark3" size="4" value="<?=$regCurrent['mark3']?>" /></td>
									</tr> 
									<tr>
										<td><span class="avg">Average Mark</span></td>
										<td id="avg">
											<span class="avg">
											<?
											$marks = 0;
											$mark1 = $regCurrent['mark1'];
											$mark2 = $regCurrent['bonus'];
											$mark3 = $regCurrent['mark2'];
											$mark4 = $regCurrent['mark3'];
											if ($mark1) $marks++;
											else $mark1 = 0;
											if (!$mark2) $mark2 = 0;
											if ($mark3) $marks++;
											else $mark3 = 0;
											if ($mark4) $marks++;
											else $mark4 = 0;
											echo round(($mark1 + $mark2 + $mark3 + $mark4) / $marks);
											?>
											</span>
										</td>
									</tr>
									<tr>
										<td>Couvert Fee</td>
										<td>$115</td>
									</tr>
									<tr>
										<td>Parent Name</td>
										<td><input type='text' name='parentName' id='parentName' value="<?=$regCurrent['parent_name']?>" /></td>
									</tr>
									<tr>
										<td>Parent Email</td>
										<td><input type='text' name='parentEmail' id='parentEmail' value="<?=$regCurrent['parent_email']?>" /></td>
									</tr>
									<tr>
										<td>Father Cell</td>
										<td><input type='text' name='parentCell' id='parentCell' value="<?=$regCurrent['parent_cell']?>" /></td>
									</tr>
									<tr>
										<td>Mother Cell</td>
										<td><input type='text' name='motherCell' id='motherCell' value="<?=$regCurrent['parent_cell2']?>" /></td>
									</tr>
									<tr>
										<td colspan="2">We will not be providing airport runs this year.
											If you would like help with arranging airport transportation please 
											contact us and we will do our best to find you the cheapest rate.</td>
									</tr>
									<tr>
										<td>Arrival Airport</td>
										<td><input type='text' name='arrAirport' value="<?=$regCurrent['arr_airport']?>" /></td>
									</tr>
									<tr>
										<td>Airline / Flight Number</td>
										<td><input type='text' name='arrNumber' value="<?=$regCurrent['arr_number']?>" /></td>
									</tr>
									<tr>
										<td>Arrival Time</td>
										<td><input type='text' name='arrTime' value="<?=$regCurrent['arr_time']?>" /></td>
									</tr>
									<tr>
										<td>Departure Airport</td>
										<td><input type='text' name='depAirport' value="<?=$regCurrent['dep_airport']?>" /></td>
									</tr>
									<tr>
										<td>Airline / Flight Number</td>
										<td><input type='text' name='depNumber' value="<?=$regCurrent['dep_number']?>" /></td>
									</tr>
									<tr>
										<td>Departure Time</td>
										<td><input type='text' name='depTime' value="<?=$regCurrent['dep_time']?>" /></td>									
									</tr>
									<tr>
										<td>Needs CH Accomodation</td>
										<td>
											<select name='help' id='help'>
												<option value='n'
												<? if ($regCurrent['help'] == 0) echo " selected='selected'"; ?>
												>no</option>
												<option value='y'
												<? if ($regCurrent['help'] == 1) echo " selected='selected'"; ?>
												>yes</option>
											</select>
										</td>
									</tr>
									<tr class="accomodation">
										<td>CH Family Name:</td>
										<td><input type='text' name='family' value="<?=$regCurrent['family']?>" /></td>
									</tr>
									<tr class="accomodation">
										<td>CH Address</td>
										<td><input type='text' name='address' value="<?=$regCurrent['address']?>" /></td>
									</tr>
									<tr class="accomodation">
										<td>CH Phone</td>
										<td><input type='text' name='phone' value="<?=$regCurrent['phone']?>" /></td>
									</tr>
									
									<tr>
										
										<td>
											<? if (!empty($regCurrent['file'])) { ?>
												Change Photo
											<? } else { ?>
												Add Photo
											<? } ?>
										</td>
										<td><input type="file" name="photo" /></td>
										
										<? if (empty($regCurrent['file'])) : ?>
										<div class="image-upload" id="crop-avatar-child">
											<div class="avatar-view" title="Add Photo">
												<img src="/mobile/reg/images/addphoto.png" class="img-people" id="imgchild" />
											</div>
				                        </div>
				                        <? endif; ?>
									</tr>
									
									<tr>
										<td width="150">Child is allowed to walk alone during day time.</td>
										<td>
											<select name="walk">
												<? 
												$walk = $regCurrent['walk_alone']; 
												if ($walk) {
													echo "<option value='0'>No</option>
															<option value='1' selected>Yes</option>";
												} else {
													echo "<option value='0' selected>No</option>
															<option value='1'>Yes</option>";
												}
												?>
											</select>
										</td>
									</tr>
									
									<tr>
										<td>If Yes, enter cross streets.</td>
										<td><input type="text" name="streets" value="<?=$regCurrent['between_streets']?>" /></td>
									</tr>
									
									<tr>
										<td>Allergies</td>
										<td><textarea rows="3" cols="19" name="allergy"><?=$regCurrent['allergies']?></textarea></td>
									</tr>
									
									<tr>
										<td>Shoe Size</td>
										<td><input type="text" name="shoeSize" value="<?=$regCurrent['shoe_size']?>" /></td>
									</tr>								
									
									<tr>
										<td>Notes</td>
										<td>
											<textarea rows="5" cols="19" name="notes"><?=$regCurrent['notes']?></textarea>
										</td>
									</tr>
									<tr>
										<td></td>
										<td id="editRow">
											<input type="submit" name="submit" value="Update Participant" id="edit" />
										</td>
									</tr>
								</table>
							</fieldset>
						</div>
					</form>
					</div>
				
				<? } ?>
					
				<div style="clear: both"></div>
				<form action="../chidon_reg_post.php" method="post" id="payForm">
					<input type='hidden' name='schoolID' value="<?=$school['chidon_schools_id']?>" />
					<input type="hidden" name="gender" value="<?=$school['gender']?>" />
					<input type="hidden" name="action" value="pay" />
					
					<a name="chap"></a>
					<fieldset>
						<legend>Chaperone Payment</legend>
						<table>
							<tr>
		        				<td colspan="2">
		        					<input type="checkbox" name="chapReg" id="chapReg" /> 
		        					Yes I would like to participate in the full program. ($100)
		        				</td>
		        			</tr>
		        			<tr>
		        				<td colspan="2">
		        					<input type="checkbox" name="chapShirt" id="chapShirt" /> 
		        					Yes I would like to order a  
		        					<select name="chapSize" id="chapSize">
		        						<option value='s'>Adult S</option>
		        						<option value='m'>Adult M</option>
		        						<option value='l'>Adult L</option>
		        						<option value='xl'>Adult XL</option>
		        					</select>
		        					sweatshirt. ($18)
		        				</td>
		        			</tr>
		        		</table>
					</fieldset>
					
					<? if (!empty($reg)) { ?>
					
					<div id="regList">
						<fieldset>
							<legend>Chidon Participants</legend>
								<table>
									<tr>
										<th class="ctr">Pay For</th>
										<th class="ctr">First Name</th>
										<th class="ctr">Last Name</th>
										<th class="ctr">Grade</th>
										<th class="ctr">Type</th>
										<th class="ctr">Sweater Size</th>
										<th class="ctr">Hebrew Names</th>
										<th class="ctr">Test Marks</th>
										<th class="ctr">Parent Info</th>
										<th class="ctr">CH Accommodation Info</th>
										<th class="ctr">Photo</th>
									</tr>
									<?
									$total = count($reg);
									for ($i = 0; $i < $total; $i++) {
										$row = $reg[$i];
										$id = $row['chidon_reg_id'];
										$paid = $row['paid'];
										$size = $row['size'];
										$name = $row['name'];
										$lname = $row['last_name'];
										$hfname = $row['hfname'];
										$hlname = $row['hlname'];
										$help = $row['help'];
										$family = $row['family'];
										$address = $row['address'];
										$phone = $row['phone'];
										?>
										<tr>
											<td class="ctr">
												<? 
												if ($paid) {
													echo "<span class='green'>&#x2713;</span>";
												} else { 
												?>
												<input type="checkbox" name='pay[<?=$row['chidon_reg_id']?>]' class="pay" />
												<? } ?>
												<input type="hidden" class="paySize" value="<?=$row['size']?>" />
												<input type="hidden" class="payMark1" value="<?=$row['mark1']?>" />
												<input type="hidden" class="payMark2" value="<?=$row['mark2']?>" />
												<input type="hidden" class="payParentName" value="<?=$row['parent_name']?>" />
												<input type="hidden" class="payParentEmail" value="<?=$row['parent_email']?>" />
												<input type="hidden" class="payParentCell" value="<?=$row['parent_cell']?>" />
											</td>
											<td>
												<?=$name?>
											</td>
											<td>
												<?=$lname?>
											</td>
											<td class="ctr">
												<?=$row['grade']?>
											</td>
											<td>
												<?=$row['type']?>
											</td>
											<td>
												<?=$size?>
											</td>
											<td class="ctr">
												<?
												if (empty($hfname) && empty($hlname)) {
													echo '<span class="red">&#x2717;</span>';
												} else {														
													echo '<span class="green">&#x2713;</span>';
												}
												?>											
											</td>
											<td class="ctr">
												<?
												if (empty($row['mark1']) || empty($row['mark2']) || empty($row['mark3'])) {
													echo '<span class="red">&#x2717;</span>';
												} else {														
													echo '<span class="green">&#x2713;</span>';
												}
												?>											
											</td>
											<td class="ctr">
												<?
												if (empty($row['parent_name']) || 
													empty($row['parent_email']) || 
													empty($row['parent_cell'])) {
														echo '<span class="red">&#x2717;</span>';
												} else {														
													echo '<span class="green">&#x2713;</span>';
												}
												?>
											</td>
											<td class="ctr">
												<?
												if (!$row['help']) {
													if (empty($row['family']) ||
														empty($row['address']) || 
														empty($row['phone'])) {
															echo '<span class="red">&#x2717;</span>';
													} else {														
														echo '<span class="green">&#x2713;</span>';
													}
												} else {
													echo "needs accomodation";
												} 
												?>
											</td>
											<td class="ctr">
												<? 
												if (!empty($row['file'])) {
													echo '<span class="green">&#x2713;</span>';
													echo "<input type='hidden' class='file' value='1' />";
												} else {
													echo '<span class="red">&#x2717;</span>';
													echo "<input type='hidden' class='file' value='0' />";
												} 
												?>
											</td>
											
											<td>
												<a href="register_<?=$gender?>.php?id=<?=$id?>&edit=1">edit</a>
											</td>
											<!--
											<td>
												<input type="hidden" class="delID" value="<?=$id?>" />
												<a href="#" class="delete">delete</a>
											</td>
											-->
										</tr>
									<? 
									}
								
								if ($action == 'edit') {
									echo "<tr><td colspan='2'><a href='register_" . $gender . ".php?id=" . 
										$school['chidon_schools_id'] . "'>
										<input type='button' value='Add New Participant' /></a></td></tr>";
								}
								
								?>
							</table>
						</fieldset>
																
						<fieldset id='cc'>
							<legend>Credit Card Info</legend>
							<table>
								<tr>
									<td>Total being charged:</td>
									<td><span class='total'></span></td>
								</tr>
								<tr>
									<td>Credit Card Type</td>
									<td> 
										<select name='cctype'> 
											<option value='mc'>MasterCard</option>
											<option value='visa'>Visa</option>
											<option value='amex'>Amex</option>
											<option value='disc'>Discover</option>
										</select>
									</td>
								</tr>
								<tr>
									<td>Name on Credit Card</td>
									<td><input type='text' name='ccname' id='ccname' /></td>
								</tr>
								<tr>
									<td>Credit Card Number</td>
									<td><input type='text' name='ccnum' id='ccnum' size="40"
										value="<?=isset($_POST['ccnum']) ? $_POST['ccnum'] : ''?>" /></td>
								</tr>
								<tr>
									<td>Expiry</td>
									<td>
										<select name='mm' id='mm'>
											<? for ($i = 1; $i < 13; $i++) {
												$val = (string)$i;
												if (strlen($val) == 1)
													$val = '0' . $val;
												if (isset($_POST['mm']) && $_POST['mm'] == $val) {
													echo "<option value=$val selected='selected'>$val</option>";
												} else {
													echo "<option value=$val>$val</option>";
												}
											} ?>
										</select>
										<select name='yy' id='yy'>
											<? for ($i = 2015; $i < 2021; $i++) {
												if (isset($_POST['yy']) && $_POST['yy'] == $i) {
													echo "<option value=$i selected='selected'>$i</option>";
												} else {
													echo "<option value=$i>$i</option>";
												} 
											} ?>
										</select>
									</td>
								</tr>
								<tr>
									<td>Security Code</td>
									<td><input type='text' name='scode' id="scode" size='3' /></td>
								</tr>
								<tr>
									<td>Billing Zip Code</td>
									<td><input type='text' name='zcode' id='zcode' size='5' /></td>
								</tr>
								<tr>
									<td>Email (for confirmation email)</td>
									<td><input type='email' name='email' id='email' size='30' /></td>
								</tr>
								<tr>
									<td colspan="2">
										<input type='checkbox' name='agree' id='agree' /> 
										I allow Tzivos Hashem to charge my credit card <span class='total'></span>
										<input type='hidden' name='total' id='total' value='0' />
									</td>
								</tr>
							</table>
						</fieldset>
						
						<fieldset id='terms'>
							<legend>Terms</legend>
							<div>
								<?=$terms?>
								<b>Chidon Hosts</b><br />
								Chidon hosts are just for sleeping and the Friday night meal. 
								The Chidon will provide all other meals, as well as transportation to and from their homes.
							</div>
							<br />
							<div>
								<input type='submit' name='submit' value='I agree to terms and charges' id="submit" />
							</div>
						</fieldset>
						
						<? } ?>
					</div>
					
					<div>
						Click <a href="reports/chaperones.php?id=<?=$_GET['id']?>">here</a> for the Report for your Chaperone
					</div>
	       		<? } ?>
	  		</form>    
       </div>
  
	    </div>
			
		</div>
			<!---->
			
			<!---->
			
			<!---->
	</div>
	<!---->
		<!---->
	
		<div class="clearfix"></div>
		</div>
	</div>
	
	<div class="archives-top">
				
				<div class="col-md-4 top-archives">
				  <h3>Yahadus curriculum created in memory of Sara Rohr
</h3>
				</div>
				<div class="col-md-4 top-archives">
				  <h3>A project of: <img src="images/sponsors.png" width="102" height="47" alt=""/></h3>
				</div>
				<div class="col-md-4 top-archives">
               
				  
				  
				  <h3>Chidon Sponsor:
					<? 
					$str = " הרוצה בעילום שמו להצלחה מופלגה בגשמיות וברוחניות ";
					$he = iconv('utf8', 'windows-1255', $str);
					echo $he;
					?>
				  </h3>
					
				</div>
                
      <div class="col-md-12 top-archives">
					<h3>Chidon Partners:</h3>
					
				</div>
				<div class="clearfix"></div>
                
  </div>
	
	
	
<script type="text/javascript">
						$(document).ready(function() {
							/*
							var defaults = {
					  			containerID: 'toTop', // fading element id
								containerHoverID: 'toTopHover', // fading element hover id
								scrollSpeed: 1200,
								easingType: 'linear' 
					 		};
							*/
							
							$().UItoTop({ easingType: 'easeOutQuart' });
							
						});
					</script>
				<a href="#" id="toTop" style="display: block;"> <span id="toTopHover" style="opacity: 1;"> </span></a>



</body>
	<script type="text/javascript">
		function addFilePic( form ) {
			if (!sizeEntered()) {
				alert("You must choose a sweater size!");
				return false;
			}
			var pic = $(form).find('#imgchild').attr('src');
			if (pic == '/mobile/reg/images/addphoto.png') {
				alert('You must upload a photo.');
				return false;
			} else {
				$(form).find('input[name="filePic"]').val(pic);
				return true;
			}
		}
		
		function sizeEntered() {
			var size = $("#size").val();
			if (size == 0 || size == '0') return false;
			else return true;
		}
	
		$(document).on('click', '#chapReg', function() {
			calculateTotal();
		});
		
		$(document).on('click', '#chapShirt', function() {
			calculateTotal();
		});
		
		$(document).on('click', '.pay', function() {
			calculateTotal();
		});
		
		$(document).on('click', '#submit', function() {
			return validate();
		});
		
		$(document).on('click', '#add', function() {
			var fname = $("#name").val();
			var lname = $("#lname").val();
			var hname = $("#hname").val();
			var lhname = $("#hname_last").val();
			var mark1 = $("#mark1").val();
			var mark2 = $("#mark2").val();
			var mark3 = $("#mark3").val();
			var bonus = $("#bonus").val();
			var pname = $("#parentName").val();
			var pemail = $("#parentEmail").val();
			var pcell = $("#parentCell").val();
			
			var errors = [];
			if (fname == '') {
				errors.push('You must enter a first name.');
			}
			if (lname == '') {
				errors.push('You must enter a last name.');
			}
			if (hname == '') {
				errors.push('You must enter the hebrew first name.');
			}
			if (lhname == '') {
				errors.push('You must enter the hebrew last name.');
			}
			if (mark1 == '' || mark1 == 0) {
				errors.push('You must enter a mark for Test 1.');
			}
			if (mark2 == '' || mark2 == 0) {
				errors.push('You must enter a mark for Test2.');
			}
			if (bonus == '' || bonus == 0) {
				errors.push('You must enter a mark for the Replacement Questions.');
			}
			if (mark3 == '' || mark3 == 0) {
				errors.push('You must enter a mark for Test2.');
			}
			if (pname == '') {
				errors.push('You must enter the parent\'s name.');
			}
			if (pemail == '') {
				errors.push('You must enter the parent\'s email.');
			}
			if (pcell == '') {
				errors.push('You must enter the parent\'s cell number.');
			}
			
			var pic = $("#imgchild").attr('src');
			if (pic == '/mobile/reg/images/addphoto.png') {
				errors.push('You must upload a photo.');
			}
			
			if (errors.length) {
				var str = '';
				for (var e in errors) {
					str += errors[e] + "\n";
				}
				alert(str);
				return false;
			} else {
				$('#regForm').submit();
			}
		});
		
		$(document).on('change', '#help', function() {
			var val = $(this).val();
			var table = $(this).parent().parent().parent();
			if (val == 'n')
				$(table).find('.accomodation').show();
			else if (val == 'y')
				$(table).find('.accomodation').hide();
		});
		
		$(function() {
			$("#loadSaved").on('click', function(e) {
				e.preventDefault();
				var username = $("#username").val();
				var school = $("#school").val();
				var year = <?=$year?>;
				var gender = '<?=$gender?>';
				if (username == '') {
					alert('You must enter the username in order to retrieve your information.');
					return false;
				}
				$.post('../ajax/getChidonSchool.php', {
					year : year, 
					username : username, 
					school : school, 
					gender : gender 
				}, function( success ) {
					if (success == 0) {
						alert('No such username / school exists. Please contact Tzivos Hashem.');
					} else {
						var gender = '<?=$gender?>';
						<? if (isset($_GET['debug'])) : ?>
						window.location = "https://mashpia.com/chidon/register_" + gender + ".php?debug=1&id=" + success;
						<? else : ?>
						window.location = "https://mashpia.com/chidon/register_" + gender + ".php?id=" + success;
						<? endif; ?>
					}
				});
			});
		});
		
		function calculateTotal() {
			var total = 0;
			$(".pay").each( function() {
				if ($(this).is(":checked")) {
					total++;
				}
			});
			
			var gtotal = 0;
			if (total) gtotal = total * 115;
			if ($("#chapReg").is(":checked")) {
				gtotal += 100;
			}
			if ($("#chapShirt").is(":checked")) {
				gtotal += 18;
			}
			
			if (gtotal > 0) {
				$(".total").text('$' + gtotal);
			} else {
				$(".total").text('');
			}
			$("#total").val(gtotal);
		}
		
		function validate() {
			var errors = [];			
			
			$(".pay").each( function() {
				if ($(this).is(":checked")) {					
					var hasPhoto = $(this).parent().parent().find(".file").val();
					var size = $(this).parent().parent().find(".paySize").val();
					var mark1 = $(this).parent().parent().find(".payMark1").val();
					var mark2 = $(this).parent().parent().find(".payMark2").val();
					var mark3 = $(this).parent().parent().find('.payMark3').val();
					var parentName = $(this).parent().parent().find(".payParentName").val();
					var parentEmail = $(this).parent().parent().find(".payParentEmail").val();
					var parentCell = $(this).parent().parent().find(".payParentCell").val();
					
					if (hasPhoto == '0' || size == '0' || parentName == '' || parentEmail == '' || parentCell == '' 
						|| mark1 == '0' || mark1 == '' || mark2 == '0' || mark2 == '' || mark3 == '' || mark3 == 0) {
							alert('You must have the following filled out for each contestant that you are paying for:\n1.picture\n2.mark1\n3.mark2\n4.mark3\n5.parent name\n6.parent email\n7.parent cell');
							return false;	
					}
				}
			});
			
			var numPay = 0;		
			$(".pay").each( function() {
				if ($(this).is(":checked")) {
					numPay++;
				}
			});
			
			if ($("#chapReg").is(":checked") || $("#chapShirt").is(":checked")) {
				numPay++;
			}
			if (numPay == 0) {
				errors.push('You have not chosen anyone to pay for!');
			}
			
			if ($.trim($("#ccname").val()) == '') {
				errors.push('You must enter the name on the credit card.');
			}
			if ($.trim($("#ccnum").val()) == '') {
				errors.push('You must enter your credit card number.');
			} 
			if ($.trim($("#scode").val()) == '') {
				errors.push('You must enter your security code.');
			}
			if ($.trim($("#zcode").val()) == '') {
				errors.push('You must enter your billing zip code.');
			}
			var email = $.trim($("#email").val());
			if (email == '') {
				errors.push('You must enter an email address.');
			}
			var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
			if (!filter.test(email)) {
				errors.push('You must enter a valid email.');	
			}
			if (!$("#agree").is(":checked")) {
				errors.push('You must indicate that you agree to the terms.');
			}
			
			if (errors.length) {
				var str = '';
				for (e in errors) {
					str += errors[e] + "\n";
				}
				alert(str);
				return false;
			} else {
				return true;
			}
		}
		
		$(document).on('change', '#grade', function() {
			var val = $(this).val();
			switch (val) {
				case '4':
					$("#book option[value='1']").attr('selected', true);
					break;
				case '5':
					$("#book option[value='2']").attr('selected', true);
					break;
				case '6':
					$("#book option[value='3']").attr('selected', true);
					break;
				case '7':
				case '8':
					$("#book option[value='4']").attr('selected', true);
					break;
			}
		});
		
		$(document).on('blur', '#mark1', function() {
			calcAvg();
		});
		
		$(document).on('blur', '#mark2', function() {
			calcAvg();
		});
		
		$(document).on('blur', '#mark3', function() {
			calcAvg();
		});
		
		$(document).on('blur', '#bonus', function() {
			calcAvg();
		});
		
		function calcAvg() {
			var marks = 0;
			var num1 = $("#mark1").val() ? parseInt($("#mark1").val()) : 0;
			var num2 = $("#mark2").val() ? parseInt($("#mark2").val()) : 0;
			var num3 = $("#mark3").val() ? parseInt($("#mark3").val()) : 0;
			var num4 = $("#bonus").val() ? parseInt($("#bonus").val()) : 0;
			if (num1) marks++;
			if (num3) marks++;
			if (num4) marks++;
			if (marks) var avg = Math.round((num1 + num2 + num3 + num4) / marks);
			else var avg = 0;
			$("#avg").text(avg);
		}
		
		$(document).on('click', '.delete', function() {
			var delID = $(this).parent().find('.delID').val();
			$.post('delChidon.php', { user : delID }, function( success ) {
				if (success) {
					$(this).parent().parent().remove();
					location.reload();
				}
			});
		});
		
		<? if (isset($schoolID)) { ?>
			$(document).on('click', '#update', function() {
				var school = <?=$schoolID?>;
				
				var names = [];
				var numbers = [];
				$(".chaperone").each( function() {
					names.push($(this).val().trim());
				});
				
				$(".chaperonePhone").each( function() {
					numbers.push($(this).val().trim());
				});
				
				$.post('ajax/updateChidonInfo.php', { school : school, names : names, numbers : numbers }, 
				function( success ) {
					if (parseInt(success)) alert('Updated.');
				});
			});
		<? } ?>
	</script>
</html>