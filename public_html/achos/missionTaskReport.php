<?
$admin_auth = array('school'); 
require('header.php');
require_once('calendar.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Mission Task Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
        <style type='text/css'>
			table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
                border-bottom: 1px black solid;
                border-right: 1px black solid;
            }
            .last {
            	border-right: none;
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
        </style>
        <script type="text/javascript">
            $( function() {
                $(".checkall").click( function() {
                    $(".mission").attr("checked", true);
                });
                $(".uncheckall").click( function() {
                    $(".mission").attr("checked", false);
                });
                $("input[name=submit]").click( function() {
                	if ( !$(".mission").is(":checked") ) {
                		alert("You must choose at least one mission.");
                		return false;
                	}
                });
            });
        </script> 
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Mission Task Report</h1>
        <?
        if ( isset( $_POST['submit'] ) ) {
	        $campaigns = $_POST['missions'];
			$start = $_POST['start_date'];
			$end = $_POST['end_date'];
	        $sql = "select dt.*, dtm.*, s.subject_name, st.school_type_name, l.label_name 
	        		from date_tasks_missions dtm 
	        		join date_tasks dt using (date_tasks_mission_id) 
	        		join subjects s using (subject_id) 
	        		join school_types st using (school_type_id) 
	        		left join labels l using (label_id) 
	        		where dtm.subject_id in (" . implode( ',', $campaigns ) . ") 
	        		and dtm.start_date >= $start 
	        		and dtm.end_date <= $end 
	        		group by dtm.school_type_id, dtm.level, dtm.track_id, 
	        		dt.name, dt.description, dt.quantity, dt.points  
	        		order by subject_id, name, school_type_id, level, track_id";
			//echo $sql;
			$result = mysql_query( $sql ) or die( $sql . "<br />" . mysql_error() );
			?>
			<table>
				<tr>
					<th>Mission ID</th>
					<th>School Type</th>
					<th>Campaign</th>
					<th>Mission</th>
					<th>Mission Number</th>
					<th>Mission Group</th>
					<th>Mission Value</th>
					<th>Task ID</th>
					<th>Order</th>
					<th>Task</th>
					<th>Description</th>
					<th>Year</th>
					<th>Ladder</th>
					<th>Mandatory</th>
					<th>Optional</th>
					<th>Bonus</th>
					<th>Label</th>
					<th>Quantity</th>
					<th>Points</th>
					<th>Sequence Number</th>
					<th>Daily Task</th>
					<th>Needed</th>
					<th class="last">Focus Task</th>
				</tr>
			<?
			while( $row = mysql_fetch_assoc($result) ) {
				echo "<tr><td>" . $row['date_tasks_mission_id'] . "</td><td>" . 
					$row['school_type_name'] . "</td><td>" . 
					$row['subject_name'] . "</td><td>" . 
					$row['mission_name'] . "</td><td>" . 
					$row['mission_number'] . "</td><td>" . 
					$row['mission_group'] . "</td><td>" . 
					$row['mission_value'] . "</td><td>" . 
					$row['date_task_id'] . "</td><td>" . 
					$row['ord'] . "</td><td>" . 
					$row['name'] . "</td><td>" . 
					$row['description'] . "</td><td>" . 
					$row['level'] . "</td><td>" . 
					$row['track_id'] . "</td><td>" . 
 					($row['mandatory_qty'] ? 'y' : 'n') . "</td><td>" . 
					($row['optional_qty'] ? 'y' : 'n') . "</td><td>" . 
					($row['is_bonus'] ? 'y' : 'n') . "</td><td>" . 
					$row['label_name'] . "</td><td>" . 
					$row['quantity'] . "</td><td>" . 
					$row['points'] . "</td><td>" . 
					$row['sequence_number'] . "</td><td>" . 
					($row['daily_task'] ? 'y' : 'n') . "</td><td>" . 
					($row['needed'] ? 'y' : 'n') . "</td><td class='last'>" . 
					($row['focus_task'] ? 'y' : 'n') . "</td></tr>";
			}
			echo "</table>";
		} else {
			require_once 'class.missionsDone.php';
        	$missions = MissionsDone::getAllMissions();
			?>
			<form action="missionTaskReport.php" method="post">
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
                        echo "<input type='checkbox' class='mission' name='missions[]' value='" . $id . "' />" . $mission . "<br />";
                    }
                    echo "</div>";
                }
                ?>
                <div style="clear: both"></div>
                </fieldset>
                <fieldset>
                <legend>
                    Select Dates
                </legend>
                <span class='dates'>
                    <INPUT type="hidden" name="start_date" value="<?=2456166?>">
                    <LABEL>
                        From: 
                        <INPUT type="text" name="start_date_disp" READONLY value="<?=es(dateToHebrew(2456166))?>" onClick="getDate(this.form, 'start_date', true);"/>
                    </LABEL>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <INPUT type="hidden" name="end_date" value="<?=2456530?>">
                    <LABEL>
                        To: 
                        <INPUT type="text" name="end_date_disp" READONLY value="<?=es(dateToHebrew(2456530))?>" onClick="getDate(this.form, 'end_date', true);"/>
                    </LABEL>
                </span>
        	</fieldset>
                <div align='center'>
                    <br /><input type="submit" name="submit" value="Submit" />
                </div>
            </form>
			<?
		}
    ?>
    </body>
</html>