<?
require '../db.php';
require '../class.mishnaInfo.php';

$user = mysql_real_escape_string($_POST['user']);
$sql = "select school_id, class_id from users where user_id = " . $user;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$school = $row['school_id'];
$grade = $row['class_id'];
$year = 5776;

$sedorim = MishnaInfo::getSedorim();
foreach ($sedorim as $id => $seder) {
	$mesechtos[$seder] = MishnaInfo::getMesechtos( $id );
	$assigned[$seder] = MishnaInfo::getAssigned( $id, $school, $grade, $year, $user );
}
$info = array();
foreach ($mesechtos as $seder => $other) {
	foreach ($other as $id => $mesechto) {
		if (in_array($id, $assigned[$seder])) { 
			$info[$seder][$id][$mesechto] = 1;
		} else {
			$info[$seder][$id][$mesechto] = 0;
		}
	}
}
$i = 1;
?>
<header class="navbar" id="top" role="banner">
    <div class="container">
        <div class="navbar-header">
        	<h1 style="font-size: 30px; height: auto">Assign משניות בע"פ</h1>
        </div>
    </div>
</header>

<div class="personalImg"></div>

<div class="container he" dir="rtl">
	<div class="content">
		<!--
		<div class="text-left" style="margin-bottom: 20px;">
			<input type="button" id="expandAll" class="btn btn-danger btn-sm" value="Expand All" style="background-color: #5e1c77;border-color:#834999;" />
		</div>
		-->
		<form action="assignMbpAction.php" method="post">
			<input type="hidden" name="year" value="<?=$year?>" />
			<input type="hidden" name="user" value="<?=$user?>" />
			<input type="submit" name="submit" value="Save" />
			<? foreach ($info as $seder => $other) : ?>
		    <div class="panel panel-default">
		    	<div class="panel-heading">
		    		<i class="glyphicon glyphicon-chevron-left"></i> סדר <?=$seder?>
		    	</div>
		    	
		    	<div class="collapse" id="<?=$i++?>">
		    		<div class="panel-body">
		    			<ul class="list-unstyled mesechtos">
		    				<? 
		    				foreach ($other as $id => $rest) {
		    					foreach ($rest as $mesechto => $assigned) {
		    						echo "<li><input type='checkbox' name='mesechto[" . $id . "]' ";
		    						if ($assigned) echo "checked";
		    						echo "/> " . $mesechto . "</li>";
		    					}
		    				}
							?>
		    			</ul>
		    		</div>
		    	</div>
		    </div>
		    <? endforeach; ?>
	    </form>
	    		                    
	</div>
</div>

<? include 'inc/footer.php' ?>

<? include 'inc/foot.php' ?>