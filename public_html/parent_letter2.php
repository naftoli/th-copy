<?
session_start();

if (!isset($_SESSION['school']) || !isset($_SESSION['schoolName']) || !isset($_SESSION['choice']) || 
	!isset($_SESSION['signature']) || !isset($_SESSION['admin_id'])) {
	header("Location: parent_letter.php");
}

$admin_auth = array('school'); 
require('header.php');

require_once 'class.parshos.php';
$parshos = Parshos::getParshos(5774);
//echo "<pre>"; print_r($_SESSION); echo "</pre>";
?>
<!DOCTYPE html>
<html>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Letter to Parents</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style> 
        	fieldset {
                border: 1px solid white;
                padding: 10px;
                padding-top: 0px;
                -moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px;
                font-size: 16px;
            }
            legend {
                margin-left: 20px;
                padding: 5px;
                color: purple;
            }
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
        </style>

	</head>
	
	<body>
		<? include('admin_header.php'); ?>
        <h1>Letter to Parents</h1>
        
        <form action="parent_letter3.php" method="post">
        	<fieldset>
        		<legend>Selection</legend>
        		<input type="radio" name="selection" class="selection" value="missions" /> I would like to send a missions report to parents<br />
        		<input type="radio" name="selection" class="selection" value="letter" /> I would like to send a letter to parents
        	</fieldset>
        	
        	<br />
	        <fieldset id="letterChoice">
	        	<legend>Choose Letter to Edit and Send</legend>
	        	<?
	        	$dir = dir("letters");
				while (($file = $dir->read()) !== false) {
					if (strpos($file, '.docx') || strpos($file, 'doc')) {
						echo "<input type='radio' name='file' class='file' value='$file'> $file <br />"; 
					}
				}
	        	?>
	        	-------------or---------------<br />
	        	<input type='radio' name='file' class='file' value=''> new file<br />  	
	        </fieldset>
	        
	        <br />
	        <fieldset id="editFile">
	        	<legend>Edit File</legend>
	        	<textarea name="content" id="editor" rows="20" cols="85"></textarea>
	        </fieldset>
		    
		    <br />
		    <fieldset id="method">
				<legend>Method of sending letter</legend>
				<input type="radio" name="method" class="method" value="email"> Email<br />
				<input type="radio" name="method" class="method" value="print"> Print 
			</fieldset>
			
			<fieldset id="missionsDates">
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
		    
		    <br />
		    <input type="submit" name="submit" value="continue" id="submit" />
	    </form>
	    
	    <script src='ckeditor/ckeditor.js'></script>
	    <script src="scripts/widgEditor.js"></script>
        <script>
        	$(function() {
        		$("#letterChoice").hide();
        		$("#editFile").hide();
        		$("#method").hide();
        		$("#missionsDates").hide();
        		$("#submit").hide();
        		
        		$(".selection").click(function() {
        			if ($(this).val() == 'letter') {
        				$("#letterChoice").show();
        				$("#method").hide();
        				$("#missionsDates").hide();
        				$("#submit").hide();
        			} else {
        				$("#letterChoice").hide();
        				$("#editFile").hide();
		        		$("#method").hide();
		        		$("#method").find('.method[value="email"]').attr("checked", true);
		        		$("#missionsDates").show();
		        		$("#submit").show();
        			}
        		});
        		
        		$(".file").click( function() {
        			$("#editFile").hide();
        			$("#editFile textarea").empty();
        			var file = $(this).val();

        			if (file) {
	        			$.post('ajax/getFile.php', {file : file}, function(data) {
	        				$("#editFile textarea").append(data);
	        				//CKEDITOR.replace('editor');
	        				$("#editFile").show();
	        				$("#method").show();
	        			});
	        		} else {
        				$("#editFile").show();
        				$("#method").show();
	        		}
        		});
        		
        		$(".method").click(function() {
        			$("#submit").show();
        		});
        	});
        </script>
	</body>
</html>