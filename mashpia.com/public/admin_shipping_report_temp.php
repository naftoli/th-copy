<?php
$admin_auth = array(); 	
require('header.php');

/*
	create shipping report that calculates the following:
	number of new kids
	number of old kids

	for each new kid give:
	sticker book
	1st set of sticker boards
	1 pack of scratch-off cards

	for each old kid give:
	sticker book
	2nd set of sticker boards
	1 pack of scratch-off cards
*/

function createReport($type) {
	
	?>
	<p>
	<? if ($type == 'new') {
			$qry = "select printed from shipping_report order by printed desc limit 1";
			$res = mysql_query($qry);
			$row = mysql_fetch_assoc($res);
			echo "Last Printed Report: " . $row['printed'];
		}
	?>
	</p>
	<table border='1' cellspacing='1' cellpadding='3'>
	<tr>
		<th>&nbsp;</th>
		<th>Shipping</th>
        <th>Sticker Books <span class='red'>*</span></th>
        <th>Sticker Board Binder <span class='red'>**</span></th>
        <th>2nd Set <span class='red'>***</span></th>
        <th>Charge cards <span class='red'>*</span></th>
	<?
	$add_ons = array();
	$s = "select year from school_add_ons group by year desc limit 1";
	$r = mysql_query($s);
	$y = mysql_fetch_row($r);
	$year = $y[0];
	$sel = "select title, needs_size from school_add_ons where year = $year order by school_add_on_id";
	$res = mysql_query($sel);
	while ($row = mysql_fetch_row($res)) {
		$add_ons[$row[0]] = $row[1];
	}
	foreach ($add_ons as $k => $v) {
	    if ($k == 'Album') 
            $k = 'Album and Rebbe pictures'; 
		echo "<th>$k</th>";
	}
	echo "</tr>";

	//get schools
	$schools = array();
	$sql = "select school_id, school_name, shipping_method 
		from schools 
		where school_era is null 
		and school_id not in (82,173,78) 
		order by school_name";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$schools[$row['school_id']]['name'] = $row['school_name'];
		$schools[$row['school_id']]['shipping'] = $row['shipping_method'];
	}
	
	//variable to find children added to system this year
	$jd = 2456171; //August 31, 2012
	$date = null; //initialize date
	
	if ($type == 'new') {
		$qry = "select printed from shipping_report order by printed desc limit 1";
		$res = mysql_query($qry);
		$row = mysql_fetch_assoc($res);
		$date = $row['printed'];
	}
	
	foreach ($schools as $k => $v) {
		
		if ($type == 'all') {
			//get new kids
			$sql1 = "
				SELECT u.user_id 
				FROM users AS u, schools AS s
				WHERE u.school_id = s.school_id
				AND s.school_id = $k  
				AND u.user_registered > 0 
				AND (u.user_start_date > $jd or u.user_start_date is null)";
			//if ($k == 54) echo $sql1;
			$result1 = mysql_query($sql1);
			$new = mysql_num_rows($result1);
			
			//get old kids
			$sql2 = "
				SELECT u.user_id 
				FROM users AS u, schools AS s
				WHERE u.school_id = s.school_id
				AND s.school_id = $k  
				AND u.user_registered > 0 
				AND u.user_start_date < $jd";
			//if ($k == 54) echo $sql2;
			$result2 = mysql_query($sql2);
			$old = mysql_num_rows($result2);
		}
		
		else if ($type == 'new') {
			//get new kids 
			$sql1 = "
				SELECT u.user_id 
				FROM users AS u, schools AS s
				WHERE u.school_id = s.school_id
				AND s.school_id = $k  
				AND u.user_registered > '$date'  
				AND (u.user_start_date > $jd or u.user_start_date is null) ";
			//echo $sql1;
			$result1 = mysql_query($sql1);
			$new = mysql_num_rows($result1);
			
			//get old kids
			$sql2 = "
				SELECT u.user_id 
				FROM users AS u, schools AS s
				WHERE u.school_id = s.school_id
				AND s.school_id = $k  
				AND u.user_registered > '$date' 
				AND u.user_start_date < $jd";
			//echo $sql2;
			$result2 = mysql_query($sql2);
			$old = mysql_num_rows($result2);
		}

		?>
		<tr>
			<td align='left'>
			<a href="
			admin_shipping_report_detail.php?id=<?=$k?>&type=<?=$type?>&date=<?=urlencode($date)?>&old=<?=$old?>&new=<?=$new;?>
			"><?=$v['name'];?>
			</a></td>
			<td>&nbsp;<?=$v['shipping'];?></td>
			<td>&nbsp;<?=$new + $old;?></td>
			<td>&nbsp;<?=$new;?></td>
			<td>&nbsp;<?=$old;?></td>
			<td>&nbsp;<?=$new + $old;?></td>
		<?
		$sizes = array();
		foreach ($add_ons as $j => $m) { 
			$sizes[$j]['noSize'] = 0;
			echo "<td>&nbsp;";
			if ($m == 1) {
				$sql = "SELECT size, count( size ) AS total
						FROM user_add_ons AS ua, school_add_ons AS sa, users AS u, schools AS s
						WHERE ua.school_add_on_id = sa.school_add_on_id
						AND u.user_id = ua.user_id
						AND s.school_id = u.school_id
						AND s.school_id = $k
						AND sa.title = '$j'
						AND u.user_registered > 0 ";
				if ($type == 'new') $sql .= "AND u.user_registered > '$date' ";
				$sql .= "GROUP BY size";
			} else { 
				$sql = "SELECT count( title ) AS total
						FROM user_add_ons AS ua, school_add_ons AS sa, users AS u, schools AS s
						WHERE ua.school_add_on_id = sa.school_add_on_id
						AND u.user_id = ua.user_id
						AND s.school_id = u.school_id
						AND s.school_id = $k
						AND sa.title = '$j'
						AND u.user_registered > 0 ";
				if ($type == 'new') $sql .= "AND u.user_registered > '$date'";
			}
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				if ($m == 1) {
					echo strtoupper($row['size']) . ":$row[total] ";
					$sizes[$j][$row['size']] = $row['total'];
				} else {
					echo "$row[total]";
					$sizes[$j]['noSize'] += $row['total'];
				}
			}
			echo "</td>";
		}
		?>
		</tr>
	<? } ?>
	</table>
<? 
//print_r($sizes);
} 
function createForm() {
?>
<form action="<?=$_SERVER['PHP_SELF'];?>" method='post'>
<input type='checkbox' name='printed'>Set report as printed <input type='submit' name='print' value='submit'>
</form>
    <p class='red'>* This is for ALL chayolim that registered this year.<br />
    ** This is for FIRST TIME registered chayolim<br />
    *** This is only for chayolim that registered in 5772</p>
<?
}
?>

<html>
<head>
    <style>
        body, table {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }
        .red {
            color: red;
        }
    </style>
</head>
<body>

<?
if (isset($_POST['submit']) && isset($_POST['report'])) { 
	createForm();
	$type = $_POST['report'];
	createReport($type);
} else if (isset($_POST['print'])) {
	$qry = "insert into shipping_report values (null, now())";
	if (mysql_query($qry)) {
		echo "Report set to printed. When you create a new report it will show you new children registered as of tomorrow.";
	} else {
		echo mysql_error();
	}
} else { 
?>
<form action="<?=$_SERVER['PHP_SELF'];?>" method='post'>
Choose what type of report you would like to generate:<br />
<input type='radio' name='report' value='all'>First Time Report (includes all children that registered for this year)<br />
<input type='radio' name='report' value='new'>New children that registered since last report<br />
<input type='submit' name='submit' value='submit'>
</form>
<? } ?>
</body>
</html>