<?
$admin_auth = array('user'); 
require('header.php'); 

require_once 'classes/admin.php';
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new \classes\admin($row);
$admin->get_markable_children();
//delete children that do not have school or class id
for ($i = 0; $i < count($admin->children); $i++) {
	if (empty($admin->children[$i]->school_id) || empty($admin->children[$i]->class_id)) {
		unset($admin->children[$i]);
	}
}

require_once 'class.parshos.php';
$parshos = Parshos::getParshos(5774);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Personalized Progress Report</title>
        <script src="scripts/jquery-1.8.3.js"></script>
        <script src="scripts/jquery.styleselect.js"></script>
        <script src="js/parentPersonalizedReport.js"></script>
        <link href="admin_styles.css" rel="stylesheet" type="text/css" />
        <style type="text/css">
	        table {
			    font-size: 12px;
			}
			th, td {
			    padding: 3px 10px;
			}
			.newPage {
			    page-break-after: always;
			}
			fieldset {
			    border: 1px solid white;
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
			@media screen {
			    .instructions {
			        display: none;
			    }
			}
			@media print {
			    .instructions {
			        display: block;
			    }
			}
        	div.newListSelected  {
				margin-right: 100%;
			}
			#loading {
			    margin-top: 20px;
			}
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Personalized Progress Report</h1>
        
        <?
        if (isset($_GET['msg'])) {
        	echo "<div style='color: red'>" . urldecode($_GET['msg']) . "</div>";
        }
        ?>
        
        <form action="createParentPersonalizedReport.php" method="post"> 
            <select name="user" id="user">
            	<?
            	/*
            	$all = "";
            	foreach ($admin->children as $child) {
            		$all .= $child->user_id . ":";
            	}
				if (count($admin->children) > 1)
					echo "<option value=" . $all . ">All</option>";
            	
				 * 
				 */
				foreach ($admin->children as $child) {
            		echo "<option value=" . $child->user_id . ">" . $child->first . ' ' . $child->last . "</option>";
            	}
            	?>
            </select><br />
            
            <fieldset id='dates'>
            	<legend>Choose Dates</legend>
            	From Parsha: <select name="start" id="start" class="date">
	            	<option value='0'>Choose Parsha</option>
	            	<?
	            	$first = true;
	            	foreach ($parshos as $parsha) {
	            		echo "<option value='" . $parsha['start'] . "' ";
	            		if ($first) {
	            			echo "selected='selected' ";
	            			$first = false;
	            		}
	            		echo ">" . $parsha['name'] . "</option>";
	            	}
	            	?>
	            </select><br />
	            
	            To Parsha: <select name="end" id="end" class="date">
	            	<option value='0'>Choose Parsha</option>
	            	<?
	            	foreach ($parshos as $parsha) {
	            		echo "<option value='" . $parsha['end'] . "' "; 
	            		$now = unixtojd();
	            		if ($now >= $parsha['start'] && $now <= $parsha['end']) {
	            			echo "selected='selected' ";
	            		}
	            		echo ">" . $parsha['name'] . "</option>";
	            	}
	            	?>
	            </select>
	        </fieldset>
	        
	        <fieldset id='reportType'>
	        	<legend>Report Type</legend>
	        	<input type="radio" name="reportType" class="reportType" value="0" checked="checked" /> Progress Report of all current tasks<br />
	        	<input type="radio" name="reportType" class="reportType" value="1" /> Progress Report of specific tasks	
	        </fieldset>
	        
	        <fieldset id='campaign'>
	        	<legend>Choose Campaign</legend>
            	<select name="subject" id="subject"></select><br />
	        </fieldset>
	        
	        <div id='loading'></div>
	        
	        <fieldset id="tasks">
	        	<legend>Choose Task(s)</legend>
	        	<select name="task[]" id="task" multiple="multiple"></select>
	        </fieldset>
            
            <br />
            <input type="submit" name="submit" id="submit" value="submit" />
        </form>
    </body>
</html>