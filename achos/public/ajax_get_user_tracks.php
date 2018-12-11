<?
include("db.php");

$school_id = $_GET['school_id'];

$subject_id = 0;
if (isset($_GET['subject_id']))
	$subject_id = $_GET['subject_id'];

include("classes/track.php");
$tracks = array();
$sql = "SELECT * FROM tracks ORDER BY track_id";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query))
{
	$track = new track($row);
	array_push($tracks, $track);
}

include("classes/user.php");
include("classes/subject.php");
include("classes/user_track.php");
$users = array();
$sql = "SELECT * FROM users WHERE school_id=" . $school_id . " ORDER BY last, first";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query))
{
	$user = new user($row);
	$user->get_class();
	$user->get_all_subjects($subject_id);
	
	foreach ($user->subjects as $subject) 
	{
		$subject->get_user_track($user->user_id);
	}
	
	array_push($users, $user);	
}
?>

<table class="list list_left">

	<thead>
		<tr>
			<th>Soldier</th>
			<th>Subject</th>  
			<th>Ladder</th>
			<th>Year(3-14)</th>
			<th>Enrolled?</th>
		</tr>
	</thead>
	
	<tbody>
	
		<tr>
			<th></th>
			<th></th>
			<th>
				<select name="track_id_all" id="track_id_all">
					<option value="0">Subject Disabled</option>
					<? foreach ($tracks as $track) : ?>
					<option value="<?=$track->track_is;?>"><?=$track->track_name;?></option>
					<? endforeach; ?>
				</select>
			</th>
			<th>
				<input type="text" size="2" maxlength="2" name="level_all">
				<a href="JavaScript:void(0);">+</a>
				<a href="JavaScript:void(0);">-</a>
			</th>
			<th></th>
		</tr>
		
	<? foreach ($users as $user) :?>
		
		<? foreach ($user->subjects as $subject) :?>
		
			<? if ($subject->user_track->enrolled != 1) $disabled = " DISABLED "; else $disabled = ""; ?>
		
		<tr name="<?=$subject->subject_id;?>" id="<?=$subject->subject_id;?>">
			<td name="user_id" id="user_id" data="<?=$user->user_id;?>">
			
				<? if ($user->class_sub == "") : ?>
				<?=$user->class_grade;?> <?=$user->first;?> <?=$user->last;?>
				<? else : ?>
				<?=$user->class_grade;?> - <?=$user->class_sub;?> <?=$user->first;?> <?=$user->last;?>
				<? endif; ?>
				
			</td>
			
			<td>
				<?=$subject->subject_name;?>
			</td>  
			
			<!-- ***** TRACK ID ***** -->
			<td>
				<select <?=$disabled;?> name="track_id" id="track_id" data="<?=$user->user_id;?>">	
					<!--<option value="0">Subject Disabled</option>-->
					<? foreach ($tracks as $track) : ?>
						<? if ($subject->user_track->track_id == $track->track_id) : ?>
						<option SELECTED value="<?=$track->track_id;?>"><?=$track->track_name;?></option>
						<? else : ?>
						<option value="<?=$track->track_id;?>"><?=$track->track_name;?></option>
						<? endif; ?>
					<? endforeach; ?>
				</select>
			</td>
			<!-- ***** TRACK ID ***** -->
			
			<!-- ***** LEVEL ***** -->
			<td style="text-align: center;">
			
				<SELECT <?=$disabled;?> id="level" name="level" data="<?=$user->user_id;?>">
					<? for ($level = 3; $level < 15; $level++) : ?>
						<? if ($subject->user_track->level == $level) : ?>
						<OPTION SELECTED value="<?=$level;?>"><?=$level;?></OPTION>
						<? else : ?>
						<OPTION value="<?=$level;?>"><?=$level;?></OPTION>
						<? endif; ?>
					<? endfor; ?>
				</SELECT>
				
			</td>
			<!-- ***** LEVEL ***** -->
			
			<!-- ***** ENROLLED ***** -->
			<td>
				<? if ($subject->user_track->enrolled == 1) : ?>
				<input CHECKED type="checkbox" name="enrolled" id="enrolled">
				<? else : ?>
				<input type="checkbox" name="enrolled" id="enrolled">
				<? endif; ?>
			</td>
			<!-- ***** ENROLLED ***** -->
			
		</tr>
		<? endforeach; ?>
		
	<? endforeach; ?>
	
	</tbody>
	
</table>