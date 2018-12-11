<?
require_once 'class.adminSchools.php';
$sql = "select school_id from schools where tanya = 1 order by tanya_ord";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$schoolIDs[] = $row['school_id'];
}

$regInfo = array();
foreach ($schoolIDs as $id) {
	$rSql = "select count(*) as total from users where school_id = $id and user_registered > 0";
	$rResult = mysql_query($rSql);
	$rRow = mysql_fetch_assoc($rResult);
	$registered = $rRow['total'];
	$regInfo[$id] = $registered ? $registered : 0;
}

require_once 'class.bpSummary.php';
require_once 'class.balPehCampaign.php';
$results = array();
foreach ($campaigns as $id => $campaign) {
	$bp = BalPehCampaign::getInstance( $id );
	$bps = new BpSummary( $id, 'school' );
	$grandTotal[$campaign]['pledged'] = 0;
	$grandTotal[$campaign]['learned'] = 0;
	foreach ($schoolIDs as $school_id) {
		$pledged = $bp->getTotalPledged( 'school', $school_id );
		$learned = $bps->getSummary( $school_id );
		if ($learned == '') $learned = 0;
		$results[$campaign]['pledged'][$school_id] = $pledged;
		$results[$campaign]['learned'][$school_id] = $learned;
		$grandTotal[$campaign]['pledged'] += $results[$campaign]['pledged'][$school_id];
		$grandTotal[$campaign]['learned'] += $results[$campaign]['learned'][$school_id];
	}
}
?>
<div class="mainYan">
	<p class="top army">The Rebbe's Birthday Present</p>
	
	<div class="thermometer1">
	    <canvas class="demo" width="300" height="350"></canvas>
	    <div class="thermLabel">Tanya</div>
	    <br />
	    <br />
	    <br />
	    <br />
	    <canvas class="demo2" width="300" height="350"></canvas>
	    <div class="thermLabel">Mishna</div>
	</div>
	
    <table>
    	<tr>
    		<th class='school'><a href="yud_alef_nissan_report.php">School</a></th>
    		<th><a href="yud_alef_nissan_report.php?sortBy=reg">חיילים <br />Registered</a></th>
    		<th><a href="yud_alef_nissan_report.php?sortBy=tanyaP">תניא בעל פה <br />Lines Pledged</a></th>
    		<th><a href="yud_alef_nissan_report.php?sortBy=tanyaL">תניא בעל פה <br />Lines Learned</a></th>
    		<th><a href="yud_alef_nissan_report.php?sortBy=mishnaP">משניות בעל פה <br />Lines Learned</a></th>
    		<th><a href="yud_alef_nissan_report.php?sortBy=mishnaL">משניות בעל פה <br />Lines Learned</a></th>
    		<!--<th><a href="yud_alef_nissan_report.php?sortBy=maos">מעות חיטים <br />Pledges</a></th>-->
    	</tr>
    	
    	<?
    	$data = array();
    	foreach ($schools as $id => $school) {
    		//$maosChittim = $m->getInfoFor($id);
    		$data[$school]['reg']     = $regInfo[$id];
			$data[$school]['tanyaP']  = isset($results['tanya']['pledged'][$id]) ? $results['tanya']['pledged'][$id] : 0;
			$data[$school]['tanyaL']  = isset($results['tanya']['learned'][$id]) ? $results['tanya']['learned'][$id] : 0;
			$data[$school]['mishnaP'] = isset($results['mishna']['pledged'][$id]) ? $results['mishna']['pledged'][$id] : 0;
			$data[$school]['mishnaL'] = isset($results['mishna']['learned'][$id]) ? $results['mishna']['learned'][$id] : 0;
			//$data[$school]['maos']  = $maosChittim['pledged'] ? $maosChittim['pledged'] : 0;
    	}
    	//echo "<pre>"; print_r($data); echo "</pre>"; //exit;
    	foreach ($data as $school => $info) {
    		$name[$school]    = $school;
    		$reg[$school] 	  = $info['reg'];
			$tanyaP[$school]  = $info['tanyaP'];
			$tanyaL[$school]  = $info['tanyaL'];
			$mishnaP[$school] = $info['mishnaP'];
			$mishnaL[$school] = $info['mishnaL'];
			//$maos[$school] = $info['maos'];
    	}
		
		if (isset($_GET['sortBy'])) {
			switch ($_GET['sortBy']) {
				case 'reg':
					array_multisort($reg, SORT_DESC, $name, $data);
					break;
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
		//echo "<pre>"; print_r($data); echo "</pre>"; exit;
		
		//initialize totals array we don't need the school id in the totals array
		$totals = array();
		foreach ($data as $school => $info) {
			foreach ($info as $key => $value) {
				$totals[$key] = 0;
			}
			break;
		}
		
		$i = 0;
    	foreach ($data as $school => $info) {
    		if ($i++ == 30) {
    			?>
    			</table>
    			
    			<p class="top army">The Rebbe's Birthday Present</p>
    			
    			<div class="thermometer1">
				    <canvas class="demo" width="300" height="350"></canvas>
				    <div class="thermLabel">Tanya</div>
				    <br />
				    <br />
				    <br />
				    <br />
				    <canvas class="demo2" width="300" height="350"></canvas>
				    <div class="thermLabel">Mishna</div>
				</div>
	
    			<table>
			    	<tr>
			    		<th><a href="yud_alef_nissan_report.php">School</a></th>
			    		<th><a href="yud_alef_nissan_report.php?sortBy=reg">חיילים <br />Registered</a></th>
			    		<th><a href="yud_alef_nissan_report.php?sortBy=tanyaP">תניא בעל פה <br />Lines Pledged</a></th>
			    		<th><a href="yud_alef_nissan_report.php?sortBy=tanyaL">תניא בעל פה <br />Lines Learned</a></th>
			    		<th><a href="yud_alef_nissan_report.php?sortBy=mishnaP">משניות בעל פה <br />Lines Pledged</a></th>
			    		<th><a href="yud_alef_nissan_report.php?sortBy=mishnaL">משניות בעל פה <br />Lines Learned</a></th>
			    		<!--<th><a href="yud_alef_nissan_report.php?sortBy=maos">מעות חיטים <br />Pledges</a></th>-->
			    	</tr>
    			<?
    		}
    		echo "<tr><td>" . $school . "</td>";		
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

<script type='text/javascript' src='jsthermometer/thermometer.js'></script>
<script type='text/javascript' src='jsthermometer/jquery.thermometer.js'></script>
<script>
	var w = $('.demo').width();
    var h = $('.demo').height();

    $('.demo').thermometer({
        w: w,
        h: h,
        color: {
            label: 'rgba(255, 255, 255, 1)',
            tickLabel: 'rgba(255, 0, 0, 1)'
        },
        centerTicks: false,
        majorTicks: 2,
        minorTicks: 1,
        max: <?=$grandTotal['tanya']['pledged']?>,
        min: 0,
        scaleTickLabelText: 1.0,
        scaleLabelText: 0.9,
        scaleTickWidth: 1.0,
        unitsLabel: ""
    });
	
	var total = <?=$grandTotal['tanya']['learned']?>;
    $('.demo').thermometer('setValue', parseInt(total));
    
    $('.demo2').thermometer({
        w: w,
        h: h,
        color: {
            label: 'rgba(255, 255, 255, 1)',
            tickLabel: 'rgba(255, 0, 0, 1)'
        },
        centerTicks: false,
        majorTicks: 2,
        minorTicks: 1,
        max: <?=$grandTotal['mishna']['pledged']?>,
        min: 0,
        scaleTickLabelText: 1.0,
        scaleLabelText: 0.9,
        scaleTickWidth: 1.0,
        unitsLabel: ""
    });
	
	var total = <?=$grandTotal['mishna']['learned']?>;
    $('.demo2').thermometer('setValue', parseInt(total));
</script>