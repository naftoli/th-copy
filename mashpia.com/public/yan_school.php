<?
require_once 'class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();
$schoolIDs = array();
foreach ($schools as $id => $school) {
	$schoolIDs[] = $id;
}

$classes = array();
require_once 'class.schoolClasses.php';
foreach ($schoolIDs as $id) {
	$s = new SchoolClasses($id);
	$classes[$id] = $s->getClasses();
}

$classIDs = array();
foreach ($classes as $school => $grades) {
	foreach ($grades as $grade) {
		$classIDs[$school][] = $grade['class_id'];
	}
}

require_once 'class.bpSummary.php';
require_once 'class.balPehCampaign.php';
$results = array();
foreach ($campaigns as $id => $campaign) {
	$bp = BalPehCampaign::getInstance( $id );
	$bps = new BpSummary( $id, 'class' );
	foreach ($classIDs as $school => $info) {
		$grandTotal[$school][$campaign]['pledged'] = 0;
		$grandTotal[$school][$campaign]['learned'] = 0;
		foreach ($info as $class_id) {
			$learned = $bps->getSummary( $class_id );
			if ($learned == '') $learned = 0;
			$results[$campaign]['pledged'][$school][$class_id] = $bp->getTotalPledged( 'class', $class_id );
			$results[$campaign]['learned'][$school][$class_id] = $learned;
			$grandTotal[$school][$campaign]['pledged'] += $results[$campaign]['pledged'][$school][$class_id];
			$grandTotal[$school][$campaign]['learned'] += $results[$campaign]['learned'][$school][$class_id];
		}
	}
}

$pledges = array(
	'Pre1a'	=>	11, 
	'1'		=>	22,
	'2'		=>	44,
	'3'		=>	66,
	'4'		=>	77,
	'5'		=>	88,
	'6'		=>	100, 
	'7'		=>	113,
	'8'		=>	113
);
?>
<div class="mainYan">
<? foreach ($classes as $id => $grades) : ?>
	<div class="school">
		
		<input type="hidden" class="totalTanyaPledged" value="<?=$grandTotal[$id]['tanya']['pledged']?>" />
		<input type="hidden" class="totalTanyaLearned" value="<?=$grandTotal[$id]['tanya']['learned']?>" />
		<input type="hidden" class="totalMishnaPledged" value="<?=$grandTotal[$id]['mishna']['pledged']?>" />
		<input type="hidden" class="totalMishnaLearned" value="<?=$grandTotal[$id]['mishna']['learned']?>" />
		
		<p class="top">
			The Rebbe's Birthday Present by <?=$schools[$id]?><br />
			<?
			$row = mysql_fetch_assoc(mq("SELECT logo FROM schools where school_id = " . $id));
			if (!empty($row['logo'])) {
				echo "<img src='schoolLogos/" . $row['logo'] . "' 
					style='width: 150px; margin-top: 10px; margin-bottom: -30px' />";
			}
			?>
		</p>
		
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
	    		<th><a href="yud_alef_nissan_school_report.php">Platoon</a></th>
	    		<th>Registered Chayolim</th>
	    		<th>Quota</th>
	    		<th><a href="yud_alef_nissan_school_report.php?sortBy=tanyaP">תניא בעל פה <br />Lines Pledged</a></th>
	    		<th><a href="yud_alef_nissan_school_report.php?sortBy=tanyaL">תניא בעל פה <br />Lines Learned</a></th>
	    		<th><a href="yud_alef_nissan_school_report.php?sortBy=mishnaP">משניות בעל פה <br />Lines Pledged</a></th>
	    		<th><a href="yud_alef_nissan_school_report.php?sortBy=mishnaL">משניות בעל פה <br />Lines Learned</a></th>
	    		<!--<th><a href="yud_alef_nissan_school_report.php?sortBy=maos">מעות חיטים <br />Pledges</a></th>-->
	    	</tr>
	    	
	    	<?
	    	$data = array();
	    	foreach ($grades as $grade) {
	    		$class_id = $grade['class_id'];
				$class = $grade['class_grade'] . (empty($grade['class_sub']) ? '' : '-' . $grade['class_sub']);
	    		//$maosChittim = $m->getInfoFor($id, 'class');
				$data[$class]['tanyaP']	 = isset($results['tanya']['pledged'][$id][$class_id]) ? $results['tanya']['pledged'][$id][$class_id] : 0;
				$data[$class]['tanyaL']	 = isset($results['tanya']['learned'][$id][$class_id]) ? $results['tanya']['learned'][$id][$class_id] : 0;
				$data[$class]['mishnaP'] = isset($results['mishna']['pledged'][$id][$class_id]) ? $results['mishna']['pledged'][$id][$class_id] : 0;
				$data[$class]['mishnaL'] = isset($results['mishna']['learned'][$id][$class_id]) ? $results['mishna']['learned'][$id][$class_id] : 0;
				//$data[$class]['maos']  = $maosChittim['pledged'] ? $maosChittim['pledged'] : 0;
			    
			    $sql = "select count(*) as total from users where class_id = " . $class_id . " and user_registered > 0";
				$result = mysql_query($sql);
				$row = mysql_fetch_assoc($result);
				$totalReg[$class] = $row['total'];
				$pledge[$class] = $pledges[$grade['class_grade']];				
	    	}
	    	//echo "<pre>"; print_r($data); echo "</pre>"; //exit;
	    	foreach ($data as $class => $info) {
	    		$name[$class]    = $class;
				$tanyaP[$class]  = $info['tanyaP'];
				$tanyaL[$class]  = $info['tanyaL'];
				$mishnaP[$class] = $info['mishnaP'];
				$mishnaL[$class] = $info['mishnaL'];
				//$maos[$class]  = $info['maos'];
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
			foreach ($data as $class => $info) {
				foreach ($info as $key => $value) {
					$totals[$key] = 0;
				}
				break;
			}
			
			$regTotals = 0;
	    	foreach ($data as $class => $info) {
	    		echo "<tr><td>" . $class . "</td>";	
				echo "<td>" . $totalReg[$class] . "</td>";
				echo "<td>" . $pledge[$class] . "</td>";
				$regTotals += $totalReg[$class];
	    		foreach ($info as $key => $value) {
	    			$totals[$key] += $value;
					echo "<td>" . $value . "</td>";
	    		}
				echo "</tr>";
	    	}
			
			echo "<tr><th align='right'>Totals</th>";
			echo "<th>" . number_format($regTotals) . "</th>";
			echo "<th> - </th>";
			foreach ($totals as $key => $value) {
				echo "<th>" . number_format($value) . "</th>";
			}
			echo "</tr>";
			
			echo "<tr><th colspan='7'>";
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
<? endforeach; ?>
</div>

<script type='text/javascript' src='jsthermometer/thermometer.js'></script>
<script type='text/javascript' src='jsthermometer/jquery.thermometer.js'></script>
<script>
	$(".school").each( function() {		
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