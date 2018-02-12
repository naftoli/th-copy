<?
$admin_auth = array('school', 'user'); 
require('header.php'); 

$id = $_GET['id'];
$start = $_GET['start'];
$end = $_GET['end'];
$subject = $_GET['subject'];
$task = $_GET['task'];

require_once 'class.personalizedReport.php';
$r = new PersonalizedReport($start, $end, array($id), $subject, array($task));
$report = $r->createDetailedReport();
//echo "<pre>"; print_r($report); echo "</pre>";

require_once 'class.parshos.php';
$parshos = Parshos::getParshos(5774);
$p = array();
foreach ($parshos as $parsha) {
	$p[$parsha['start']] = $parsha['name'];
}

$days = array("S", "M", "T", "W", "T", "F", "ש");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Detailed Personalized Progress Report</title>
        <link rel="stylesheet" href="jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
	    <script src="js/jquery-1.10.2.min.js"></script>
	    <script src="jqwidgets/jqwidgets/jqxcore.js"></script>
	    <script src="jqwidgets/jqwidgets/jqxchart.js"></script>
	    <script src="jqwidgets/jqwidgets/jqxdata.js"></script>
	    <script>
	    	$(function() {
	    		// prepare the data
	    		var data = [
	                {parsha : "bo"}, 
	                {parsha : "beshalach"}
	            ];
	
	            // prepare jqxChart settings
	            var settings = {
	                title: "Personalized Progress Report",
	                description: "Report showing progress of a certain task",
	                enableAnimations: true,
	                showLegend: true,
	                padding: { left: 5, top: 5, right: 5, bottom: 5 },
	                titlePadding: { left: 90, top: 0, right: 0, bottom: 10 },
	                source: data,
	                categoryAxis:
	                    {
	                        text: 'Category Axis',
	                        textRotationAngle: 0,
	                        dataField: 'parsha',
	                        showTickMarks: true,
	                        tickMarksInterval: 1,
	                        tickMarksColor: '#888888',
	                        unitInterval: 1,
	                        showGridLines: true,
	                        gridLinesInterval: 3,
	                        gridLinesColor: '#888888'
	                    },
	                colorScheme: 'scheme05',
	                seriesGroups:
	                    [
	                        {
	                            type: 'rangecolumn',
	                            columnsGapPercent: 100,
	                            valueAxis:
	                            {
	                                unitInterval: 5,
	                                displayValueAxis: true,
	                                description: 'Total times done',
	                                axisSize: 'auto',
	                                tickMarksColor: '#888888',
	                                minValue: -5,
	                                maxValue: 30
	                            },
	                            series: [
	                                    { dataFieldTo: 'max', displayText: 'Total times done', dataFieldFrom: 'min', opacity: 1 }
	                                ]                        
	                        },
	                        {
	                            type: 'spline',
	                            valueAxis:
	                            {
	                                unitInterval: 5,
	                                displayValueAxis: false,
	                                minValue: -5,
	                                maxValue: 30
	                            },
	                            series: [
	                                    { dataField: 'avg', displayText: 'Average times done', opacity: 1, lineWidth: 2 }
	                                ]
	                        }
	
	                    ]
	            };

	    		$("#chart").jqxChart(settings);
	    	});
	    </script>
	    
        <link href="admin_styles.css" rel="stylesheet" type="text/css" />
        <style type="text/css">
	        table {
			    font-size: 12px;
			}
			th, td {
			    padding: 3px 10px;
			}
			.newPage {
			    page-break-after: always;
			}
			fieldset {
			    border: 1px solid white;
			    padding: 10px;
			    padding-top: 0px;
			    -moz-border-radius: 10px;
			    -webkit-border-radius: 10px;
			    border-radius: 10px;
			    font-size: 14px;
			}
			legend {
			    margin-left: 20px;
			    padding: 5px;
			    color: purple;
			    font-size: 16px;
			}
			@media screen {
			    .instructions {
			        display: none;
			    }
			}
			@media print {
			    .instructions {
			        display: block;
			    }
			}
        	div.newListSelected  {
				margin-right: 100%;
			}
			#loading {
			    margin-top: 20px;
			}
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Detailed Personalized Progress Report</h1>
        
        <?
        $daily = false;
        foreach ($report as $user => $info) {
        	foreach ($info as $subject => $arr) {
        		foreach ($arr as $date => $info) {
        			foreach ($info as $t => $arr) {
        				foreach ($arr as $k => $v) {
        					if (is_array($v)) {
        						$daily = true;
							}
						}
					}
				}
			}
		}
		
		echo $task. "<br /><br />";
        echo "<table>";
		echo "<tr><th>Parsha</th>";
		if ($daily) {
			foreach ($days as $day) {
				echo "<th>" . $day . "</th>";
			}
		} else {
			echo "<th>Weekly Task</th>";
		}
		echo "</tr>";
		
        foreach ($report as $user => $info) {
        	foreach ($info as $subject => $arr) {
        		foreach ($arr as $date => $info) {
        			foreach ($info as $t => $arr) {
        				foreach ($arr as $k => $v) {
	        				echo "<tr><td>";
							if (array_key_exists($date, $p)) echo $p[$date];
							echo "</td>";
							if ($daily) {
								foreach ($v as $value) {
									echo "<td>";
									if ($value) echo "&#x2713;";
									else echo "x";
									echo "</td>";
								}
							} else {
								echo "<td>";
								if ($v) echo "&#x2713;";
								else echo "x";
								echo "</td>";
							}
							echo "</tr>";
						}
        			}
        		}
        	}
        }
        ?>
        </table>
        <br />
        <div id="chart" style="width:680px; height:400px"></div>
    </body>
</html>