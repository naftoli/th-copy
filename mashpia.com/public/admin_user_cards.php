<? 
$admin_auth = array('user'); 

require('header.php'); 

$card_type = gr('card_type', '');
$school_id = gri('school_id', -1);
$subject_id = gri('subject_id', -1);
$student_id = gri('student_id', -1);

// ********* Users underneath admin users ********** //
$soldiers = array();
$schools = array();
$prev_school_id = "";
foreach($admin_user['auths']['user'] as $user_id) { 
	$users_sql = "SELECT * FROM users JOIN schools USING (school_id) WHERE user_id=" . $user_id . " ORDER BY school_id";
	$users_query = mysql_query($users_sql);	
	$users_row = mysql_fetch_assoc($users_query);
	$soldier = new soldier($users_row);
	array_push($soldiers, $soldier);
	
	if ($prev_school_id != $users_row['school_id']) 
		array_push($schools, new \classes\school($users_row));
		
	$prev_school_id = $users_row['school_id'];
}
			
class soldier {
	var $user_id;
	var $first;
	var $last;
	var $school_id;
	var $school_name;
	
	function soldier($row) {
		$this->user_id = $row['user_id'];
		$this->first = $row['first'];
		$this->last = $row['last'];
		$this->school_id = $row['school_id'];
		$this->school_name = $row['school_name'];
	}
	
}

class school {
	var $school_id;
	var $school_name;
	
	function school($row) {
		$this->school_id = $row['school_id'];
		$this->school_name = $row['school_name'];	
	}
}
// ********* Users underneath admin users ********** //

// ********** School(s) ********** //
$schools_string = "";
if (count($schools) == 1) {
	$school_id = $schools[0]->school_id;
	$schools_string = "<label>School:" . $schools[0]->school_name . "</label>";
}
else {
	for ($cntr = 0; $cntr < count($schools); $cntr++) {
		if ($cntr == 0) 
			$schools_string = "\n\t<label>\n\t\t<select name='school_id' id='school_id' onchange='document.card_form.submit();'>\n\t\t\t<option value='-1'>" . T_('Select a school') . "</option>\n";
			
		if ($school_id == $schools[$cntr]->school_id)
			$schools_string = $schools_string . "\t\t\t<option selected value='" . $schools[$cntr]->school_id . "'>" . $schools[$cntr]->school_name . "</option>\n";
		else
			$schools_string = $schools_string . "\t\t\t<option value='" . $schools[$cntr]->school_id . "'>" . $schools[$cntr]->school_name . "</option>\n";		
	}
	$schools_string = $schools_string . "\t\t</select>\n\t</label>\n";
}
// ********** School(s) ********** //

$sql = "";
if ($card_type != "") {

	if ($school_id == -1 && $admin_user['auth'] == "super")
		$sql_where = "";
	else
		$sql_where = " WHERE (school_id=" . $school_id . ") "; 
		
	if ($subject_id > -1)
		$sql_and = " AND template.subject_id=" . $subject_id;
	else
		$sql_and = "";
		
		
	if ($card_type == "army")
		$sql = "SELECT * FROM army_templates AS template LEFT JOIN schools AS s USING (school_id) JOIN subjects USING (subject_id) " . $sql_where . $sql_and;
	else
		$sql = "SELECT * FROM base_templates AS template LEFT JOIN schools AS s USING (school_id) JOIN subjects USING (subject_id) " . $sql_where . $sql_and;
	
	$query = mysql_query($sql);	
}

$subjects_sql = "SELECT * FROM schools JOIN school_subjects USING (school_id) JOIN subjects USING (subject_id) WHERE school_id=" . $school_id;
$subjects_query = mysql_query($subjects_sql);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
	<HEAD>
		<TITLE><?=T_('Assign Cards'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		
		<script type="text/javascript">
			function get_missions() {
				subject_id = document.getElementById("subject_id").value;
				
				if (subject_id > -1) {
					var url = "ajax_get_missios.php?subject_id=" + subject_id;
					var http = getHTTPObject();
					http.open("GET", url, true);
					http.onreadystatechange = function() {
						if (http.readyState == 4 && http.status == 200) {
							document.getElementById("missions_div").innerHTML = http.responseText;
						}
					}
					http.send(null);
				}
			}
			
			function getHTTPObject() {
				var xmlhttp;

				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				}
				else if (window.ActiveXObject){ 
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
					
					if (!xmlhttp) {
						xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
					}
				}
				
				return xmlhttp; 
			} 
		
			function submit_card_type(card_type) {
				document.getElementById("card_type").value = card_type;
				document.card_form.submit();
			}			
		</script>
	</HEAD>
	
	<BODY>
	
		<? include('admin_header.php'); ?>
		
		<DIV CLASS="body">
			
			<H1>
				<?=T_('Assign Cards')?>
			</H1>
						
			<? if (!empty($message)) : ?>
				<H2><?=$message?></H2>
			<? endif; ?>
			
			

			
			<form name="card_form" id="card_form" action="admin_cards.php" method="post" accept-charset="UTF-8">					
						
				<input type='hidden' name='school_id' value="<?=$school_id;?>">
				<input type='hidden' name='subject_id' value="<?=$subject_id;?>">
				<input type='hidden' name='SQL' value="<?=$sql;?>">
				<input type="hidden" name="card_type" id="card_type" value="<?=$card_type;?>">								
				
				<input type="button" value="ARMY" onclick="submit_card_type('army');">
				<input type="button" value="BASE" onclick="submit_card_type('base');">
				
				<br />
				
				<?=$schools_string;?>
				
				<br />
				
				<? if (count($soldiers) == 1 && count($schools) == 1) { ?>
				<label>Soldier:<?=$soldiers[0]->first;?> <?=$soldiers[0]->last;?></label>
				<? } else { ?>
				<label>
					<select>
						<option value="-1"><?=T_('Select a Soldier');?></option>
						<? for ($cntr = 0; $cntr < count($soldiers); $cntr++) { ?>
						<option value="<?=$soldiers[$cntr]->user_id;?>"><?=$soldiers[$cntr]->first;?> <?=$soldiers[$cntr]->last;?></option>
						<? } ?>
					</select>
				</label>
				<? } ?>
								
				<?//=$soldiers_string;?>
			
				
				
				<br />
				
				<label>
					<select name="subject_id" id="subject_id" onchange="get_missions();">
						<option value="-1"><?=T_('Select a Subject');?></option>
						<? while ($row = mysql_fetch_assoc($subjects_query)) { ?>
						<option value="<?=$row['subject_id'];?>"><?=$row['subject_name'];?></option>
						<? } ?>
					</select>
				</label>

				<br />
				
				<div name="missions_div" id="missions_div">
				</div>
				
				<!--<label>
					<?//=T_('Army or Base')?>: 
					<select name="card_type" onchange="document.card_form.submit();">
						<option value="army"  <? //if ($card_type == "army") echo "selected"; ?>>Army</option>
						<option value="base" <? //if ($card_type == "base") echo "selected"; ?>>Base</option>
					</select>
				</label>-->
				
				
				<? if ($card_type == "army") { ?>
				<table class="list">
					<tr>
					  <th><?=T_('School')?></th>
					  <th><?=T_('Subject')?></th>
					  <th><?=T_('Points')?></th>
					  <th><?=T_('Left Circle')?></th>
					  <th><?=T_('Right Circle')?></th>
					  <th><?=T_('Description')?></th>
					  <th><?=T_('Series')?></th>
					  <th></th>
					  <th></th>
					</tr>				
					
					<? if ($sql != "") { ?>
						<? while ($row = mysql_fetch_assoc($query)) { ?>
					<tr>
						<? if ($row['school_name'] != "") { ?>
							<td><?=$row['school_name'];?></td>
						<? } else { ?>
							<td>All schools</td>
						<? } ?>
						<td><?=$row['subject_name'];?></td>
						<td><?=$row['points'];?></td>
						<td><?=$row['left_circle'];?></td>
						<td><?=$row['right_circle'];?></td>
						<td><?=$row['description'];?></td>
						<td><?=$row['series'];?></td>
						<td><a href="admin_card_assign.php?template_id=<?=$row['template_id'];?>">Assign Card</a></td>
						<td></td>
					</tr>
						<? } ?>
					<? } ?>
				</table>
				<? } ?>
				
			</form>
			
			<? include('admin_footer.php'); ?>
			
		</DIV> <!-- body -->
				
	</BODY>
	
</HTML>
