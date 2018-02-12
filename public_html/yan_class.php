<?
require_once 'class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();
$schoolIDs = array();
foreach ($schools as $id => $school) {
	$schoolIDs[] = $id;
}

$classes = array();
$classNames = array();
foreach ($schoolIDs as $id) {
	$sql = "select * from classes where school_id = " . $id . " and class_era = 0 order by class_grade, class_sub";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$classes[$id][] = $row['class_id'];
		$classNames[$row['class_id']] = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
	}
}

$users = array();
$userNames = array();
foreach ($classes as $school => $grades) {
	foreach ($grades as $grade) {
		$sql = "select * from users where class_id = " . $grade . " and user_registered > 0 order by last, first";
		$result = mysql_query($sql);
		if (mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_assoc($result))	{
				$users[$school][$grade][] = $row['user_id'];
				$userNames[$row['user_id']] = $row['first'] . ' ' . $row['last'];
			}
		}
	}
}

require_once 'class.bpSummary.php';
require_once 'class.balPehCampaign.php';
$results = array();
foreach ($campaigns as $id => $campaign) {
	$bp = BalPehCampaign::getInstance( $id );
	$bps = new BpSummary( $id, 'user' );
	foreach ($users as $school => $grades) {
		foreach ($grades as $grade => $info) {
			$grandTotal[$school][$grade][$campaign]['pledged'] = 0;
			$grandTotal[$school][$grade][$campaign]['learned'] = 0;
			foreach ($info as $user_id) {
				$learned = $bps->getSummary( $user_id );
				if ($learned == '') $learned = 0;
				$results[$campaign]['pledged'][$school][$grade][$user_id] = $bp->getTotalPledged( 'user', $user_id );
				$results[$campaign]['learned'][$school][$grade][$user_id] = $learned;
				$grandTotal[$school][$grade][$campaign]['pledged'] += $results[$campaign]['pledged'][$school][$grade][$user_id];
				$grandTotal[$school][$grade][$campaign]['learned'] += $results[$campaign]['learned'][$school][$grade][$user_id];
			}
		}
	}
}
//echo "<pre>"; print_r($results); echo "</pre>"; exit;
?>
<div class="mainYan">
<? 
foreach ($users as $school => $grades) {
	foreach ($grades as $grade => $info) { 
		?>
		<div class="grade">
			
			<input type="hidden" class="totalTanyaPledged" value="<?=$grandTotal[$school][$grade]['tanya']['pledged']?>" />
			<input type="hidden" class="totalTanyaLearned" value="<?=$grandTotal[$school][$grade]['tanya']['learned']?>" />
			<input type="hidden" class="totalMishnaPledged" value="<?=$grandTotal[$school][$grade]['mishna']['pledged']?>" />
			<input type="hidden" class="totalMishnaLearned" value="<?=$grandTotal[$school][$grade]['mishna']['learned']?>" />
			
			<p class="top">The Rebbe's Birthday Present<br /> by Grade <?=$classNames[$grade]?></p>
			
			<div class="thermometer1">
			    <canvas class="demo" width="250" height="350"></canvas>
			    <div class="thermLabel">Tanya</div>
		    </div>
		    
		    <div class="thermometer2">
			    <canvas class="demo2" width="250" height="350"></canvas>
			    <div class="thermLabel2">Mishna</div>
		    </div>
			
		    <table width="65%">
		    	<tr>
		    		<th><a href="yud_alef_nissan_class_report.php">Chayol</a></th>
		    		<th><a href="yud_alef_nissan_class_report.php?sortBy=tanyaP">תניא בעל פה <br />Lines Pledged</a></th>
		    		<th><a href="yud_alef_nissan_class_report.php?sortBy=tanyaL">תניא בעל פה <br />Lines Learned</a></th>
		    		<th><a href="yud_alef_nissan_class_report.php?sortBy=mishnaP">משניות בעל פה <br />Lines Pledged</a></th>
		    		<th><a href="yud_alef_nissan_class_report.php?sortBy=mishnaL">משניות בעל פה <br />Lines Learned</a></th>
		    		<!--<th><a href="yud_alef_nissan_class_report.php?sortBy=maos">מעות חיטים <br />Pledges</a></th>-->
		    	</tr>
		    	
		    	<?
		    	$data = array();
		    	foreach ($info as $user) {
					$userName = $userNames[$user];
		    		//$maosChittim = $m->getInfoFor($user, 'user');
					$data[$userName]['tanyaP']  = isset($results['tanya']['pledged'][$school][$grade][$user]) ? $results['tanya']['pledged'][$school][$grade][$user] : 0;
					$data[$userName]['tanyaL']  = isset($results['tanya']['learned'][$school][$grade][$user]) ? $results['tanya']['learned'][$school][$grade][$user] : 0;
					$data[$userName]['mishnaP'] = isset($results['mishna']['pledged'][$school][$grade][$user]) ? $results['mishna']['pledged'][$school][$grade][$user] : 0;
					$data[$userName]['mishnaL'] = isset($results['mishna']['learned'][$school][$grade][$user]) ? $results['mishna']['learned'][$school][$grade][$user] : 0;
					//$data[$userName]['maos']  = $maosChittim['pledged'] ? $maosChittim['pledged'] : 0;
		    	}
		    	//echo "<pre>"; print_r($data); echo "</pre>"; //exit;
		    	foreach ($data as $user => $info) {
		    		$name[$user]    = $user;
					$tanyaP[$user]  = $info['tanyaP'];
					$tanyaL[$user]  = $info['tanyaL'];
					$mishnaP[$user] = $info['mishnaP'];
					$mishnaL[$user] = $info['mishnaL'];
					//$maos[$user]  = $info['maos'];
		    	}
				
				if (isset($_GET['sortBy'])) {
					switch ($_GET['sortBy']) {
						case 'tanyaP':
							array_multisort($tanyaP, SORT_DESC, $name, $data);
							break;
						case 'tanyaL':
							array_multisort($tanyaL, SORT_DESC, $name, $data);
							break;
						case 'mishnaP':
							array_multisort($mishnaP, SORT_DESC, $name, $data);
							break;
						case 'mishnaL':
							array_multisort($mishnaL, SORT_DESC, $name, $data);
							break;
						case 'maos':
							array_multisort($maos, SORT_DESC, $name, $data);
							break;
					}
				}
				
				//initialize totals array we don't need the school id in the totals array
				$totals = array();
				foreach ($data as $user => $info) {
					foreach ($info as $key => $value) {
						$totals[$key] = 0;
					}
					break;
				}
				
		    	foreach ($data as $user => $info) {
		    		echo "<tr><td>" . $user . "</td>";		
		    		foreach ($info as $key => $value) {
		    			$totals[$key] += $value;
						echo "<td>" . number_format($value) . "</td>";
		    		}
					echo "</tr>";
		    	}
				
				echo "<tr><th align='right'>Totals</th>";
				foreach ($totals as $key => $value) {
					echo "<th>" . number_format($value) . "</th>";
				}
				echo "</tr>";
				
				echo "<tr><th colspan='6'>";
				include 'yan_footer.php';
				echo "</th></tr>";
		    	?>
		    </table>
		    
		    <div id='countdown'>
		    	<?
				$dif = $yudAlef - unixtojd();
				echo $dif;
		    	?>
		    	days left!!!
		    </div>
		    <div class="page-break"></div>
		</div>
		<?
	 }
} 
?>
</div>

<script type='text/javascript' src='jsthermometer/thermometer.js'></script>
<script type='text/javascript' src='jsthermometer/jquery.thermometer.js'></script>
<script>
	$(".grade").each( function() {		
		var tanya = $(this).find('.demo');
		var mishna = $(this).find('.demo2');
		
		var w = $(tanya).width();
	    var h = $(mishna).height();
		
		var tanyaPledged = $(this).find(".totalTanyaPledged").val();
		var tanyaLearned = $(this).find(".totalTanyaLearned").val();
		var mishnaPledged = $(this).find(".totalMishnaPledged").val();
		var mishnaLearned = $(this).find(".totalMishnaLearned").val();
		
		if (tanyaPledged > 0) {		
		    $(tanya).thermometer({
		        w: w,
		        h: h,
		        color: {
		            label: 'rgba(255, 255, 255, 1)',
		            tickLabel: 'rgba(255, 0, 0, 1)'
		        },
		        centerTicks: false,
		        majorTicks: 2,
		        minorTicks: 1,
		        max: tanyaPledged,
		        min: 0,
		        scaleTickLabelText: 1.0,
		        scaleLabelText: 1.0,
		        scaleTickWidth: 1.0,
		        unitsLabel: ""
		    });
		    $(tanya).thermometer('setValue', parseInt(tanyaLearned));
		}
		
		if (mishnaPledged > 0) {		    
		    $(mishna).thermometer({
		        w: w,
		        h: h,
		        color: {
		            label: 'rgba(255, 255, 255, 1)',
		            tickLabel: 'rgba(255, 0, 0, 1)'
		        },
		        centerTicks: false,
		        majorTicks: 2,
		        minorTicks: 1,
		        max: mishnaPledged,
		        min: 0,
		        scaleTickLabelText: 1.0,
		        scaleLabelText: 1.0,
		        scaleTickWidth: 1.0,
		        unitsLabel: ""
		    });
		    $(mishna).thermometer('setValue', parseInt(mishnaLearned));
		}
	});
</script>