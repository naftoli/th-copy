<?
$admin_auth = array('user'); 
require('header.php');

include("camps/includes/classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
$admin->get_markable_children();
$children = array();
foreach ( $admin->children as $child ) {
	$children[$child->user_id] = $child->first . " " . $child->last;
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
                
                $(".mission").attr("checked", true);
                $(".children").attr("checked", true);               
            });
        </script> 
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1 class="no-print">Sticker Report</h1>
        <?         
        require_once 'class.missionsDone.php';
        $missions = MissionsDone::getAllMissions();
        $medals = MissionsDone::getAllMedals();
        
        require_once 'class.stickers.php';
        $s = new Stickers;
        
        if ( isset( $_POST['submit'] ) ) {
            if ( !isset( $_POST['missions'] ) || !isset( $_POST['children'] ) ) {
                echo "Please go back and choose at least one mission and one child.";
                exit;
            }
            
            //print_r( $_POST );            
            $missionsPosted = $_POST['missions'];
            $children = $_POST['children'];			
			$m = new MissionsDone();
			$m->setMissionsDone( $missionsPosted, $children );
			$userMissions = $m->getMissionsDone();
            
            echo "<div align='center' class='no-print'>";
            echo "<input type='button' value='Print' onclick='window.print()' />";
            echo "</div>";
			echo "<br />";
                        
            echo "<table>";
            echo "<tr><th>&nbsp;</th>";
            foreach ( $missionsPosted as $mp ) {
                echo "<th>" . $missions[$mp] . "</th>";
            }
            echo "</tr>";
			foreach ( $userMissions as $user => $mission ) {
				echo "<tr><td>" . $user . "</td>";
                foreach ( $missionsPosted as $m ) {
                    if ( !isset( $mission[$missions[$m]] ) ) {
                        echo "<td>&nbsp;</td>";                            
                    } else {
                        $missionsDone = $mission[$missions[$m]];
                        $sticker = $s->calculateSticker( $m, $missionsDone );
                        $k = key( $sticker );
                        echo  "<td>" . $sticker[$k] . " of " . $medals[$k] . "</td>"; 
                    }
                }
				echo "</tr>";
			}
            echo "</table><br />";
            echo "<div class='page-break'></div>";
        } else {          
            ?>
            <form action="children_stickers.php" method="post">
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
       }
    ?>
    </body>
</html>