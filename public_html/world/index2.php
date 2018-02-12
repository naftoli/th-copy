<?
$campaigns = array(
	5 => 'tanya',
	6 => 'mishna'
); //tanya, mishna yud alef nissan 5775

include '../db.php';
$schools = array();
$sql = "select school_id, school_name from schools where school_era is null and school_id not in (79,82,198,199) order by school_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$schools[$row['school_id']] = $row['school_name'];
}

$regInfo = array();
foreach ($schools as $id => $school) {
	$rSql = "select count(*) as total from users where school_id = $id and user_registered > 0";
	$rResult = mysql_query($rSql);
	$rRow = mysql_fetch_assoc($rResult);
	$registered = $rRow['total'];
	$regInfo[$id] = $registered ? $registered : 0;
}

require_once '../class.bpSummary.php';
require_once '../class.balPehCampaign.php';
$results = array();
foreach ($campaigns as $id => $campaign) {
	$bp = BalPehCampaign::getInstance( $id );
	$bps = new BpSummary( $id, 'school' );
	$grandTotal[$campaign]['pledged'] = 0;
	$grandTotal[$campaign]['learned'] = 0;
	foreach ($schools as $school_id => $school) {
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
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Bootstrap Admin Theme</title>

    <!-- Bootstrap Core CSS -->
    <link href="admin2/bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="admin2/bower_components/metisMenu/dist/metisMenu.min.css" rel="stylesheet">

    <!-- Timeline CSS -->
    <link href="admin2/dist/css/timeline.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="admin2/dist/css/sb-admin-2.css" rel="stylesheet">

    <!-- Morris Charts CSS -->
    <link href="admin2/bower_components/morrisjs/morris.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="admin2/bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    
    <style>
    	#wrapper {
    		width: 70%;
    		margin: auto;
    	}
    	.page-header {
    		text-align: center;
    	}
    	th {
			border-bottom: 1px solid black;
			background-color: #a8a8a8;
		}
		tr {
			background-color: #d8d8d8;
		}
		tr:nth-child(odd) {
			background-color: #e8e8e8;
		}
		td:first-child {
			text-align: left;
		}
		.demo {
			margin-left: -70px;
		}
		.demo2 {
			margin-right: -100px;
		}
		.thermometer1 {
			float: left;
			position: absolute;
			left: 0px;
			top: 100px;
		}
		.thermometer2 {
			float: right;
			position: absolute;
			right: 0px;
			top: 100px;
		}
		.thermometer1 .thermLabel {
			margin-left: 90px;
		}
		.thermometer2 .thermLabel {
			margin-left: 155px;
		}
		#DateCountdown {
			margin: auto;
			background-color: transparent !important;
			margin-bottom: 10px !important;
		}
		.panel-footer {
			border-bottom: 1px solid #ddd;
			border-top-left-radius: 3px;
			border-top-right-radius: 3px;
			text-align: center;
		}
		.panel-heading {
			border-top-left-radius: 0px;
			border-top-right-radius: 0px;
			border-bottom-left-radius: 3px;
			border-bottom-right-radius: 3px;
		}
    </style>
    <link rel="stylesheet" href="../countdown/TimeCircles/inc/TimeCircles.css" />
</head>

<body>
	
	<div class="thermometer1">
	    <canvas class="demo" height="400px" width="350px"></canvas>
	    <div class="thermLabel">תניא</div>
	</div>
    	
	<div class="thermometer2">
	    <canvas class="demo2" height="400px" width="350px"></canvas>
	    <div class="thermLabel">משנה</div>
	</div>

    <div id="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">The Rebbe's Birthday Present</h1>
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <!-- /.row -->
        <div class="row">
        	<div class="col-lg-3 col-md-6 col-xs-6">
                <div class="panel panel-primary">
                	<div class="panel-footer">
                        <span>תניא בעל פה</span>
                        <div class="clearfix"></div>
                    </div>
                    <div class="panel-heading">
                        <div class="row">                           
                            <div class="col-xs-12 text-center">
                                <div class="huge">
                                	<?=number_format($grandTotal['tanya']['pledged'])?>
                                </div>
                                <div>Lines Pledged!</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-xs-6">
                <div class="panel panel-red">
                	<div class="panel-footer">
                        <span>משניות בעל פה</span>
                        <div class="clearfix"></div>
                    </div>
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-12 text-center">
                                <div class="huge">
                                	<?=number_format($grandTotal['mishna']['pledged'])?>
                                </div>
                                <div>Lines Pledged!</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-xs-6">
                <div class="panel panel-primary">
                	<div class="panel-footer">
                        <span>תניא בעל פה</span>
                        <div class="clearfix"></div>
                    </div>
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-12 text-center">
                                <div class="huge">
                                	<?=number_format($grandTotal['tanya']['learned'])?>
                                </div>
                                <div>Lines Learned!</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-xs-6">
                <div class="panel panel-red">
                	<div class="panel-footer">
                        <span>משניות בעל פה</span>
                        <div class="clearfix"></div>
                    </div>
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-12 text-center">
                                <div class="huge">
                                	<?=number_format($grandTotal['mishna']['learned'])?>
                                </div>
                                <div>Lines Learned!</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row -->
        
        <div class="row">
            <div class="col-lg-12 col-md-12 col-xs-12">
            	<div id="DateCountdown" data-date="2015-03-30 19:51:00" style="width: 500px; height: 125px; padding: 0px; box-sizing: border-box; background-color: #E0E8EF"></div>
            </div>
            <!-- /.col-lg-12 -->
        </div>
        
        <div class="row">
        	<div class="col-lg-12 col-md-12 col-xs-12">
	       		<div class="table-responsive">       
			        <table class="table table-striped table-bordered table-hover">
			        	<thead>
					    	<tr>
					    		<th class='school'>School</th>
					    		<th>חיילים <br />Registered</th>
					    		<th>תניא בעל פה <br />Lines Pledged</th>
					    		<th>תניא בעל פה <br />Lines Learned</th>
					    		<th>משניות בעל פה <br />Lines Pledged</th>
					    		<th>משניות בעל פה <br />Lines Learned</th>
					    	</tr>
				    	</thead>
				    	<tbody>
				    	<?
				    	$data = array();
				    	foreach ($schools as $id => $school) {
				    		$data[$school]['reg']     = $regInfo[$id];
							$data[$school]['tanyaP']  = isset($results['tanya']['pledged'][$id]) ? $results['tanya']['pledged'][$id] : 0;
							$data[$school]['tanyaL']  = isset($results['tanya']['learned'][$id]) ? $results['tanya']['learned'][$id] : 0;
							$data[$school]['mishnaP'] = isset($results['mishna']['pledged'][$id]) ? $results['mishna']['pledged'][$id] : 0;
							$data[$school]['mishnaL'] = isset($results['mishna']['learned'][$id]) ? $results['mishna']['learned'][$id] : 0;
				    	}
						
						$totals = array();
						foreach ($data as $school => $info) {
							foreach ($info as $key => $value) {
								$totals[$key] = 0;
							}
							break;
						}
						
						$i = 0;
				    	foreach ($data as $school => $info) {
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
						
						echo "<tr><th colspan='6' style='text-align: center'>";
						?>
						
						תניא בעל פה לע״נ התמים נתן נטע בן הרה"ח ר' זלמן יודא דייטש ע"ה
						<br />
						משניות בעל פה לע"נ זאב ארי' ע"ה בן יבלט"א הרה"ח ר' שניאור זלמן שי' גליק
						</th></tr></tbody>
				    </table>
				</div>
			</div>
		</div>
    </div>
    <!-- /#wrapper -->

    <!-- jQuery -->
    <script src="admin2/bower_components/jquery/dist/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="admin2/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="admin2/bower_components/metisMenu/dist/metisMenu.min.js"></script>

    <!-- Morris Charts JavaScript -->
    <script src="admin2/bower_components/raphael/raphael-min.js"></script>
    <script src="admin2/bower_components/morrisjs/morris.min.js"></script>
    <script src="admin2/js/morris-data.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="admin2/dist/js/sb-admin-2.js"></script>
    
    <script type='text/javascript' src='../jsthermometer/thermometer.js'></script>
	<script type='text/javascript' src='../jsthermometer/jquery.thermometer.js'></script>
	<script type="text/javascript" src="../countdown/TimeCircles/inc/TimeCircles.js"></script>
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
	    
	    $("#DateCountdown").TimeCircles();
	</script>
</body>
</html>
