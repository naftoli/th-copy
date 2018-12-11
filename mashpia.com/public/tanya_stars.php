<?
$admin_auth = array('school', 'user'); 
require('header.php');

require_once 'class.tanya.php';
$tanya = new Tanya;

$users = array();
$sql = "select u.user_id, u.first, u.last, c.class_grade, c.class_sub, s.school_name from users u 
		join schools s using (school_id) 
		join classes c on (c.class_id = u.class_id) 
		where user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[$row['user_id']] = $row;
}

$lines = array();
foreach ($users as $user => $info) {
	$lines[$user] = $tanya->getTotalForUser($user);
}
asort($lines);
$lines = array_reverse($lines, true);


$start = isset($_POST['start']) ? $_POST['start'] : 1;
$end = isset($_POST['end']) ? $_POST['end'] : 10000;

$totals = array();
foreach ($lines as $user => $line) {
	if ($line > $end) continue;
	if ($line < $start) break;
	$totals[$user] = $line;
}

//echo "<pre>"; print_r($totals); echo "</pre>"; exit;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tanya Stars Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            p {
                font-size: 12px;
            }
            table {
                font-size: 11px;
            }
            th, td {
            	padding: 5px;
            }
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
            .page-break {
                page-break-after: always;
            }
            @media print {
                .no-print {
                    display: none;
                }
            }
        </style>
    </head>
    
	<body>
		<? include('admin_header.php'); ?>
        <h1 class="no-print">Tanya Stars Report</h1>
        
        <form action="tanya_stars.php" method="post">
	        <fieldset>
	        	<legend>Filter</legend>
	        	<p>
	        		Show tanya lines learned between 
	        		<select name='start'>
	        			<?
	        			for ($i = 1; $i < 11; $i++) {
	        				if ($i == $start) {
	        					$selected = "selected='selected'";
	        				} else {
	        					$selected = '';
	        				}
	        				echo "<option value='$i' $selected>$i</option>";
	        			}
						for ($j = 10; $j < 100; $j += 10) {
							if ($j == $start) {
	        					$selected = "selected='selected'";
	        				} else {
	        					$selected = '';
	        				}
							echo "<option value='$j' $selected>$j</option>";
						}
						for ($k = 100; $k <= 10000; $k += 100) {
							if ($k == $start) {
	        					$selected = "selected='selected'";
	        				} else {
	        					$selected = '';
	        				}
							echo "<option value='$k' $selected>$k</option>";
						}
	        			?>
	        		</select>
	        		and 
	        		<select name='end'>
	        			<?
	        			for ($i = 1; $i < 11; $i++) {
	        				if ($i == $end) {
	        					$selected = "selected='selected'";
	        				} else {
	        					$selected = '';
	        				}
	        				echo "<option value='$i' $selected>$i</option>";
	        			}
						for ($j = 10; $j < 100; $j += 10) {
							if ($j == $end) {
	        					$selected = "selected='selected'";
	        				} else {
	        					$selected = '';
	        				}
							echo "<option value='$j' $selected>$j</option>";
						}
						for ($k = 100; $k < 1000; $k += 100) {
							if ($k == $end) {
	        					$selected = "selected='selected'";
	        				} else {
	        					$selected = '';
	        				}
							echo "<option value='$k' $selected>$k</option>";
						}
						for ($l = 1000; $l <= 10000; $l += 1000) {
							if ($l == $end) {
	        					$selected = "selected='selected'";
	        				} else {
	        					$selected = '';
	        				}	
							echo "<option value='$l' $selected>$l</option>";
						}
	        			?>
	        		</select>
	        	</p>
	        	<input type="submit" name="submit" value="submit" />
	        </fieldset>
	    </form>
        
        <table>
        	<tr>
        		<th>School</th>
        		<th>Grade</th>
        		<th>First Name</th>
        		<th>Last Name</th>
        		<th>Lines Learned</th>
        	</tr>
        	<?
        	foreach ($totals as $user => $total) {
        		$school = $users[$user]['school_name'];
        		$grade = $users[$user]['class_grade'] . (empty($users[$user]['class_grade']) ? '' : '-' . $users[$user]['class_grade']);
        		echo "<tr><td>" . $school . "</td><td>" . $grade . "</td><td>" . $users[$user]['first'] . 
        			"</td><td>" . $users[$user]['last'] . "</td><td>" . $total . "</td></tr>";  
        	}
        	?>
        </table>
	</body>
</html>