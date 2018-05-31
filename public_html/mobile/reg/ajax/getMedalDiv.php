<?
require '../../../db.php';

$campaignLogos = array(
	1	=>	'Tehillim.gif',
	4	=>	'Tefilla.gif',
	12	=>	'Mivtzoim.gif',
	13	=>	'Niggunim.gif',
	16	=>	'hiskashrus.gif',
	21	=>	'sefer-hamitzvos.gif',
	27	=>	'tanya.gif',
	40	=>	'Yom-Dipagra.gif',
	41	=>	'Father-Son.gif',
	42	=>	'Footsteps.gif',
	45	=>	'Cheshbon-Hanefesh.gif',
	90	=>	'Chitas.gif',
	100	=>	'Brias-Haguf.gif'
);

$start = 2457277;
$end = 2457661;
$user_id = mysql_real_escape_string( $_POST['user'] );
$id = mysql_real_escape_string( $_POST['id'] );

$sql = "SELECT first, last, user_photo_id, lang_id, ut.track_id, ut.level FROM users u 
		JOIN user_tracks ut USING (user_id)	
		WHERE ut.subject_id = 1 
		AND user_id = " . $user_id;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$photo = $row['user_photo_id'];
$first = $row['first'];
$lang = $row['lang_id'];
$ladder = $row['track_id'];
$level = $row['level'];

$qry = "SELECT qty, minutes FROM tehillim_ladders WHERE ladder = " . $ladder . " AND age = " . $level;
$res = mysql_query($qry);
$r = mysql_fetch_assoc($res);

$year = 5776;

$sql = "SELECT * FROM subjects WHERE subject_id = " . $id;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
?>
<div class="panel panel-default">
	<a data-toggle="collapse" href="#collapse1">
		<div class="panel-heading">
			<i class="glyphicon glyphicon-chevron-right"></i> <?=$row['subject_name']?>
		</div>
	</a>
	<div class="collapse" id="collapse1">
		<div class='alert alert-warning' role='alert' style="margin-bottom: 0 !important;">
			<div class='media'>
				<div class='media-left'>
					<img class='media-object' width="50px;"
						src="/mission_report/campaignLogos/<?=$campaignLogos[$id]?>" alt='Camapign Logo'>
			  	</div>
			  	<div class="media-body">
			  		<?
			  		$str = "";
        		switch ( $id ) {
					case 1:
						$str = "<b>Shabbos Mevorchim Tehillim Mission</b><br />
								You can earn one shabbos mevorchim tehillim mission each month, 
								by saying tehillim on shabbos mevorchim.";
						break;
					case 4:
						$str = "<b>Teffila Mission</b><br />
You can earn up to one teffila mission each week, by <b>davening in shul on shabbos </b>.";
						break;
					case 12:
						$str = "<b>Mivtzoim</b><br />
You can earn one mivtzoim mission each month, by <b>doing the monthly mivtza</b>.";
						break;
					case 13:
						$str = "<b>Niggunim Mission</b><br />
You can earn up to one niggunim mission each week by <b>singing the weekly niggunim at your shabbos table</b>.";
						break;
					case 16:
						$str = "<b>Hiskashrus Mission</b><br />
You can earn up to one hiskashrus mission each week, by <b>watching a video of the Rebbe</b>.";
						break;
					case 21:
						$str = "<b>Sefer Hamitzvos Mission</b><br />
You can earn one sefer Hamitzvos mission each week by <b>learning the daily shiur of sefer hamitzvos</b>
 at least five out of seven times throughout the week.";
						break;
					case 27:
						$str = "<b>Tanya Baal Peh</b><br />
You can earn one tanya baal peh mission each week, by <b>learning tanya baal peh for 5 minutes a day</b> at least five out of seven times throughout the week.";
						break;
					case 40:
						$str = "<b>Yomei Depagra Mission</b><br /> 
You can earn one Yomei depagra mission each special day of the year, by <b>completing all the mandatory tasks on that days mission</b>.";
						break;
					case 41:
						$str = "<b>Avos Ubonim Mission</b><br /> 
You can earn up to one avos ubonim mission each week, by <b>learning with a parent or grandparent</b>.";
						break;
					case 45:
						$str = "<b>Cheshbon Hanefesh Mission</b><br /> 
You can earn up to one cheshbon hanefesh mission each week, by <b>saying krias shema from a siddur</b> at least five out of seven times throughout the week.";
						break;
					case 90:
						$str = "<b>Chitas Mission</b><br />
You can earn up to one chitas mission each week, by <b>saying the daily Tehillim</b> at least five out of seven times throughout the week.";
						break;
					case 100:
						$str = "<b>Brias Haguf Mission</b><br /> 
You can earn one brias haguf mission each week by being in bed on time at least five out of seven times throughout the week.";
						break;
        		}
				echo $str;
        		?>        
			  	</div>
			</div>
		</div>
    </div>
</div>