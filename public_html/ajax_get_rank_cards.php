<?
include("db.php");
include("calendar.php");

$school_id = $_GET['school_id'];
$class_id = $_GET['class_id'];
$user_id = $_GET['user_id'];
$rank_ord = $_GET['rank_ord'];

$sql = "SELECT u.*, s.*, c.class_grade, c.class_sub, r.rank_name, r.rank_color ";
$sql = $sql . "FROM users AS u ";
$sql = $sql . "JOIN schools AS s USING (school_id) ";
$sql = $sql . "JOIN classes AS c USING (class_id) ";
if ($user_id > 0)
{
	$sql = $sql . "WHERE u.user_id=" . $user_id;
}
else
{
	if ($rank_ord > 0)
		$sql = $sql . "JOIN rank_marks AS rm ON (rm.user_id=u.user_id AND rm.rank_ord=" . $rank_ord . ") ";
	else
		$sql = $sql . "JOIN rank_marks AS rm ON (rm.user_id=u.user_id) ";
	
	$sql = $sql . "JOIN ranks AS r ON (r.rank_ord=rm.rank_ord) ";
	
	if ($school_id > 0)
	{
		$sql = $sql . "WHERE u.school_id=" . $school_id . " ";
		if ($class_id > 0)
			$sql = $sql . "AND u.class_id=" . $class_id . " ";
		
	}
	else
	{
		if ($class_id > 0)
			$sql = $sql . "WHERE u.class_id=" . $class_id . " ";
	}	
}
$sql = $sql . "ORDER BY s.school_name, s.school_city, s.school_state, c.class_grade, c.class_sub, u.last, u.first, r.rank_ord";

$query = mysql_query($sql);
$row_num = 0;

function es($string) 
{
	return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>

<TABLE class="card_sheet">

	<? while ($row = mysql_fetch_assoc($query)) : ?>
	<? $row_num++; ?>

	<? if ($row_num % 2 == 1) : ?>
	<tr>
	<? endif; ?>
	
		<td class="card">
					
			<div style="background-color:<?=$row['rank_color'];?>;" class="top">
				This card entitles the cardholder to TH privileges with the <?=$row['rank_name'];?> rank 
			</div>
						
			<div style="border-color:<?=$row['rank_color'];?>;" class="sig">
				Authorized Signature							
				<h1>
					<em style="color:<?=$row['rank_color'];?>;"><?=$row['rank_name'];?></em>
					<? if ($row['first_he'] != "" && $row['last_he'] != "") : ?>
					<?=es($row['first_he']);?> <?=es($row['last_he']);?>
					<? else : ?>
					<?=$row['first'];?> <?=$row['last'];?>
					<? endif; ?>
				</h1>
				SERIAL # <?=$row['user_serial'];?>
			</div>
						
			<img alt="user_card_bg_private.jpg" class="bg" src="/file_view.php?id=<?=$row['user_photo_id'];?>">
			
			<div class="panel">
			
				<table cellspacing="0" cellpadding="0">
				
					<tbody>
					
						<tr>
						
							<td width="1" class="logo">
								<img alt="BCMT-Logo09.png" src="/file_view.php?id=<?=$row['school_logo_id'];?>">							
							</td>
							
							<td class="base">
								<p>
									BASE # <?=$row['school_number'];?><br>
									<b><?=$row['school_name'];?></b><br>
									<?=$row['school_city'];?>, <?=$row['school_state'];?>										
								</p>
							</td>
							
							<td width="1" class="photo">
							</td>
							
						</tr>
						
					</tbody>
					
				</table>
				
				<table>
				
					<tbody>
					
						<tr>
						
							<td class="code">
							
								<p>
									<img alt="" src="barcode.php/35968003152367741952">
									<br>
									35968003152367741952
								</p>
								
							</td>
							
							<td class="stats">
								<p>
									Member since<br><span dir="rtl"><?=dateToHebrewShortYear($row['user_start_date'])?></span>
								</p>
								<p>
<?
									if ($bdate = dateToJD($row['dob'])+$row['dob_he_offset']) 
									{
										$cal = cal_from_jd($bdate, CAL_JEWISH);
										$valid = 'Valid until' . '<BR><SPAN dir="rtl">' . dateToHebrewShortYear(cal_to_jd(CAL_JEWISH, 13, cal_days_in_month(CAL_JEWISH, 13, $cal['year']+13), $cal['year']+13)) . '</SPAN>';
									} 
									else 
									{
										$valid = '';
									}
?>
											<?=$valid?>
								</p>
							</td>
									
						</tr>
						
					</tbody>
					
				</table>
				
			</div>

		</td>	
		 
	<? if ($row_num % 2 == 0) : ?>		 
	</tr>
	<? endif; ?>
	
	<? endwhile; ?>		
	
</TABLE>
