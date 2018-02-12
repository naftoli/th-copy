<?
$admin_auth = array('school'); 
require('header.php'); 

require_once 'class.adminSchools.php';       
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], false );
$schools = $as->getSchools();

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
        <script src="js/personalizedReport.js"></script>
        <script>
        	$(function() {
        		<? if (count($schools == 1)) { ?>
			    	$("#school").trigger('change');
			    <? } ?>
        	});
        </script>
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
        
        <form action="createPersonalizedReport.php" method="post">
            <select name="school" id="school">
                <?
                if (count($schools) > 1) {
                    echo "<option value='0'>Select School</option>";
                }
                foreach ($schools as $id => $school) {
                    echo "<option value=$id>$school</option>";
                }
                ?>
            </select><br />
            
            <select name="class" id="class" style="display: none"></select><br />
            
            <select name="user" id="user" style="display: none"></select><br />
            
            <fieldset id='dates'>
            	<legend>Choose Dates</legend>
            	From Parsha: <select name="start" id="start" class="date" style="display: none">
	            	<option value='0'>Choose Parsha</option>
	            	<?
	            	foreach ($parshos as $parsha) {
	            		echo "<option value='" . $parsha['start'] . "'>" . $parsha['name'] . "</option>";
	            	}
	            	?>
	            </select><br />
	            
	            To Parsha: <select name="end" id="end" class="date" style="display: none">
	            	<option value='0'>Choose Parsha</option>
	            	<?
	            	foreach ($parshos as $parsha) {
	            		echo "<option value='" . $parsha['end'] . "'>" . $parsha['name'] . "</option>";
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
            <input type="submit" name="submit" id="submit" value="submit" style="display: none" />
        </form>
    </body>
</html>