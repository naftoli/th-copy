<?
$admin_auth = array('school', 'user'); 
require('header.php');

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
        <title>Total Stickers Earned</title>
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
            td:nth-child(2) {
            	text-align: left;
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
            img.sticker {
            	height: 40px;
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
            });
        </script> 
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1 class="no-print">Total Stickers Earned</h1>
        <? 
        require_once 'class.adminSchools.php';      
        $as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
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
            
            //print_r( $_POST );            
            $missionsPosted = $_POST['missions'];
			
			//array to hold stickers info
			$stickers = array();
			$str = implode( ',', $missionsPosted );
			$sql = "select subject_id, subject_name, subject_image_id from subjects where subject_id in ($str)";
			$result = mysql_query( $sql );
			while ( $row = mysql_fetch_assoc( $result ) ) {
			    $stickers[$row['subject_id']][$row['subject_name']] = $row['subject_image_id'];
			}
            
            foreach ( $schools as $id => $school ) {
                $m = new MissionsDone( $id );
                if ( isset( $_POST['classes'] ) ) 
                    $m->setClasses( $_POST['classes'] );
                $m->setMissionsDone( $missionsPosted );
                $userMissions[$id] = $m->getMissionsDone();
				$userInfo[$id] = $m->getUsers();
            }
            
            echo "<div align='center' class='no-print'>";
            echo "<input type='button' value='Print' onclick='window.print()' />";
            echo "</div>";
			
			echo "<div class='infobox no-print'>If your report is too wide to fit on the page please change your Printer Settings to 'Landscape' before printing.</div>";
                        
            foreach ( $userMissions as $school => $users ) {
                foreach ( $users as $class => $user ) {
                	echo "<h2>" . $schools[$school] . " Platoon " . $class . "</h2>";
	                echo "<table>";
	                echo "<tr><th>Soldier</th>";
	                foreach ( $missionsPosted as $mp ) {
	                    echo "<th align='center'><img class='sticker' src='images/stickers/Sticker-" . $stickers[$mp][$missions[$mp]] . ".gif'><br />". $missions[$mp] . "</th>";
	                }
	                echo "</tr>";
                    foreach ( $user as $name => $mission ) {
                    	$rank = getRank(array_search($name, $userInfo[$school]));
                        echo "<tr><td>" . $rank['name'] . ' ' . $name . "</td>"; 
                        foreach ( $missionsPosted as $m ) {
                            if ( !isset( $mission[$missions[$m]] ) ) {
                                echo "<td>&nbsp;</td>";                            
                            } else {
                                $missionsDone = $mission[$missions[$m]];
                                $sticker = $s->calculateSticker( $m, $missionsDone );
                                $k = key( $sticker );
								$req = (int)getMedalsRequired($m, $k);
                                //echo  "<td>" . $missionsDone . ' total ' . $sticker[$k] . "/" . $req . " to " . $medals[$k] . "</td>"; 
                                echo  "<td>" . $sticker[$k] . "/" . $req . " to " . $medals[$k] . "</td>"; 
							}
                        }
                        echo "</tr>";
                    }
                    echo "</table><br />";
                	echo "<div class='page-break'></div>";
                }             
            }
        } else {          
            ?>
            <form action="stickers_report.php" method="post">
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
							if ($id != 21)
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
                <div align='center'>
                    <br /><input type="submit" name="submit" value="Submit" />
                </div>
            </form>
            <?          
       }
    ?>
    </body>
</html>