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
	<table>
	<tr>
		<th rowspan=2>&nbsp;</th>
		<th rowspan=2>Shipping</th>
        <th rowspan=2>Sticker Sheets <span class='red'>*</span></th>
        <th rowspan=2>Sticker Boards and Green Binder <span class='red'>**</span></th>
        <th rowspan=2>2nd Set of Sticker Boards <span class='red'>***</span></th>
        <th rowspan=2>Sets of Charge cards <span class='red'>*</span></th>
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
		switch ($k) {
			case 'Album':
				$k = 'Album and Rebbe pictures';
				echo "<th rowspan=2>$k</th>";
				break;
			case 'Tehillim': 
				echo "<th rowspan=2>$k</th>";
				break;
			case 'Sweatshirt':
				echo "<th colspan=4>$k</th>";
				break;
			case 'Cap':
			case 'Yarmulka':
				echo "<th colspan=2>$k</th>";
				break;
            case 'Siddur':
            case 'Backpack':
                echo "<th rowspan=2>$k</th>";
                break;
		}
	}
	echo "</tr>";
	echo "<tr><th>S</th><th>M</th><th>L</th><th>XL</th>";
	echo "<th>S</th><th>L</th>";
	echo "<th>4</th><th>5</th></tr>";
	
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
	$jd = 2456538; //September 3, 2013
	$date = null; //initialize date
	$jdBeg = 2455804; //beginning of last year for 2nd set
    $jdEnd = 2456104; //end of last year for 2nd set
	
	if ($type == 'new') {
		$qry = "select printed from shipping_report order by printed desc limit 1";
		$res = mysql_query($qry);
		$row = mysql_fetch_assoc($res);
		$date = $row['printed'];
	}
	
    //array to be able to print all schools in one shot
    $schoolsToPrint = array();
        
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
            
            //get kids registered last year only (not before)
            $sql3 = "
                SELECT u.user_id 
                FROM users AS u, schools AS s
                WHERE u.school_id = s.school_id
                AND s.school_id = $k  
                AND u.user_registered > 0 
                AND u.user_start_date > $jdBeg 
                AND u.user_start_date < $jdEnd";
            //if ($k == 54) echo $sql3;
            $result3 = mysql_query($sql3);
            $lastYear = mysql_num_rows($result3);
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
			
			//get kids registered last year only (not before)
            $sql3 = "
                SELECT u.user_id 
                FROM users AS u, schools AS s
                WHERE u.school_id = s.school_id
                AND s.school_id = $k  
                AND u.user_registered > 0 
                AND u.user_start_date > $jdBeg 
                AND u.user_start_date < $jdEnd 
                AND u.user_registered > '$date'";
            //echo $sql3;
            $result3 = mysql_query($sql3);
            $lastYear = mysql_num_rows($result3);
		}

        $schoolsToPrint[] = 
                    array( 
                        'id'   => $k, 
                        'type' => $type, 
                        'date' => $date, 
                        'old'  => $old, 
                        'new'  => $new, 
                        'lastYear' => $lastYear                      
                    );
		?>
		<tr>
			<td align='left'>
			<a href="
			admin_shipping_report_detail.php?id=<?=$k?>&type=<?=$type?>&date=<?=urlencode($date)?>&old=<?=$old?>&new=<?=$new;?>&lastYear=<?=$lastYear;?>
			"><?=$v['name'];?>
			</a></td>
			<td>&nbsp;
			<?
			if ( in_array( $k, array(112, 66, 55, 110, 180, 3) ) ) {
			    echo "international";
			} else {
			    echo $v['shipping'];
			}
			?></td>
			<td>&nbsp;<?=$new + $old;?></td>
			<td>&nbsp;<?=$new;?></td>
			<td>&nbsp;<?=$lastYear;?></td>
			<td>&nbsp;<?=$new + $old;?></td>
		<?
		$sizes = array();		
		foreach ($add_ons as $j => $m) { 
			if ($m == 1) {
				$sql = "SELECT size, count( size ) AS total
						FROM user_add_ons AS ua, school_add_ons AS sa, users AS u, schools AS s
						WHERE ua.school_add_on_id = sa.school_add_on_id
						AND u.user_id = ua.user_id
						AND s.school_id = u.school_id
						AND s.school_id = $k
						AND sa.title = '$j'
						AND u.user_registered > 0 ";
				if ($type == 'new') {
					$sql .= "AND ua.date > '$date' ";
				}
				$sql .= "GROUP BY size";
                //echo $sql;
				
				//initialize specific sizes
				$sizes[$j]['s'] = 0;
				$sizes[$j]['m'] = 0;
				$sizes[$j]['l'] = 0;
				$sizes[$j]['xl'] = 0;
				$sizes[$j]['four'] = 0;
				$sizes[$j]['five'] = 0;
				
			} else { 
				$sql = "SELECT count( title ) AS total
						FROM user_add_ons AS ua, school_add_ons AS sa, users AS u, schools AS s
						WHERE ua.school_add_on_id = sa.school_add_on_id
						AND u.user_id = ua.user_id
						AND s.school_id = u.school_id
						AND s.school_id = $k
						AND sa.title = '$j'
						AND u.user_registered > 0 ";
				if ($type == 'new') {
					$sql .= "AND ua.date > '$date'";
				}
				$sizes[$j]['total'] = 0;
			}
			//echo $sql . "<br />";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				switch ($j) {
					case 'Sweatshirt':
						$size = strtoupper($row['size']);
						switch ($size) {
							case 'S':
								$sizes[$j]['s'] = $row['total'];
								break;
							case 'M':
								$sizes[$j]['m'] = $row['total'];
								break;
							case 'L':
								$sizes[$j]['l'] = $row['total'];
								break;
							case 'XL':
								$sizes[$j]['xl'] = $row['total'];
								break;							
						}
					case 'Cap':
						$size = strtoupper($row['size']);
						switch ($size) {
							case 'S':
								$sizes[$j]['s'] = $row['total'];
								break;
							case 'L':
								$sizes[$j]['l'] = $row['total'];
								break;
						}
					case 'Yarmulka': 
						$size = strtoupper($row['size']);
						switch ($size) {
							case '4':
								$sizes[$j]['four'] = $row['total'];
								break;
							case '5':
								$sizes[$j]['five'] = $row['total'];
								break;
							case 'S':
								$sizes[$j]['four'] += $row['total'];
								break;
							case 'L':
								$sizes[$j]['five'] += $row['total'];
								break;
						}
					default:
						$sizes[$j]['total'] = $row['total'];	
				}
			}
		}
		foreach ($add_ons as $j => $m) {
			echo "<td>&nbsp;";
			switch ($j)	{
				case 'Album':
				case 'Tehillim':
                case 'Siddur':
                case 'Backpack':
					echo $sizes[$j]['total'];
					break;
				case 'Sweatshirt':
					echo $sizes[$j]['s'] . "</td><td>";
					echo $sizes[$j]['m'] . "</td><td>";
					echo $sizes[$j]['l'] . "</td><td>";
					echo $sizes[$j]['xl'];
					break;				
				case 'Cap':
					echo $sizes[$j]['s'] . "</td><td>";
					echo $sizes[$j]['l'];
					break;
				case 'Yarmulka':
					echo $sizes[$j]['four'] . "</td><td>";
					echo $sizes[$j]['five'];
					break;
			}
			echo "</td>";
		}
		echo "</tr>";
	} 
echo "</table>";
$arr = http_build_query($schoolsToPrint);
?>
<p>
    <a href='admin_shipping_report_all.php?<?=$arr?>'>Print all Schools</a>
</p>
<?
} 
//print_r($sizes);
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
        th, td {
        	padding: 5px;
        	border: 1px solid black;
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