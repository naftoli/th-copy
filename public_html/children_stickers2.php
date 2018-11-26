<?
$admin_auth = array('user'); 
require('header.php');

include("camps/includes/classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new \camps\classes\admin($row);
$admin->get_markable_children();

$children = array();
$childrenSelected = array();
$userPhoto = array();
foreach ( $admin->children as $child ) {
	$children[$child->user_id] = $child->first . " " . $child->last;
	$childrenSelected[$child->first . " " . $child->last] = $child->user_id;
	$userPhoto[$child->first . " " . $child->last] = $child->user_photo_id;
}

if (isset($_POST['submit'])) {
	//print_r($_POST['missions']);
	foreach ($childrenSelected as $k => $child) {
		if (!in_array($child, $_POST['children'])) unset($childrenSelected[$k]);
	}
}

function getRank($id) {
	$sql = "select rank_name, rank_image_id from ranks r 
			join rank_marks rm using (rank_ord) 
			where rm.user_id = $id 
			order by rank_ord desc 
			limit 1";
	//echo $sql;
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	return array('name' => $row['rank_name'], 'image' => $row['rank_image_id']);
}

function getMedal($subject, $user) {
	$sql = "select m.medal_name, ms.profile_photo_id 
			from medals m 
			join medal_marks mm using (medal_ord) 
			join medals_subjects ms using (medal_ord, subject_id) 
			where ms.subject_id = $subject 
			and mm.user_id = $user 
			order by medal_ord desc 
			limit 1";
	//echo $sql;
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	return array('name' => $row['medal_name'], 'image' => $row['profile_photo_id']);
}

function getMedalsRequired($subject, $medal) {
	$sql = "select missions_required from medals_subjects where subject_id = $subject and medal_ord = $medal";
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	return $row['missions_required'];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Sticker Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="scripts/jquery-ui-1.9.2.custom.min.css" />
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <script type="text/javascript" src="scripts/jquery-ui-1.9.2.custom.min.js"></script>
        <style type='text/css'>
            p {
                font-size: 12px;
            }
            table {
                font-size: 11px;
                margin-bottom: 30px;
            }
            th, td {
                padding: 3px 10px;
                border-bottom: 1px solid #C0C0C0;
                border-right: 1px solid #C0C0C0;
                text-align: center;
            }
            tr:first-child td {
            	border-top: 1px solid #C0C0C0;
            	font-size: 18px;
            	font-weight: bold;
            	padding: 10px;
            }
            td:first-child, th:first-child {
            	border-left: 1px solid #C0C0C0;
            }
            .missionSelection {
                width: 30%;
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
            img.sticker {
            	height: 40px;
            }
            .rank {
            	float: left;
            }
            .userPhoto {
            	float: right;
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
                $(".checkallChildren").click( function() {
                    $(".children").attr("checked", true);
                });
                $(".uncheckallChildren").click( function() {
                    $(".children").attr("checked", false);
                });
                
                $(".mission").each( function() {
                	if ($(this).val() != 21) { 
                		$(this).attr("checked", true);
                	}
                });
                
                $(".children").attr("checked", true);               
            });
        </script> 
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1 class="no-print">Sticker Report</h1>
        <?      
        //echo "<pre>";   
        require_once 'class.missionsDone.php';
        $missions = MissionsDone::getAllMissions();
		//print_r($missions);
		
        $medals = MissionsDone::getAllMedals();
		//print_r($medals);
		
        require_once 'class.stickers.php';
        $s = new Stickers;
        
		$missionsSelected = array();		
		//array to hold stickers info
		$stickers = array();
		$str = '';
		
		if (!isset($_POST['submit'])) {
			foreach ($missions as $id => $mission) {
				if (in_array($id, array(21,92,93,94))) continue;
				$str .= $id . ',';
				$missionsSelected[] = $id;
			}
		} else {
			foreach ($_POST['missions'] as $mission) {
				$str .= $mission . ',';
				$missionsSelected[] = $mission;
			}
		}
		$str = substr($str, 0, (strlen($str)-1));
		$sql = "select subject_id, subject_name, subject_image_id from subjects where subject_id in ($str)";
		$result = mysql_query( $sql );
		while ( $row = mysql_fetch_assoc( $result ) ) {
		    $stickers[$row['subject_id']][$row['subject_name']] = $row['subject_image_id'];
		}
		//print_r($stickers);
				
		$m = new MissionsDone();
		$m->setMissionsDone( $missionsSelected, $childrenSelected );
		$userMissions = $m->getMissionsDone();
        //print_r($userMissions);
		
		//echo "</pre>"; exit;
		
		echo "<div align='center' class='no-print'>";
        echo "<input type='button' value='Print' onclick='window.print()' />";
        echo "</div>";
		echo "<br />";	
		
		$modify = false;
		if (isset($_GET['modify']) && $_GET['modify'] == 1) {
			$modify = true;
		}
		
		if (!$modify) {
			echo "Click <a href='children_stickers2.php?modify=1'>here</a> to modify report.<br /><br />";
		}
		
		if ($modify) {
			?>
			<form action="children_stickers2.php" method="post">
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
                    	if ( $id > 90 ) continue;
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
                <br />
                <fieldset>
                	<legend>
                		Select Children
                	</legend>
                	<div align='center'>
	                    <input type='button' class='checkallChildren' value="Check All" />
	                    <input type='button' class='uncheckallChildren' value="Uncheck All" />
	                </div>
	                <?
	                foreach ( $children as $id => $name ) {
	                	echo "<div class='missionSelection'>
	                		<input type='checkbox' class='children' name='children[]' value='" . $id . "' />" . $name . "</div>";
	                }
	                ?>
                </fieldset>
 
                <div align='center'>
                    <br /><input type="submit" name="submit" value="Submit" />
                </div>
            </form>
			<?
		} else {
			foreach ( $userMissions as $user => $mission ) {
				$rank = getRank($childrenSelected[$user]); 
				$colspan = count($missionsSelected) + 1;  
				$img = $userPhoto[$user];
		        echo "<table>";
				echo "<tr><td colspan='$colspan'><img src='file_view.php?id=" . $rank['image'] . "' height='60' class='rank' />" . 
					$rank['name'] . " " . $user . "<img src='file_view.php?id=" . $img . "' height='60' class='userPhoto' /></td></tr>";
		        echo "<tr><th align='center'>Campaign</th>";
		        foreach ( $missionsSelected as $m ) {
		            echo "<th align='center'>" . $missions[$m] . "</th>";
		        }
		        echo "</tr>";
				
				echo "<tr><td>Highest Medal Earned</td>";
				foreach ($missionsSelected as $m) {
					$medal = getMedal($m, $childrenSelected[$user]);
					if ($medal['image']) {
						echo "<td><img src='file_view.php?id=" . $medal['image'] . "' height='40' /></td>";
					} else {
						echo "<td>&nbsp;</td>";
					}
				}
				echo "</tr>";
				
				echo "<tr><td>Missions Completed<br />(Stickers Earned)</td>";
	            foreach ( $missionsSelected as $m ) {
	                if ( !isset( $mission[$missions[$m]] ) ) {
	                    echo "<td>&nbsp;</td>";                            
	                } else {
	                    $missionsDone = $mission[$missions[$m]];
	                    $sticker = $s->calculateSticker( $m, $missionsDone );
	                    $k = key( $sticker );
	                    echo  "<td>";
						echo "<img class='sticker' src='images/stickers/Sticker-" . $stickers[$m][$missions[$m]] . ".gif'><br />";
						$req = (int)getMedalsRequired($m, $k);
						echo $sticker[$k] . "/" . $req . " of " . $medals[$k] . "</td>"; 
	                }
	            }
				echo "</tr></table>";
			}
		}
        ?>
    </body>
</html>