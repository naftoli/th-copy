<?
ini_set('display_errors', 1);
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

function generatePdf() {
	/*
	require_once 'simple_html_dom.php';
	$content = file_get_html();
	require_once 'pdfcrowd/pdfcrowd.php';
	try
	{   
	    // create an API client instance
	    $client = new Pdfcrowd("naftolir", "165af2db10b5431b75b4f8ea242356e4");
		
		// convert a web page and store the generated PDF into a $pdf variable
	    $pdf = $client->convertHtml($content);
	    
	    // set HTTP response headers
	    header("Content-Type: application/pdf");
	    header("Cache-Control: no-cache");
	    header("Accept-Ranges: none");
	    header("Content-Disposition: attachment; filename=\"status_report.pdf\"");
	
	    // send the generated PDF 
	    echo $pdf;
	}
	catch(PdfcrowdException $why)
	{
	    echo "Pdfcrowd Error: " . $why;
	}
	 * 
	 */
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Status Report</title>
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
                padding: 5px;
                border-bottom: 1px solid #C0C0C0;
                border-right: 1px solid #C0C0C0;
                text-align: center;
                vertical-align: top;
                width: 80px;
            }
            tr:first-child th {
            	border-top: 1px solid #C0C0C0;
            	font-size: 13px;
            	font-weight: bold;
            	padding: 10px;
            }
            tr:first-child th:nth-child(1), tr:first-child th:nth-child(2) {
            	vertical-align: middle;
            }
            td:first-child, th:first-child {
            	border-left: 1px solid #C0C0C0;
            }
            td:nth-child(1) {
            	vertical-align: middle;
            }
            td:nth-child(2) {
            	padding: 0;
            	vertical-align: middle;
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
            img.holder {
            	opacity: 0.65;
            }
            .statusReport {
            	margin-left: auto;
            	margin-right: auto;
            }
        </style>
        <script type="text/javascript">
            $(function() {
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
            
            function printToPdf() {
            	var content = $("html").html();
            	$.post('ajax/createPDF.php', {content : content});
            }
        </script> 
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1 class="no-print">Status Report</h1>
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
			
			$chosen = array();
			if (isset($_POST['schools'])) {
				foreach ($_POST['schools'] as $id) {
					$chosen[] = $id;
				}
			} else {
				foreach ($schools as $id => $name) {
					$chosen[] = $id;
				}
			}
			
			$numChildren = $_POST['numChildren'];
            
            foreach ($chosen as $id) {
                $m = new MissionsDone($id);
                if (isset($_POST['classes'])) 
                    $m->setClasses($_POST['classes']);
                $m->setMissionsDone($missionsPosted);
                $userMissions[$id] = $m->getMissionsDone();
				$userInfo[$id] = $m->getUsers();
            }
			
			/*
			echo "<pre>";
			print_r($userMissions);
			echo "</pre>";
			exit;
			*/
			
			//get all photos of soldiers
			$userPhotos = array();
			foreach ($userInfo as $school => $info) {
				$sql = "select user_id, user_photo_id from users where user_id in (" . implode(',', array_keys($info)) . ")";
				$result = @mysql_query($sql);
				while ($row = @mysql_fetch_assoc($result)) {
					$userPhotos[$row['user_id']] = $row['user_photo_id'];
				}
			}
            
            echo "<div align='center' class='no-print'>";
            echo "<input type='button' value='Print' onclick='window.print()' />";
            //echo "<input type='button' value='Print to PDF' onclick='printToPdf()' />";
            echo "</div>";
			
			echo "<div class='infobox no-print'>If your report is too wide to fit on the page please change your Printer Settings to 'Landscape' before printing.</div>";
			
			echo "<div class='statusReport' align='center'>";            
            foreach ( $userMissions as $school => $users ) {
                foreach ( $users as $class => $user ) {
                	echo "<h2>" . $schools[$school] . " Platoon " . $class . "</h2>";
	                echo "<table>";
	                echo "<tr><th>Rank</th><th>Soldier</th>";
	                foreach ( $missionsPosted as $mp ) {
	                    echo "<th align='center'><img class='sticker' src='images/stickers/Sticker-" . $stickers[$mp][$missions[$mp]];
						if ($mp == 100) echo ".jpg";
						else echo ".gif";
						echo "'><br />". $missions[$mp] . "</th>";
	                }
	                echo "</tr>";
	                $i = 1;
                    foreach ( $user as $name => $mission ) {
                    	$user_id = array_search($name, $userInfo[$school]);
                    	$rank = getRank($user_id);
                        echo "<tr><td>";
                        if (!empty($rank['image'])) {
                        	echo "<img src='file_view.php?id=" . $rank['image'] . "' height='60' />";
						} else {
							echo $name;	
						}
                        echo "</td><td>";
                        if (key_exists($user_id, $userPhotos) && !empty($userPhotos[$user_id])) {
                        	echo "<img src='file_view.php?id=" . $userPhotos[$user_id] . "' height='60' />"; 
                        } else {
                        	echo $name;
                        }
						echo "</td>";
                         
                        foreach ( $missionsPosted as $m ) {
                            if ( !isset( $mission[$missions[$m]] ) ) {
                            	//check if user is enrolled
                            	$sql = "select * from user_tracks where user_id = $user_id and subject_id = $m and enrolled = 1";
								$result = mysql_query($sql);
								if (mysql_num_rows($result)) {
									$sticker = $s->calculateSticker( $m, 0 );
                                	$k = key( $sticker );
                                	$req = (int)getMedalsRequired($m, $k);
                                	echo "<td><img src='kiosk/images/medals/holder.png' height='60' class='holder' /><br />";
                                	echo  $sticker[$k] . "/" . $req . " to " . $medals[$k] . "</td>";
								} else {
                                	echo "<td style='vertical-align: middle'>Not Enrolled</td>";  
								}                          
                            } else {
                                $missionsDone = $mission[$missions[$m]];
                                $sticker = $s->calculateSticker( $m, $missionsDone );
                                $k = key( $sticker );
								$req = (int)getMedalsRequired($m, $k);
								$medal = getMedal($m, $user_id);
								if ($medal['image'] && !empty($medal['image']))
									echo "<td><img src='file_view.php?id=" . $medal['image'] . "' height='60' /><br />";
								else 
									echo "<td><img src='kiosk/images/medals/holder.png' height='60' class='holder' /><br />";
                                echo  $sticker[$k] . "/" . $req . " to " . $medals[$k] . "</td>"; 
                            }
                        }
                        echo "</tr>";
						if (++$i == ($numChildren+1)) {
							echo "</table><div class='page-break'></div>";
							echo "<h2>" . $schools[$school] . " Platoon " . $class . "</h2>";
			                echo "<table>";
			                echo "<tr><th>Rank</th><th>Soldier</th>";
			                foreach ( $missionsPosted as $mp ) {
			                    echo "<th align='center'><img class='sticker' src='images/stickers/Sticker-" . $stickers[$mp][$missions[$mp]];
								if ($mp == 100) echo ".jpg";
								else echo ".gif";
								echo "'><br />". $missions[$mp] . "</th>";
			                }
			                echo "</tr>";
							$i = 1;
						}
                    }
                    echo "</table><br />";
                	echo "<div class='page-break'></div>";
                }             
            }
			echo "</div>";
        } else {          
            ?>
            <form action="status_report.php" method="post">
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
                <? } else { ?>
                	<br />
                	<fieldset>
                		<legend>Select School(s)</legend>
                		<? 
                		foreach ($schools as $id => $name) {
                			echo "<input type='checkbox' name='schools[]' value='$id' /> $name<br />";
						} 
						?>
                	</fieldset>
                <? } ?>
                
                <br />
            	<fieldset>
            		<legend>Children per page</legend>
            		Please choose how many children you would like to show per page: 
            		<select name="numChildren">
            			<?
            			for ($i=10; $i < 26; $i++) {
            				if ($i == 15) {
            					echo "<option value=$i selected>$i</option>"; 
            				} else {
								echo "<option value=$i>$i</option>";
							}
						}
						?>
            		</select>
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