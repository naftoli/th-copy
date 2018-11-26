<?php
//ini_set('display_errors',1);
if (!isset($_GET['school_id'])) {
    header("Location: index.php");
    exit;
}

$ROOT_DIR = __DIR__ . '/../../';

require $ROOT_DIR . 'db.php';
require $ROOT_DIR . 'class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

// get campaigns for current year
$sql = "SELECT * from line_campaigns where year = " . $year;
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
	$campaigns[$row['id']] = strtolower( $row['type'] );
}

$school_id = mysql_real_escape_string( $_GET['school_id'] );

require $ROOT_DIR . 'class.schoolClasses.php';
$sc = new SchoolClasses( $school_id );
$grades = $sc->getClasses();

$regInfo = array();
foreach ($grades as $grade) {
	$rSql = "select count(*) as total from users where class_id = " . $grade['class_id'] . " and (user_registered > 0 or yan = 1)";
	$rResult = mysql_query($rSql);
	$rRow = mysql_fetch_assoc($rResult);
	$registered = $rRow['total'];
	$regInfo[$grade['class_id']] = $registered ? $registered : 0;
}

require_once $ROOT_DIR . 'class.bpSummary.php';
//require_once '../class.balPehCampaign.php';
$results = array();
foreach ($campaigns as $id => $campaign) {
	//$bp = BalPehCampaign::getInstance( $id );
	$bps = new BpSummary( $id, 'class' );
	//$grandTotal[$campaign]['pledged'] = 0;
	$grandTotal[$campaign]['learned'] = 0;
	foreach ($grades as $grade) {
		//$pledged = $bp->getTotalPledged( 'school', $school_id );
		$learned = $bps->getSummary( $grade['class_id'] );
		if ($learned == '') $learned = 0;
		if ($learned == 0) continue;
		//$results[$campaign]['pledged'][$school_id] = $pledged;
		$results[$campaign]['learned'][$grade['class_id']] = $learned;
		//$grandTotal[$campaign]['pledged'] += $results[$campaign]['pledged'][$school_id];
		$grandTotal[$campaign]['learned'] += $results[$campaign]['learned'][$grade['class_id']];
	}
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Pragma" content="No-Cache" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="refresh" content="300" />
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
		.panel-success {
			border-color: #3c763d !important;
		}
		.panel-success > .panel-heading {
			color: #fff !important;
			background-color: #3c763d !important;
			border-color: #3c763d !important;
		}
    </style>
    <link rel="stylesheet" href="../countdown/TimeCircles/inc/TimeCircles.css" />
</head>

<body>
    <div id="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">The Rebbe's Birthday Present</h1>
            </div>
            <!-- /.col-lg-12 -->
        </div>
		
		<!--<div class="row">
			<div class="col-lg-12" style="text-align: center; font-style: italic; margin-top: -20px; margin-bottom: 10px;">
                <h3>The goal for ה'תשע"ח is 1,000,000 combined lines of tanya and mishna!</h3>
            </div>
		</div>-->
        <!-- /.row -->
        <div class="row">
        	<!--
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
           -->
			<div class="col-lg-12 col-md-12 col-xs-12">
                <div class="panel panel-success">
                	<div class="panel-footer">
                        <span>תניא בעל פה + משניות בעל פה</span>
                        <div class="clearfix"></div>
                    </div>
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-12 text-center">
                                <div class="huge">
                                	<?=number_format($grandTotal['tanya']['learned'] + $grandTotal['mishna']['learned'])?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 col-xs-12">
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 col-xs-12">
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row -->
			
        <div class="row">
        	<div class="col-lg-12 col-md-12 col-xs-12">
	       		<div class="table-responsive">       
			        <table class="table table-striped table-bordered table-hover sortable">
			        	<thead>
					    	<tr>
					    		<th class='school'>Platoon</th>
					    		<th>Lines of<br />תניא בעל פה</th>
								<th>Avg per Child</th>
					    		<th>Lines of<br />משניות בעל פה</th>
								<th>Avg per Child</th>
					    	</tr>
				    	</thead>
				    	<tbody>
				    	<?
				    	$data = array();
                        $gradeInfo = array();
				    	foreach ($grades as $grade) {
                            $id = $grade['class_id'];
                            $name = $grade['class_grade'] . (empty($grade['class_sub']) ? '' : '-' . $grade['class_sub']);
                            $gradeInfo[$id] = $name;
				    		//$data[$school]['reg']     = $regInfo[$id];
							//$data[$school]['tanyaP']  = isset($results['tanya']['pledged'][$id]) ? $results['tanya']['pledged'][$id] : 0;
							$data[$name]['tanyaL']  = isset($results['tanya']['learned'][$id]) ? $results['tanya']['learned'][$id] : 0;
							//$data[$school]['mishnaP'] = isset($results['mishna']['pledged'][$id]) ? $results['mishna']['pledged'][$id] : 0;
							$data[$name]['mishnaL'] = isset($results['mishna']['learned'][$id]) ? $results['mishna']['learned'][$id] : 0;
				    	}
						
						$totals = array();
						foreach ($data as $grade => $info) {
							foreach ($info as $key => $value) {
								$totals[$key] = 0;
							}
							break;
						}
						
						$i = 0;
				    	foreach ($data as $grade => $info) {
                            $k = array_search($grade, $gradeInfo);
				    		echo "<tr><td><a href='soldierDetails.php?class_id=" . $k . "'>" . $grade . "</td>";		
				    		foreach ($info as $key => $value) {
				    			$totals[$key] += $value;
								echo "<td>";
								if ($value) echo number_format($value);
								else echo "n/a";
								echo "</td><td>";
								if ($value) echo number_format(floor($value / $regInfo[$k]));
								else echo "n/a";
								echo "</td>";
							}
							echo "</tr>";
				    	}
                        ?>
                        </tbody>
                        <tfoot>
                        <?						
						echo "<tr><th align='right'>Totals</th>";
						foreach ($totals as $key => $value) {
							echo "<th>" . number_format($value) . "</th>";
							echo "<th></th>";
						}
						echo "</tr>";
						
						echo "<tr><th colspan='6' style='text-align: center'>";
						?>
						
						תניא בעל פה לע״נ התמים נתן נטע בן הרה"ח ר' זלמן יודא דייטש ע"ה
						<br />
						משניות בעל פה לע"נ זאב ארי' ע"ה בן יבלט"א הרה"ח ר' שניאור זלמן שי' גליק
						</th></tr></tfoot>
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
    
	<script type="text/javascript" src="../countdown/TimeCircles/inc/TimeCircles.js"></script>
	<script>
	    $("#DateCountdown").TimeCircles();
	</script>
    
    <script src="/js/sortable.js"></script>
</body>
</html>
