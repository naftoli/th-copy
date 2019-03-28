<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Total Stickers Earned</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="scripts/jquery-ui-1.9.2.custom.min.css" />
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <script type="text/javascript" src="scripts/jquery-ui-1.9.2.custom.min.js"></script>
        <style type='text/css'>
            table {
                font-size: 11px;
            }
            th, td {
                padding: 3px 10px;
                border-bottom: 1px solid #C0C0C0;
                border-right: 1px solid #C0C0C0;
            }
            .missionSelection {
                width: 30%;
                float: left;
                line-height: 1.5;
                margin-top: 10px;
            }
            .classSelection {
                width: 25%;
                float: left;
                line-height: 1.5;
                margin-top: 10px;
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
            .totals {
                border-top: 1px dashed purple;
                border-bottom: 1px dashed purple;
            }
            .classes {
                margin: auto;
            }
            .grade, .name, .sticker, .reportDates {
            	font-size: 14px;
            	padding: 6px;
            }
            .sticker img {
            	height: 40px;
            	vertical-align: middle;
            	padding-right: 12px; 
            }
        </style>
        <script type="text/javascript">
            $( function() {
                $(".checkall").click( function() {
                    $(".mission").attr("checked", true);
                });
                $(".uncheckall").click( function() {
                    $(".mission").attr("checked", false);
                });
                
                $(".checkallClasses").click( function() {
                    $(".class").attr("checked", true);
                });
                $(".uncheckallClasses").click( function() {
                    $(".class").attr("checked", false);
                });
                
                $(".class").attr("checked", true); 
                
                $("input[type=submit]").click( function() {
                	if ( $("select[name=from] option:selected").val() > $("select[name=to] option:selected").val() ) {
                		alert( "From week cannot be ahead of to week!" );
                		return false;
                	}
                	if ( !$(".mission:checked").val() ) {
                		alert( "You must choose at least one mission!" );
                		return false;
                	}
                	return true;
                });               
            });
        </script> 
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1 class="no-print">Total Stickers Earned</h1>
        <? 
        require_once 'class.adminSchools.php';      
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        
        require_once 'class.missionsDone.php';
        $missions = MissionsDone::getAllMissions();
        $medals = MissionsDone::getAllMedals();
        
        require_once 'class.stickers.php';
        $s = new Stickers;
        
        if ( isset( $_POST['submit'] ) ) {
            if ( !isset( $_POST['missions'] ) ) {
                echo "Please go back and choose at least one mission.";
                exit;
            }
			
			$missionsPosted = $_POST['missions'];
			
			//array to hold stickers info
			$stickers = array();
			$subjects = array();
			$str = implode( ',', $missionsPosted );
			$sql = "select subject_id, subject_name, subject_image_id from subjects where subject_id in ($str)";
			$result = mysql_query( $sql );
			while ( $row = mysql_fetch_assoc( $result ) ) {
			    $stickers[$row['subject_id']] = $row['subject_image_id'];
				$subjects[$row['subject_name']] = $row['subject_id'];
			}
			
            $start = $_POST['from'];
			$sql = "SELECT * FROM reports WHERE report_type='mission_cover_sheet' AND visibility != 'none' and start_date = " . $start;	
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			$start_report = $row['report_name'];
			
			$end = $_POST['to'];
			$sql = "SELECT * FROM reports WHERE report_type='mission_cover_sheet' AND visibility != 'none' and end_date = " . $end;	
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			$end_report = $row['report_name'];
             
            foreach ($schools as $id => $school) {
                $m = new MissionsDone($id);
                if (isset($_POST['classes'])) 
                    $m->setClasses($_POST['classes']);
				$m->setDates($start, $end);
                $m->setMissionsDonePerChild($missionsPosted);
                $userMissions[$id] = $m->getMissionsDone();
				$userIDs[$id] = $m->getUserIDs();
				$parshos[$id] = $m->getWeeks();
            }
			
			echo "<pre>";
			//print_r($userMissions);
			echo "</pre>";
			//exit;
            ?>
            <div align='center' class='no-print'>
            <input type='button' value='Print' onclick='window.print();' />
			<input type='button' value='Export To CSV' onclick='export_csv();' />
            </div>
			<?
            foreach ($userMissions as $school => $users) { 
                echo "<h2>" . $schools[$school] . "</h2>";
                echo "<div class='page-break'></div>";

                foreach ($users as $class => $user) {
                	foreach ($user as $name => $week) {
                		//echo "<pre>"; print_r($week); echo "</pre>";
						echo "<div class='result'>";
                		echo "<div class='grade'>Grade: " . $class . "</div>";
                        echo "<div class='name'>Student: " . $name . "</div>";
						echo "<div class='reportDates'>For the weeks of:</div>";
						echo "<table><tr>";
						foreach ($parshos[$school] as $parsha) {
							echo "<th>" . $parsha['name'] . "</th>";
						}
						echo "</tr><tr>";
						foreach ($parshos[$school] as $parsha) {
							echo "<td valign='top'><div class='sticker'>";
							if (isset($week[$parsha['name']])) {
		                    	foreach ($week[$parsha['name']] as $mission => $total) {
		                    		$subject_id = $subjects[$mission];
			                        echo  "<img src='images/stickers/Sticker-" . $stickers[$subject_id] .  ".gif'><br />" . $missions[$subject_id] . '-' . $total . "<br />"; 
								}
							}
							echo "</div></td>";
						}
						echo "</tr></table><div class='page-break'></div></div>";
                    }
                } 
            }
        } else {          
            ?>
            <form action="stickers_report_by_child.php" method="post">
                Check off the missions that you would like for the report:<br /><br />
                <fieldset>
                    <legend>
                        Select Missions
                    </legend>
                <div align='center'>
                    <input type='button' class='checkall' value="Check All" />
                    <input type='button' class='uncheckall' value="Uncheck All" />
                </div>
                <?
                //calculate how many missions will be showing and show into two columns
                $numMissions = count( $missions );
                if ( $numMissions > 1 ) {
                    $middle = ceil( $numMissions / 3.0 );
                    echo "<div class='missionSelection'>";
                    $i = 0;
                    foreach ( $missions as $id => $mission ) {
                        if ( $i++ == $middle ) {
                            $middle *= 2;
                            echo "</div><div class='missionSelection'>";
                        }
                        echo "<input type='checkbox' class='mission' name='missions[]' value='" . $id . "' ";
						if ($id <= 90) {
							echo "checked='checked' ";
						}
						echo "/>" . $mission . "<br />";
                    }
                    echo "</div>";
                }
                ?>
                <div style="clear: both"></div>
                </fieldset>
                <br />
                <? if ( count( $schools ) == 1 ) { ?>
                    <br />
                    <fieldset>
                        <legend>
                            Select Class(es)
                        </legend>
                        <div align='center'>
                            <input type='button' class='checkallClasses' value="Check All" />
                            <input type='button' class='uncheckallClasses' value="Uncheck All" />
                        </div>
                        <?
                        //get classes
                        $school_id = null;
                        $classes = array();
                        foreach ( $schools as $id => $school ) {
                            $school_id = $id;
                        }
                        $sql = "select class_id, class_grade, class_sub 
                                from classes 
                                where school_id = " . $school_id . " 
                                and class_era = 0 
                                order by class_grade, class_sub";
                        $result = mysql_query( $sql );
                        while ( $row = mysql_fetch_assoc( $result ) ) {
                            $classes[] = $row;
                        }
                        
                        //calculate how many classes will be showing and show into 4 columns
                        $numClasses = count( $classes );
                        if ( $numClasses > 1 ) {
                            $column = ceil( $numClasses / 4.0 );
                            $newColumn = $column;
                            echo "<div class='classSelection'>";
                            $i = 0;
                            foreach ( $classes as $class ) {
                                if ( $i++ == $newColumn ) {
                                    $newColumn += $column;
                                    echo "</div><div class='classSelection'>";
                                }
                                echo "<input type='checkbox' class='class' name='classes[]' value=" . $class['class_id'] . " /> " . 
                                    $class['class_grade'] . ( empty( $class['class_sub'] ) ? '' : "-" . $class['class_sub'] ) . "<br />";
                            }
                            echo "</div>";
                        }
                        ?>
                    </fieldset>
                <? } ?>
                <br />
                <fieldset>
	                <legend>
	                    Select Dates
	                </legend>
	                <span class='dates'>
                        <?php
	                	require_once 'class.globalSettings.php';
                        $curDates = GlobalSettings::getCurYearDates();
	                    $dates = array();
						$sql = "SELECT * FROM reports
                                WHERE report_type='mission_cover_sheet'
                                AND visibility != 'none'
                                and start_date > " . $curDates['start'] . "
                                ORDER BY start_date";	
						$result = mysql_query($sql);
						while ($row = mysql_fetch_assoc($result)) {
							$dates[] = $row;
						}
						?>
						From beginning of: <select name='from'>
						<?
						foreach ($dates as $date) {
							echo "<option value='" . $date['start_date'] . "'>" . $date['report_name'] . "</option>";
						}
						?>
						</select><br />
						Until end of: <select name='to'>
						<?
						foreach ($dates as $date) {
							echo "<option value='" . $date['end_date'] . "'>" . $date['report_name'] . "</option>";
						}
						?>
						</select><br />
	                </span>
	        	</fieldset>
	        	
                <div align='center'>
                    <br /><input type="submit" name="submit" value="Submit" />
                </div>
            </form>
            <?          
       }
    ?>
	<script>
		function export_csv() {
			alert("Please note that is feature is in BETA and may not work correctly in all readers. It has been only been tested with Microsoft Excel 2016 and your milage may vary.");
			var rows = []; // the rows for the csv export
			var csvContent = ""; // the baisc csv file
			var universalBOM = "\uFEFF";
			// TODO add headers
            $.each($(".result"), function(index, item) {
				item = $(item); // cast to jquery;
				var row = [item.find(".name").text().replace('Student: ',''), item.find(".grade").text().replace('Grade: ','')];
				$.each(item.find("td"), function(index, item) {
					row.push($(item).text());
				});
				rows.push(row); // add the row to the csv export
				row = row.join(",");
				csvContent += row + "\n";
			});
			
			var hiddenElement = document.createElement('a');
			hiddenElement.href = "data:text/csv;charset=utf-8," + encodeURIComponent(universalBOM+csvContent); // set the data
			hiddenElement.target = '_blank'; // in a new tab
			hiddenElement.download = 'stickers_by_child.csv'; // with this file_name
			hiddenElement.click(); // and click it
        }
	</script>
    </body>
</html>