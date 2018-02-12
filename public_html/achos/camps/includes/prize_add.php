<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();

if(isset($_POST["prize_name"])){
	include_once("db.php");
	include ("../application/php/classes/camp_prize.php");

	if (!empty($_FILES['image']['name']) && $_FILES['image']['size'] > 0) {
		$count = 0;
		
		do {
			if($count++ > 100000) trigger_error('could not get ID', E_USER_ERROR);
			$prize_image_id = mysql_result(mq('SELECT FLOOR(RAND() * 4294967295)'),0);
		} while (mysql_result(mq("SELECT COUNT(*) FROM files WHERE file_id = $prize_image_id"),0) != 0);
		
		if (!mq("INSERT INTO files (file_id, file_name, file_content_type, file_data) VALUES ($prize_image_id, " . ms($_FILES['image']['name']) . ', ' . ms(mime_content_type($_FILES['image']['tmp_name'])) . ', ' . ms(file_get_contents($_FILES['image']['tmp_name'])) . ')')) 
			print "Error uploading image";
	}

	$prize = new camp_prize();
	$insert = $prize->add_new_prize($camp_id, $_POST["prize_name"], $_POST["prize_description"], $_POST["prize_points"], $_POST["prize_available"], $prize_image_id);
	if ($insert)
		$message = "success";
	else
		$message = $insert;
		
	print $message;
}
else{
?>
	


<script>
$(document).ready(function(){
//$('#new_prize').ajaxForm({success:displayData});
});
function displayData(responseText, statusText, xhr, $form){
alert('status: ' + statusText + '\nresponse: ' + responseText);
}
			$(function() {
				$('#new_prize a.submit').click(function(){

                 //$('#new_prize').ajaxSubmit(displayData);
		$('#overlay .close').click();
                  var data = $("#new_prize").serialize();
                  $.post("includes/prize_add.php", data,  function(data){
                  if(data != "success"){
                    //alert("An error occurred, please try again later");
			 alert("This prize name already exists - Enter a new one");
                  }else{
                   alert("Added new prize successfully");
			var url = 'content.php?output=store';
			$.get(url,'',function(data) {
				$('.slider_container').append(data);
				$('.slider_container .slider:last .col_title').append('<a class="slider_back"></a>');
				$('.slider_container .slider:last .col_title a').html($('.slider:last .col_title span').html());
				$('.slider_container').data('url',url);
				hideLoader();
				initialize();
				slide_width = 773;
				$('.slider_container').animate({'margin-left':parseInt($('.slider_container').css('margin-left')) - slide_width + 'px'}, 500, hideLoader());
			});
                   // $('#overlay .close').click();
                  }
});
				});
            });
        </script>

	<?php if ($_GET['param']) { 
		$sql = "SELECT * FROM prizes_camp WHERE prize_id=" .  $_GET['param'];
        	$query = mq($sql);

		while ($row = mysql_fetch_assoc($query)) {
			$prize_id = $row['prize_id'];
			$prize_name = $row['prize_name'];
			$prize_description = $row['prize_description'];
			$prize_points = $row['prize_points'];
			$prize_available = $row['prize_available'];
			$prize_image = $row['prize_image_id'];
        	}
	} ?>
	<form name="new_prize" id="new_prize" method="post" action="" enctype="multipart form-data" >
		<?php if ($prize_id) { ?><input type="hidden" name="prize_id" id="prize_id" value="<?php echo $prize_id; ?>" /><?php } ?>
			<div class="slider">
                <div class="col_title"><span>Add/Edit Prize</span></div>
				<div class="col_content">
                <div class="module clearfix" id="module-info">
                    <div class="module_content list form">
                        <ul>
                            <li>
                                <span class="label">Prize Name</span>
                                <span class="input"><input type="text" name="prize_name" value="<?php if ($prize_name) { echo $prize_name; } ?>" /></span>
                            </li>
                            <li>
                                <span class="label">Description</span>
                                <span class="input"><input type="text" name="prize_description" value="<?php if ($prize_description) { echo $prize_description; } ?>" /></span>
                                <span class="tip">Note: Output as is, including any HTML.</span>
                            </li>
                            <li>
                                <span class="label">Points</span>
                                <span class="input"><input type="text" name="prize_points" value="<?php if ($prize_points) { echo $prize_points; } ?>" /></span>
                            </li>
                            <li>
                                <span class="label">Prizes Available</span>
                                <span class="input"><input type="text" name="prize_available" value="<?php if ($prize_available) { echo $prize_available; } ?>"  /></span>
                                <span class="tip">Leave blank for unlimited.</span>
                            </li>
                            <li>
                                <span class="label">Image</span>
                                <span class="input"><input type="file" id="image" name="image" /></span>
                                <span class="tip">Maximum file size: 2MB. Image size: 300px x 300px.</span>
				<?php if ($prize_image) { print "<br />"; print "<img src=\"http://www.mashpia.com/file_view.php?id=" . $prize_image . "\" />"; } ?>
                            </li>
                            <li>
				<input type="submit" method="post" value="Next" class="submit button" /><!--
                                <a href="#" class="submit button" id="prize_add" >Next</a>-->
                            </li>
                        </ul>
                    </div>
                </div>
                    </div>
                </div>
	</form>

<?php } ?>
