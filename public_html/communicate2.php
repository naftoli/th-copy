<?
if (!isset($_POST['submit'])) {
	header("Location: communicate.php");
}

session_start();
$_SESSION['type'] = $_POST['type'];
$_SESSION['method'] = $_POST['method'];
$_SESSION['signature'] = $_POST['signature'];

$admin_auth = array('school'); 
require('header.php'); 
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<link href="communicate.css" rel="stylesheet" type="text/css">
		<title>Communicate with Parents</title>
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
		<h1>Communicate with Parents</h1>
		
		<form action="communicate3.php" method="post">
			
			<? if ($_SESSION['type'] == 'letter') : ?>
			
	        	<div id="letter">
			        <fieldset>
			        	<legend>Choose Letter to Edit and Send</legend>
			        	<?
			        	$sql = "select * from communicate_files";
			        	$result = mysql_query($sql);
						while ($row = mysql_fetch_assoc($result)) {
							$file = $row['file'];
							$desc = $row['description'];
							echo "<input type='radio' name='file' class='file' value='$file'> $file";
							if (!empty($desc)) {
								echo " (" . strtoupper($desc) . ")";
							}
							echo "<br />"; 
						}
			        	?>
			        	-------------or---------------<br />
			        	<input type='radio' name='file' class='file' value=''> new file<br />  	
			        </fieldset>
			    </div>
		        
		        <div id="edit">
		        	<br />
		        	<fieldset>
			        	<legend>Edit File</legend>
			        	<textarea name="content" id="editor" rows="20" cols="85"></textarea>
			        </fieldset>
		        </div>
		        
			<? elseif ($_SESSION['type'] == 'missions') : ?>
			
				<?
				require_once 'class.parshos.php';
				$parshos = Parshos::getParshos(5774);
				?>
			
				<div  id="dates">
					<fieldset>
						<legend>Choose dates for Missions Report</legend>
						<?
						$d = unixtojd();
						$day = date("N");
						$end = $d;
						
						switch ($day) {
						    case 1: //Monday
						        $end -= 2;
						        break;
						    case 2:
						        $end -= 3;
						        break;
						    case 3:
						        $end -= 4;
						        break;
						    case 4:
						        $end -= 5;
						        break;
						    case 5:
						        $end -= 6;
						        break;
						    case 6:
						        $end = $d;
						        break;
						    case 7: //Sunday
						    	$end -= 1;
						        break;
						    default:
						        break;
						}
						$start = $end - 6;
						?>
						From the beginning of:<br />
						<select name="start" id="start" class="date">
			            	<?
			            	foreach ($parshos as $parsha) {
			            		echo "<option value='" . $parsha['start'] . "' ";
			            		if ($start == $parsha['start']) {
			            			echo "selected='selected' ";
			            		}
			            		echo ">" . $parsha['name'] . "</option>";
			            	}
			            	?>
			   			</select><br />
			            To the end of:<br />
			            <select name="end" id="end" class="date">
			            	<?
			            	foreach ($parshos as $parsha) {
			            		echo "<option value='" . $parsha['end'] . "' "; 
			            		$now = unixtojd();
			            		if ($end == $parsha['end']) {
			            			echo "selected='selected' ";
			            		}
			            		echo ">" . $parsha['name'] . "</option>";
			            	}
			            	?>
			            </select>
					</fieldset>
				</div>
				
				<div id="comment">
					<br />
					<fieldset>
						<legend>Optionally add comment to beginning of email</legend>
						<textarea name="content" id="editor" rows="5" cols="85"></textarea>
					</fieldset>
				</div>
				
		    <? endif; ?>
		    
		    <br />
		    <input type="submit" name="submit" value="continue" id="submit" />
	    </form>
	    
	    <script>
	    	$(function() {
	    		$("#edit").hide();
	    		
	    		<? if ($_SESSION['type'] == 'letter') : ?>
	 	    		$("#submit").hide();
	 	    	<? endif; ?>
	    	});
	    	
	    	$(".file").click( function() {
    			$("#edit textarea").empty();
    			var file = $(this).val();

    			if (file) {
        			$.post('ajax/getFile.php', {file : file}, function(data) {
        				$("#edit textarea").append(data);
        				//CKEDITOR.replace('editor');
        				//$("#edit textarea").select();
        				$("#edit").show();
        				$("#submit").show();
        			});
	        	} else {
	        		$("#edit").show();
	        		$("#edit textarea").select();
	        		$("#submit").show();
	        	}
    		});
	    </script>
	</body>
</html>