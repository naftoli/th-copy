<?
$admin_auth = array('school'); 
require('header.php');

if (isset($_POST['submit'])) {
	//$sortBy = "?sortBy=" . $_POST['sort'];
	switch ($_POST['type']) {
		case 'army':
			$location = "yud_alef_nissan_report.php";
			break;
		case 'base':
			$location = "yud_alef_nissan_school_report.php";
			break;
		case 'platoon':
			$location = "yud_alef_nissan_class_report.php";
			break;
		case 'all':
			$location = "yud_alef_nissan_all.php";
			break;
	}
	header("Location: $location");
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Our birthday present to the Rebbe</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            fieldset {
                border: 1px solid white;
                padding: 10px;
                padding-top: 0px;
                -moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px;
            }
            legend {
                margin-left: 20px;
                padding: 5px;
                color: purple;
            }
        </style> 
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>  
        <script>
        	$(function() {
        		$(".type").click(function() {
        			var val = $(this).val();
        			switch (val) {
        				case 'army':
        				case 'all':
        					$("#school").show();
        					$("#reg").show();
        					break;
        				default: 
        					$("#school").hide();
        					$("#reg").hide();
        					break;
        			}
        		});
        	});
        </script>     
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1 class="no-print">Our birthday present to the Rebbe</h1>
        
        <form action="yud_alef_nissan_choose.php" method="post">
        	<fieldset>
        		<legend>Report Type</legend>
        		<? if ($admin_user['auth'] == 'super') { ?>
        		<input type="radio" name="type" class="type" value="army" checked="checked" /> Army-Wide<br />
        		<? } ?>
        		<input type="radio" name="type" class="type" value="base" /> Base-Wide<br />
        		<input type="radio" name="type" class="type" value="platoon" /> Platoon-Wide<br /> 
        		<!--<input type="radio" name="type" class="type" value="all" /> All of the above-->
        	</fieldset>
        	<!--
        	<br />
        	<fieldset>
        		<legend>Sort Report By</legend>
        		<span id='school'>
        			<input type="radio" name="sort" class="sort" value="school" checked="checked" /> School (A-Z)<br />
        		</span>
        		<span id='reg'>
        			<input type="radio" name="sort" class="sort" value="reg" /> Highest Registered Chayolim<br />
        		</span>
        		<span id='tanya'>
        			<input type="radio" name="sort" class="sort" value="tanya" /> Highest Tanya<br />
        		</span>
        		<span id='mishna'>
        			<input type="radio" name="sort" class="sort" value="mishna" /> Highest Mishna<br />
        		</span>
        		<!--
        		<span id='maos'>
               		<input type="radio" name="sort" class="sort" value="maos" /> Highest Mo'os Chitim
                </span>
              
        	</fieldset>
        	-->
        	<br />
        	<input type="submit" name="submit" value="generate" />
        </form>
	</body>
</html>