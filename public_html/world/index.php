<?php
//ini_set('display_errors',1);
require '../db.php';
require '../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

// get campaigns for current year
$sql = "SELECT * FROM line_campaigns WHERE year = " . $year;
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
	$campaigns[$row['id']] = strtolower( $row['type'] );
}

$byGrade = false;
$info = array();
if (isset($_GET['grade'])) {
	$byGrade = true;
	$grade = mysql_real_escape_string( $_GET['grade'] );
	$sql = "SELECT c.*, s.school_name FROM classes c
			JOIN schools s USING (school_id)
			WHERE class_era = 0
			AND s.school_era is null
			AND s.tanya = 1
			AND class_grade = '" . $grade . "'
			ORDER BY class_grade, class_sub, school_name";
	$result = mysql_query( $sql );
	while ($row = mysql_fetch_assoc( $result )) {
		$info[$row['class_id']] = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']) . " : " . $row['school_name'];
	}
	//echo "<pre>"; print_r( $grades ); echo "</pre>";
} else {
	//$sql = "select school_id, school_name from schools where school_era is null and school_id not in (247,66,79,198,199,82,240,241,255,265,269,272,286) order by school_name";
	$sql = "SELECT school_id, school_name FROM schools WHERE school_era IS NULL AND tanya = 1 ORDER BY school_name";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$info[$row['school_id']] = $row['school_name'];
	}
}

$regInfo = array();
foreach ($info as $id => $desc) {
	if (isset($_GET['grade'])) $rSql = "SELECT count(*) AS total FORM users WHERE class_id = $id AND (user_registered > 0 OR yan = 1)";
	else $rSql = "SELECT count(*) AS total FROM users WHERE school_id = $id AND (user_registered > 0 OR yan = 1)";
	$rResult = mysql_query($rSql);
	$rRow = mysql_fetch_assoc($rResult);
	$registered = $rRow['total'];
	$regInfo[$id] = $registered ? $registered : 0;
}
//echo "<pre>"; print_r($regInfo); echo "</pre>"; exit;

require_once '../class.bpSummary.php';
//require_once '../class.balPehCampaign.php';
$results = [];
$child_count = [];
foreach ($campaigns as $id => $campaign) {
	//$bp = BalPehCampaign::getInstance( $id );
	if ($byGrade) $bps = new BpSummary( $id, 'class' );
	else $bps = new BpSummary( $id, 'school' );
	//$grandTotal[$campaign]['pledged'] = 0;
	$grandTotal[$campaign]['learned'] = 0;
	foreach ($info as $id => $desc) {
		//$pledged = $bp->getTotalPledged( 'school', $school_id );
		$learned = $bps->getSummary( $id );
		$child_count[$id] = $bps->getChildCount( $id ); // get the total number of kids for the campaign...

		// use the regInfo number if it is higher...
		$child_count[$id] = $regInfo[$id] > $child_count[$id] ? $regInfo[$id] : $child_count[$id];

		if ($learned == '') $learned = 0;
		if ($learned == 0) continue;
		//$results[$campaign]['pledged'][$school_id] = $pledged;
		$results[$campaign]['learned'][$id] = $learned;
		//$grandTotal[$campaign]['pledged'] += $results[$campaign]['pledged'][$school_id];
		$grandTotal[$campaign]['learned'] += $results[$campaign]['learned'][$id];
	}
}

//echo "<pre>"; print_r([$child_count, $regInfo]); echo "</pre>"; exit;
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
		table.sortable th:not(.sorttable_sorted):not(.sorttable_sorted_reverse):not(.sorttable_nosort):after {
			content: " \25B4\25BE"
		}
    </style>
    <link rel="stylesheet" href="../countdown/TimeCircles/inc/TimeCircles.css" />
</head>

<body>
	<!--
	<div class="thermometer1">
	    <canvas class="demo" height="400px" width="350px"></canvas>
	    <div class="thermLabel">תניא</div>
	</div>

	<div class="thermometer2">
	    <canvas class="demo2" height="400px" width="350px"></canvas>
	    <div class="thermLabel">משנה</div>
	</div>
	-->
    <div id="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">The Rebbe's Birthday Present</h1>
            </div>
            <!-- /.col-lg-12 -->
        </div>
		<!--
		<div class="row">
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
        <!--
        <div class="row">
            <div class="col-lg-12 col-md-12 col-xs-12">
            	<div id="DateCountdown" data-date="2015-03-30 19:51:00" style="width: 500px; height: 125px; padding: 0px; box-sizing: border-box; background-color: #E0E8EF"></div>
            </div>
            <!-- /.col-lg-12
        </div>
        -->
		<div class="row">
        	<div class="col-lg-12 col-md-12 col-xs-12">
				<div style="font-size: 18px; font-weight: bold;">
					<div align="center">
						<!--<iframe width="650px" height="430px" src="https://www.youtube.com/embed/-WueRB1OVdo" frameborder="0" allowfullscreen></iframe>-->
						<iframe src="https://player.vimeo.com/video/207512639" width="640" height="433" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
						<!--<p><a href="https://vimeo.com/207512639">The Rebbe tells us what he wants for his birthday present</a> from <a href="https://vimeo.com/tzivoshashem">Tzivos Hashem</a> on <a href="https://vimeo.com">Vimeo</a>.</p>-->
					</div>
				</div>
			</div>
		</div>

		<div class="row">
        	<div class="col-lg-6 col-md-12 col-xs-12">
				<div class="alert alert-success" align="center">
					<h1>
						<a href="https://www.dropbox.com/sh/uoeb0cv5op8h4t5/AAD3JOzVP2VE3_sdKdYpaCLla?dl=0" target="_blank">TBP Resources</a>
					</h1>
				</div>
			</div>
			<div class="col-lg-6 col-md-12 col-xs-12">
				<div class="alert alert-success" align="center">
					<h1>
						<a href="https://www.dropbox.com/sh/dt9siuwih6v6vv9/AACr8VM0ocKWDMdCQth3MTD-a?dl=0" target="_blank">MBP Resources</a>
					</h1>
				</div>
			</div>
		</div>

		<div class="row">
        	<div class="col-lg-12 col-md-12 col-xs-12">
				Show By: <a href="index.php">School</a><br />
				OR Choose Grade:
				<select name="grade" id="grade">
					<option value='0'>Choose Grade</option>
					<option value='pre1a'>Pre1A</option>
					<?php
					for ($i = 1; $i < 9; $i++) {
						echo "<option value='" . $i . "'>" . $i . "</option>";
					}
					?>
				</select>
			</div>
		</div>

        <div class="row">
        	<div class="col-lg-12 col-md-12 col-xs-12">
	       		<div class="table-responsive">
			        <table class="table table-striped table-bordered table-hover sortable">
			        	<thead>
					    	<tr>
					    		<th class='school'><?=$byGrade?'Grade':'School'?></th>
					    		<th>Lines of<br />תניא בעל פה</th>
								<th id="defaultSort">Avg per Child</th>
					    		<th>Lines of<br />משניות בעל פה</th>
								<th>Avg per Child</th>
					    	</tr>
				    	</thead>
				    	<tbody>
				    	<?
				    	$data = array();
				    	foreach ($info as $id => $desc) {
				    		//$data[$school]['reg']     = $regInfo[$id];
							//$data[$school]['tanyaP']  = isset($results['tanya']['pledged'][$id]) ? $results['tanya']['pledged'][$id] : 0;
							$data[$desc]['tanyaL']  = isset($results['tanya']['learned'][$id]) ? $results['tanya']['learned'][$id] : 0;
							//$data[$school]['mishnaP'] = isset($results['mishna']['pledged'][$id]) ? $results['mishna']['pledged'][$id] : 0;
							$data[$desc]['mishnaL'] = isset($results['mishna']['learned'][$id]) ? $results['mishna']['learned'][$id] : 0;
				    	}

						//echo "<pre>"; print_r( $data ); echo "</pre>"; exit;
						$totals = array();
						foreach ($data as $desc => $details) {
							foreach ($details as $key => $value) {
								$totals[$key] = 0;
							}
							break;
						}

						// figure out default sort
						$sorted = array();
						foreach ($data as $desc => $details) {
							$k = array_search($desc, $info);
							$avg = $details['tanyaL'] ? floor($details['tanyaL'] / $child_count[$k]) : 0;
							$sorted[$desc] = $avg;
						}
						arsort( $sorted );

						foreach ($sorted as $desc => $avg) {
				    	//foreach ($data as $desc => $details) {
							// get school/class id
							$k = array_search($desc, $info);
							?>
							<tr>
								<td>
									<? if ($byGrade) { ?>
										<a href='soldierDetails.php?class_id=<?=$k?>'><?=$desc?></a>
									<? } else { ?>
										<a href='platoonDetails.php?school_id=<?=$k?>'><?=$desc?></a>
									<? } ?>
								</td>
							<? foreach ($data[$desc] as $key => $value) {
				    			$totals[$key] += $value; ?>
								<td>
									<?= $value ? number_format($value) : "n/a"?>
								</td>
								<td>
									<?= $value ? number_format(floor($value / $child_count[$k])) : "n/a"?>
								</td>
							<? } // end foreach campaign... ?>
							</tr>
				    	<? } // end foreach school... ?>
						</tbody>
						<tfoot>
							<tr>
								<th align='right'>Totals</th>
								<? foreach ($totals as $key => $value) { ?>
									<th><?=number_format($value)?></th>
									<th></th>
								<? } // end for each totals... ?>
							</tr>
							<tr>
								<th colspan='6' style='text-align: center'>
									תניא בעל פה לע״נ התמים נתן נטע בן הרה"ח ר' זלמן יודא דייטש ע"ה
									<br />
									משניות בעל פה לע"נ זאב ארי' ע"ה בן יבלט"א הרה"ח ר' שניאור זלמן שי' גליק
								</th>
							</tr>
						</tfoot>
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

	<script src="/js/sortable.js"></script>

	<script>
		/*
		var w = $('.demo').width();
	    var h = $('.demo').height();

	    $('.demo').thermometer({
	        w: w,
	        h: h,
	        color: {
	            label: 'rgba(255, 255, 255, 1)',
	            tickLabel: 'rgba(255, 0, 0, 1)'
	        },
	        centerTicks: true,
	        majorTicks: 1,
	        minorTicks: 1,
	        max: 500000,
	        min: 0,
	        scaleTickLabelText: 0.1,
	        scaleLabelText: 1.0,
	        scaleTickWidth: 0.1,
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
	        centerTicks: true,
	        majorTicks: 1,
	        minorTicks: 1,
	        max: 500000,
	        min: 0,
	        scaleTickLabelText: 0.1,
	        scaleLabelText: 1.0,
	        scaleTickWidth: 0.1,
	        unitsLabel: ""
	    });

		var total = <?=$grandTotal['mishna']['learned']?>;
	    $('.demo2').thermometer('setValue', parseInt(total));
	    */
	    $("#DateCountdown").TimeCircles();

		$("#grade").change( function() {
			var val = $(this).val();
			if (val > 0) {
				location.href="index.php?grade=" + val;
			}
		});

		$(function() {
			$("#defaultSort").click();
		});
	</script>
</body>
</html>
