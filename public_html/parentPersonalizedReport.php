<?
$admin_auth = array('user'); 
require('header.php');

require_once 'classes/admin.php';
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new \classes\admin($row);
$admin->get_markable_children();

$children = array();
$childrenInfo = array();
foreach ($admin->children as $child) {
	if (!empty($child->school_id) && !empty($child->class_id)) {
		$children[] = $child->user_id;
		$childrenInfo[$child->user_id] = (array)$child;
		//echo "<pre>"; print_r((array)$child); echo "</pre>";
	}
}
if (isset($_POST['user']) && $_POST['user'] > 0) {
	$children = array($_POST['user']);
}

require_once 'class.parshos.php';
$parshos = Parshos::getParshos(5776);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Personalized Progress Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            fieldset {
			    border: 1px solid black;
			    padding: 10px;
			    padding-top: 0px;
			    -moz-border-radius: 10px;
			    -webkit-border-radius: 10px;
			    border-radius: 10px;
			    font-size: 14px;
			}
			legend {
			    margin-left: 20px;
			    padding: 5px;
			    color: purple;
			    font-size: 16px;
			}
			.filter {
				width: 560px;
			    margin: auto;
			    padding: 10px;
			    font-size: 14px;
			}
        </style>
        
        <script src="scripts/jquery-1.8.3.js"></script>
        <script src="scripts/jquery.styleselect.js"></script>
        <script>
        	$(function() {
        		$(".date").sSelect();
        		$("#user").sSelect();
        		
        		$("#submit").click( function() {
        			//alert($("#start").val() + '-' + $("#end").val());
        			if ($("#start").val() >= $("#end").val()) {
        				alert("The starting parsha can not be before the ending parsha!");
        				return false;
        			}
        		})
        	});
        </script>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1 class="noPrint">Personalized Progress Report</h1>
		
		<?  
		if (isset($_POST['start']) && isset($_POST['end'])) {
			$start = $_POST['start'];
			$end = $_POST['end'];
		} else {	      
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
		}
		?>
		
		<div class="infobox2 filter">
			<form action='parentPersonalizedReport.php' method="post">
            	<div style="float: left">
	            	From the beginning of:<br /><select name="start" id="start" class="date">
		            	<?
		            	foreach ($parshos as $parsha) {
		            		echo "<option value='" . $parsha['start'] . "' ";
		            		if ($start == $parsha['start']) {
		            			echo "selected='selected' ";
		            		}
		            		echo ">" . $parsha['name'] . "</option>";
		            	}
		            	?>
		            </select>
	            </div>
	            
	            <div style="float: right">
		            To the end of:<br /><select name="end" id="end" class="date">
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
		        </div>
	            
	            <br /><br /><br />
	            <div style="margin-top: 16px"></div>
	            <div style="float: left">
		            For Child(ren):<br /><select name="user" id="user">
		            	<option value='-1'>All</option>
		            	<?
						foreach ($children as $id) {
		            		echo "<option value=" . $id . ">" . $childrenInfo[$id]['first'] . ' ' . $childrenInfo[$id]['last'] . "</option>";
		            	}
		            	?>
	            	</select>
	            </div>
            	
            	<div style="float: right">
            		<br />
            		<input type="submit" name="submit" id="submit" value="submit" />
            	</div>
            	
            	<div style="clear: both"></div>
		    </form>
		</div>
	    <br /><br />
		
		<?
		require_once 'class.personalizedReport.php';
		$r = new PersonalizedReport($start, $end, $children);
		$r->createReport($childrenInfo);
		?>
	</body>
</html>