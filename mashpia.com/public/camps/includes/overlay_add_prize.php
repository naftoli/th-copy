<?php	
include ("get_camp_id.php");
$camp_id = get_camp_id();

	if (isset($_GET["edit_prize"])) {
		$edit = true;
		$prize_id = $_GET["edit_prize"];

		$sql = "SELECT * FROM prizes_camp WHERE camp_id=" . $camp_id . " AND prize_id=" . $prize_id;
		$query = mysql_query($sql);
		$result = mysql_fetch_assoc($query);
	}

if (isset($_POST["action"])) {
	include_once ("db.php");
	include_once ("file_save.php");

	$prize_name = $_POST['prize_name'];
	$prize_description = $_POST['prize_description'];
	$prize_points = $_POST['prize_points'];
	$prize_available = $_POST['prize_available'];
	$prize_id = $_POST['prize_id'];
	
	if ($_FILES['image']['size'] > 0) {
		$prize_image_id = addFile($_FILES['image'], $prize_image_id);
	} else {
		if ($_POST['prize_image_id'] > 0) {
			$prize_image_id = $_POST['prize_image_id'];
		} else {
		$prize_image_id = "NULL";
		}
	}

	 	
	if ($_POST["action"] == "edit") {
		$sql = "UPDATE prizes_camp SET camp_id=" . $camp_id . ", prize_name='" . mysql_real_escape_string($prize_name) . "', prize_description='" . mysql_real_escape_string($prize_description) . "', prize_points=" . $prize_points . ", prize_available=" . $prize_available . ", prize_image_id=" . $prize_image_id . ", installed=1 WHERE prize_id=" . $prize_id;
		$query = mysql_query($sql);
	} else {
		$sql = "INSERT INTO prizes_camp SET camp_id=" . $camp_id . ", prize_name='" . mysql_real_escape_string($prize_name) . "', prize_description='" . mysql_real_escape_string($prize_description) . "', prize_points=" . $prize_points . ", prize_available=" . $prize_available . ", prize_image_id=" . $prize_image_id . ", installed=1";
		$query = mysql_query($sql);
	}

       if ($query  == FALSE) {
		echo "error";
       } else {
		echo "success";
	};


} else {


?>
<script src="scripts/jquery.blockui.js"></script>
 
		<script type="text/javascript" src="includes/functions.js"></script>

		<script>
				var action = '';
				
				$(function() {
					$.blockUI.defaults.css = {};
					$.blockUI.defaults.overlayCSS = {}; 
					//$("#add_prize_form").validator({messageClass:'form_error'});
					function validator(){
						$.tools.validator.localize("en",{'[required]':'Required'});
						var checkValidity = $("#add_prize_form").validator({messageClass:'form_error'}).data("validator").checkValidity();
						if (checkValidity) {
							$.blockUI({ message: '<h1><img src="images/ajax-loader-uploading.gif" /> Uploading</h1>'});
						}
						return checkValidity;
					}
					
					$('form a.submit').click( function(e) {
						e.preventDefault();
						$('#action').val('<?=($edit?"edit":"add")?>');
						$('#add_prize_form').ajaxSubmit({
						beforeSubmit: validator,
						success: function(data){
							if (data != "" && data != "error") {
								$('#overlay a.close').click();
								setTimeout(slideRefresh,500);
								<? if ($edit) {?>
									$.growlUI('Prize Successfully Updated');
								<? } else {?> 
									$.blockUI({ message: $('#question')}); 
								<? }?>
							} else { 
								$.blockUI({ message: '<h1>An error occurred, please try again.</h1>',timeout: 2000 });
							}
						}

						});
					});					
					$('#question #another').click(function(e) { 
						e.preventDefault();
						$.unblockUI();
						$('#add_new_prize').click();
					}); 
					
					$('#question #cancel').click(function(e) { 
						e.preventDefault();
						$.unblockUI();
					}); 
				});


		
        </script>
		
		<div class="slider">
		
			<div class="col_title"><span>
				<?=($edit?"Editing ".$result['prize_name']:"Add Prize")?></span></div>
			
			<div class="col_content">
			
                <div id="module-info" class="module clearfix">
                    
					<div class="module_content list form">
					
                        <ul>
							
							<form name="add_prize_form" id="add_prize_form" action="includes/overlay_add_prize.php" method="post" enctype="multipart/form-data" accept-charset="UTF-8">
								<input type="hidden" name="action" id="action" value="add">
								<input type="hidden" name="prize_id" value="<?=($edit?$prize_id:'')?>">
								<input type="hidden" name="prize_image_id" value="<?=($edit?$result['prize_image_id']:'')?>">
								
								<li>
									<span class="label">Prize Name</span>
									<span class="input"><input type="text" name="prize_name" required="required" value="<?=($edit?$result['prize_name']:'')?>"></span>
								</li>
								
								<li>
									<span class="label">Description</span>
									<span class="input"><input type="text" name="prize_description" value="<?=($edit?$result['prize_description']:'')?>"></span>
									<span class="tip">Note: Output as is, including any HTML.</span>
								</li>
								
								<li>
									<span class="label">Points</span>
									<span class="input"><input onkeypress='return number_validation(event);' type="text" name="prize_points" required="required" value="<?=($edit?$result['prize_points']:'')?>"></span>
								</li>
								
								<li>
									<span class="label">Prizes Available</span>
									<span class="input"><input onkeypress='return number_validation(event);' type="text" name="prize_available" required="required" value="<?=($edit?$result['prize_available']:'')?>"></span>
									<span class="tip">Leave blank for unlimited.</span>
								</li>
								
								<li>
									<span class="label"><?=($edit&&isset($result['prize_image_id'])?"Replace Image":"Image")?></span>
									<? if ($edit && isset($result['prize_image_id'])) {?><img src="includes/file_view.php?id=<?=($edit?$result['prize_image_id']:'')?>" width="150" height="150" /><?}?>
									<span class="input"><input type="file" name="image"></span>
									<span class="tip">Maximum file size: 2MB. Image size: 300px x 300px.</span>
								</li>
								
								<li>
									<a href="#" title="Save" class="submit button"><?=($edit?"Update":"Save")?></a>
								</li>
								
							</form>
							
                        </ul>
			<div id="question">
                    	<p>Prize successfully <?=($edit?"updated":"added")?>.<?=$message;?></p>
                        <? if (!$edit) {?><p><a id="another" class="button" href="#">Add Another Prize</a></p>
                        <p><a id="cancel" class="button" href="#">Close</a></p><?}?>
                    </div>
		
                    </div>
					
                </div>
				
			</div>
					
		</div> <!-- <div class="slider"> -->
<? } ?>