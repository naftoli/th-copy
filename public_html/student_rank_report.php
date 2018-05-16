<?php
//  student rank report - upgrades
$admin_auth = array(); 	
require('header.php'); 
//error_reporting( E_ERROR | E_USER_ERROR | E_WARNING );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?=$dir?>">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title></title>
		<link rel="alternate" media="print" href="index.php">
		<script src="jquery.js" type="text/javascript"></script>
		<script src="kiosk/scripts/jquery.core.js" type="text/javascript"></script>
		<script src="kiosk/scripts/jquery.ui.js" type="text/javascript"></script>
		<script src="kiosk/scripts/jquery_date_time/jquery.dynDateTime.min.js" type="text/javascript"></script>
		<script src="kiosk/scripts/jquery_date_time/lang/calendar-en.js" type="text/javascript"></script>
		<link rel="stylesheet" type="text/css" media="all" href="kiosk/scripts/jquery_date_time/css/calendar-win2k-cold-2.css"  />
		<link href="admin_styles.css" rel="stylesheet" type="text/css" />				
		<div id="wrapper">
		<NOSCRIPT><P STYLE="color: red; font-size: larger;">Notice: You have javascript disabled. Some parts of the site will not function without javascript.</P></NOSCRIPT>
		
	</head>
	<style>
	
	body{
		margin: 20px;	
		text-align:left;
	}
	
	#wrapper{
		padding: 15px;
	}
	
	table td, th{		
		padding: 3px;
	}
	.align_right{		
		text-align:right;
	}
	th{
		font-weight:bold
	}
	.small
	{
		font-size: 10px;
	}
	
	.student{
		background-color: #9cd4fb;
		font-size: 12px;
		border: 1px solid black;		
	}


	.product{
		background-color: #c3e3f9;
		font-size: 12px;
	}

	.begin_totals
	{
		margin-top:30px;
	}
	
	span .input{
		width:100px 
	}
	
	.school {
		color: blue;
		font-weight: bold;
	}
	
	.rank {
		color: red;
		font-weight; bolder;
	}
	
	</style>

	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery("#dateTimeCustom_from").dynDateTime({
				showsTime: true,
				ifFormat: "%Y-%m-%d",
				daFormat: "%l;%M %p, %e %m,  %Y"
			});
		
			jQuery("#dateTimeCustom_to").dynDateTime({
				showsTime: true,
				ifFormat: "%Y-%m-%d",
				daFormat: "%l;%M %p, %e %m,  %Y"
			});

			$('#submit').click(function() {
				date_from = $('#dateTimeCustom_from').val();
				date_to = $('#dateTimeCustom_to').val();
				var from_Date = new Date(date_from);
				var to_Date = new Date(date_to);				
			});
		});
		
	</script>

<?php	
// get passed in variables
$from = isset($_POST['dateTimeCust_from']) 	? $_POST['dateTimeCust_from'] : ''; 
$to = isset($_POST['dateTimeCust_to']) 	? $_POST['dateTimeCust_to'] : ''; 

// from day
if($from<>'') $default_from = $from;
else $default_from = 'YYYY/MM/DD';

// to day
if($to<>'') $default_to = $to;
else  $default_to = 'YYYY/MM/DD';

/*
//array to hold abbreviations
$schools = array(
    'Bais Chaya Mushka IA'              =>  'BCM Iowa', 
    'Bais Chaya Mushka LA'              =>  'BCM Los Angeles',
    'Bais Chaya Mushka Panama'          =>  'BCM Panama',  
    'Bais Chaya Mushka Toronto'         =>  'BCM Toronto', 
    'Beis Chaya Mushka Crown Heights'   =>  'BCM Crown Heights', 
    'Beis Rivkah Crown Heights'         =>  'BR Crown Heights', 
    'Beth Rivkah Montreal'              =>  'BR Montreal', 
    'Bnos Menachem Crown Heights'       =>  'Bnos Menachem CH', 
    'Chabad Hebrew School Lake Grove'   =>  'Cheder Lake Grove', 
    'Chabad Youth Boys'                 =>  'CY Melbourne Boys', 
    'Chabad Youth Girls'                =>  'CY Melbourne Girls', 
    'Chabad Youth Club Ohio'            =>  'CYC Ohio', 
    'Chassidus Club New Haven'          =>  'Chassidus Club CT', 
    'Cheder at the Ohel'                =>  'Cheder at the Ohel', 
    'Cheder Chabad Baltimore'           =>  'Cheder Baltimore', 
    'Cheder Chabad of Monsey'           =>  'Cheder Monsey', 
    'Cheder Chabad of Philadelphia'     =>  'Cheder Philadelphia', 
    'Cheder Chabad Sydney Australia'    =>  'Cheder Sydney', 
    'Cheder Chabad Toronto'             =>  'Cheder Toronto', 
    'Cheder Menachem LA'                =>  'Cheder Menachem LA', 
    'Cheder Lubavitch Morristown Boys'  =>  'Cheder Morristown Boys', 
    'Cheder Lubavitch Morristown Girls' =>  'Cheder Morristown Girls', 
    'Cheder Lubavitch Chicago Boys'     =>  'CLHDS Chicago Boys', 
    'Cheder Lubavitch Chicago Girls'    =>  'CLHDS Chicago Girls', 
    'Cheder Menachem NJ'                =>  'Cheder Menachem NJ', 
    'Hebrew Academy Community School Margate Florida'   =>  'HACS Margate', 
    'Hillel Academy'                    =>  'Hillel Milwaukee', 
    'Kesser Torah College'              =>  'KTC Sydney', 
    'Lamplighters Yeshivah'             =>  'Lamplighters CH', 
    'London Lubavitch Boys'             =>  'Lubavitch London Boys', 
    'Lubavitch Cheder Day School Minnesota' =>  'Lubavitch Cheder MN', 
    'Lubavitch Educational Center Florida Boys' =>  'LEC Florida Boys', 
    'Lubavitch Educational Center Florida Girls'    =>  'LEC Florida Girls', 
    'Lubavitch House Boys Junior School'    =>  'Lubavitch House Boys', 
    'Lubavitcher Yeshiva - Crown Heights' =>  'ULY Crown Heights', 
    'MyShliach Tzivos Hashem for Yaldei Hashluchim' =>  'MyShliach', 
    'Ohr Temimim Buffalo'               =>  'Ohr Temimim Buffalo', 
    'Torah Day School of Houston'       =>  'TDS Houston', 
    'Yeshiva Darchai Menachem Crown Heights New York'   =>  'Darchei Menachem CH', 
    'Yeshiva Tomche Temimim Lubavitch Montreal' =>  'YTTL Montreal'
);
*/
?>	
<h2>Mashpia - Rank Upgrade Report</h2>
<a href='admin.php'>Back</a>
<br/>

	<div id="wrapper">	
	<h3>Rank upgrade by date</h3>
	<br/>
	<form action="" method="post" >
	<table><tr>
		<td>From Date/Time: </td><td><span class="box"><input class="input" type="text" name="dateTimeCust_from" id="dateTimeCustom_from" value='<?=$default_from?>'/>	</span>	</td>
		</tr><tr>
		<td>To Date/Time: </td><td> <span class="box"><input  class="input" type="text" name="dateTimeCust_to" id="dateTimeCustom_to" value='<?=$default_to?>'/></span></td>
		</tr>
		<td>
		
		</td>
		<td><input type="submit" name="submit" id="submit"/></td>
	</form>
	</table>
	
<?php	

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// get all students who have changed rank with period requested
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

if($from <> '') 
{
	$fromArr = explode("-", $from);
	$from = gregoriantojd($fromArr[1], $fromArr[2], $fromArr[0]);
	$toArr = explode("-", $to);
	$to = gregoriantojd($toArr[1], $toArr[2], $toArr[0]);
	
	// fake user accounts created for tehillim/tanya purposes
	$userExceptions = array(
		18575,
		18576,
		18577,
		18578,
		18581,
		18583,
		18584,
		18586,
		18588,
		18589,
		18590,
		18592,
		18593,
		18595,
		18596,
		18618,
		18619,
		18639,
		18640,
		18848,
		18849,
		18850,
		18852,
		18853,
		18854,
		18855,
		18856,
		18857,
		18858,
		18859,
		18907,
		18908,
		18909,
		18910,
		18911,
		21565,
		21566,
		21567,
		21568,
		21569,
		21570
	);
	
	$sql = "SELECT "
		. "s.school_name, "
		. "s.hachayol_name as nickname, "
		. "u.first, "
		. "u.last, "    
		. "k.rank_name, "
		. "r.rank_ord "
		. "FROM rank_marks r "
		. "JOIN users u on u.user_id = r.user_id "
		. "JOIN ranks k on k.rank_ord = r.rank_ord "
		. "JOIN schools s on u.school_id = s.school_id "
        . "WHERE r.date_promoted > $from and r.date_promoted < $to "
        . "AND r.rank_ord > 1  "
		. "and s.test_school = 0 "
		. "and s.chayolei = 1 "
		. "and u.user_id not in (" . implode(',', $userExceptions) . ") "
        . "order by k.rank_ord desc, s.school_name, u.last, u.first";
		
	echo "<input type='hidden' name='query' value='" . $sql . "'>";
	$query = mysql_query($sql);	
	
	$prev_school_name = "";
	$prev_rank_name = "";
	
	$ranks = array();
	$totals = array();
	while ($row = mysql_fetch_assoc($query)) {
		// On new rank, show new rank header
		if($row['rank_name'] != $prev_rank_name)			
			echo "<br /><span class='rank'>" . $row['rank_name'] . "</span><br />";

		// On new school, show new school header
		if ($row['rank_ord'] < 9) {
			if($row['school_name'] != $prev_school_name)
				echo "<span class='school'>" . 
				 ($row['nickname'] ? $row['nickname']  : $row['school_name']) . 
				 "</span><br />";
		}

		// store current school/rank in temporary "hold" variables
		$prev_school_name  = $row['school_name'];
		$prev_rank_name	   = $row['rank_name'];
		
		// show name
		// show first name initials
		$first = ucwords(strtolower($row['first']));
		if ($row['rank_ord'] < 9) {
			$pos = strpos( $first, ' ' );
			if ( $pos ) { 
				$firstArr = explode( ' ', $first );
				$first = '';
				foreach ( $firstArr as $str ) {
					$first .= substr( $str, 0, 1 );
				}
			} else {
				$first = substr( $first, 0, 1 );
			}
		}
		echo $first . " " . ucwords(strtolower($row['last'])) . "<br />";
		if ($row['rank_ord'] > 8) {
			echo "<span class='school'>" . 
				 ($row['nickname'] ? $row['nickname']  : $row['school_name']) . 
				 "</span><br />";
		}
		
		// update totals
		if (isset($totals[$row['rank_ord']])) $totals[$row['rank_ord']]++; 
		else $totals[$row['rank_ord']] = 1;
		if (!isset($ranks[$row['rank_ord']])) $ranks[$row['rank_ord']] = $row['rank_name'];
	}
	
	echo "<h2>Totals</h2>";
	foreach ($totals as $rank => $num) {
		echo $ranks[$rank] . " - " . $num . "<br />";
	}
	
	/*
	$sql = "SELECT "
		. "s.school_name, "
		. "u.first, "
		. "u.last, "    
		. "k.rank_name,  "
		. "k.rank_ord,  "
		. "r.user_id,  "
		. "r.date_promoted, "
		. "r.date_printed,  "
		. "r.date_book_received,  "
		. "r.date_card_received,  "
		. "u.user_city "
		. "FROM rank_marks r "
		. "JOIN users u on u.user_id = r.user_id "
		. "LEFT JOIN ranks k on k.rank_ord = r.rank_ord "
		. "LEFT JOIN schools s on u.school_id = s.school_id "
        . "WHERE r.date_promoted > $from and r.date_promoted < $to "
        . "AND r.rank_ord > 1  "
        . "order by k.rank_ord, s.school_name, u.last, u.first";
	
	$query = mysql_query($sql);	
		
		echo "<table class='student'>"; 
		echo "<tr>";
		echo "<th>Rank Name</th>";
		echo "<th>School Name</th>";
		echo "<th>Full Name</th>";	
		echo "<th>City</th>";	
		echo "<th>Date Promoted</th>";	
		echo "<th>ect</th>";
		
		echo "</tr>";
		
		$prev_school_name = "";
		$prev_rank_name = "";
	
	 while($row = mysql_fetch_assoc($query)){
	 	
		// convert date from julian to gregorian for comparison
		//$row['date_promoted'] = date('Y-m-d', strtotime(jdtogregorian($row['date_promoted'])));	
		
		if ($row['date_promoted'] >= $from && $row['date_promoted'] <= $to)
		{
			echo "<tr  class='student'>";		
			
			// On new rank, show new rank header
			if($row['rank_name'] == $prev_rank_name)			
				{ echo "<td>" . " " . "</td>"; }
			else
				{ echo "<td>" . $row['rank_name'] . "</td>"; }

			// On new school, show new school header
			if($row['school_name'] == $prev_school_name)
				{ echo "<td>" . " "  . "</td>"; }
			else
				{ echo "<td>" . $row['school_name'] . "</td>"; }

			// store current school/rank in temporary "hold" variables
			$prev_school_name  = $row['school_name'];
			$prev_rank_name	   = $row['rank_name'];
			
			// show report details
			echo "<td>" . strtoupper(substr($row['first'],0,1)) . ". " .  $row['last'] . "</td>";		
			echo "<td>" . $row['user_city'] . "</td>";
			echo "<td>" . $row['date_promoted'] . "</td>";
			echo "<td>" . $row['first'] . "/";		
			echo " " . $row['last'] . "/";
			echo " " . $row['rank_name'] . "/";
			echo " " . $row['school_name'] . "/";
			echo " " . $row['rank_ord'] . "/";				
			echo "</tr>";
		}
		
		//if ($row['date_promoted'] >= $from && $row['date_promoted'] <= $to)
		{			
			// On new rank, show new rank header
			if(!$row['rank_name'] == $prev_rank_name)			
				echo "<br /><b>" . $row['rank_name'] . "</b><br />";

			// On new school, show new school header
			if(!$row['school_name'] == $prev_school_name)
				echo "<br />" . $row['school_name'] . "<br />";

			// store current school/rank in temporary "hold" variables
			$prev_school_name  = $row['school_name'];
			$prev_rank_name	   = $row['rank_name'];
			
			// show report details
			echo $row['first'] . " " . $row['last'] . "<br />";		
		
		}
	}
	 * 
	 */
}
?>