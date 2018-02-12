<?php
require_once '../db.php';
require_once '../class.mishnaInfo.php';

$sql = "select school_id, class_id from users where user_id = " . $user_id;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$school = $row['school_id'];
$grade = $row['class_id'];

$sedorim = MishnaInfo::getSedorim();
foreach ($sedorim as $id => $seder) {
	$mesechtos[$seder] = MishnaInfo::getMesechtos( $id );
	$assigned[$seder] = MishnaInfo::getAssigned( $id, $school, $grade, $user );
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
<div>
	<? foreach ($info as $seder => $other) : ?>
	<? $sederID = array_search($seder, $sedorim); ?>
    <div class="panel panel-default">
    	<div class="panel-heading mbp">
    		<? if ($lang == 2) : ?>
    			<i class="glyphicon glyphicon-chevron-left"></i> סדר <?=$seder?>
    		<? else : ?>
    			<i class="glyphicon glyphicon-chevron-right"></i> סדר <?=$seder?>
    		<? endif; ?>
    	</div>
    	
    	<div class="collapse" id="<?=$i++?>">
    		<div class="panel-body">
    			<ul class="list-unstyled mesechtos">
    				<? 
    				foreach ($other as $id => $rest) {
    					foreach ($rest as $mesechto => $assigned) {
    						echo "<li><input type='hidden' name='mID' value='" . $sederID . ':' . $id . "' />
    							<input type='checkbox' class='mesechto' name='mesechto[" . $id . "]' ";
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
    <p><button id="save" class='btn btn-danger btn-sm save' style='background-color : #5e1c77;border-color:#834999;'>Save</button></p>
</div>