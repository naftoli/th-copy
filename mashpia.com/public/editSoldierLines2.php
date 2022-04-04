<?php
// ini_set('display_errors',1);
$admin_auth = array('school');
require_once 'header.php';

require 'class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

/****************** CAMPAIGNS ******************/
$campaigns_query = mysql_query( 
	"SELECT * FROM line_campaigns WHERE year = " . $year
);
// for each line campaign
while ($row = mysql_fetch_assoc( $campaigns_query )) {
	if (strtolower($row['type']) == 'tanya') 
		$tanyaCampaign = $row['id'];
	else if (strtolower($row['type']) == 'mishna') 
		$mishnaCampaign = $row['id'];
}

/****************** LOAD SCHOOLS ******************/
require_once( $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php' );
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
$schoolIDs = array();
foreach ( $schools as $id => $school ) {
	$schoolIDs[] = $id;
}

if ( isset( $_POST['school'] ) && isset( $_POST['grade'] ) ) {
	$school = $_POST['school'];
	$grade = $_POST['grade'];

	/****************** CLASSES ******************/
	$classes = [];	$classNames = [];
	if ( $school ) {
		if ( $grade ) {
			$class_query = mysql_query(
				"SELECT * FROM classes WHERE school_id = " . $school . " AND class_id = " . $grade . " ORDER BY class_grade, class_sub"
			);
		} else {
			$class_query = mysql_query(
				"SELECT * FROM classes WHERE school_id = " . $school . " AND class_era = 0 ORDER BY class_grade, class_sub"
			);
		}
		while ( $row = mysql_fetch_assoc( $class_query ) ) {
			$classes[$school][] = $row['class_id'];
			$classNames[$row['class_id']] = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
		}
	} else {
		foreach ( $schoolIDs as $id ) {
			$class_query = mysql_query(
				"SELECT * FROM classes WHERE school_id = " . $id . " AND class_era = 0 ORDER BY class_grade, class_sub"
			);
			while ( $row = mysql_fetch_assoc( $class_query ) ) {
				$classes[$id][] = $row['class_id'];
				$classNames[$row['class_id']] = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
			}
		}
	}

	/****************** USERS ******************/
	$users = [];	$userNames = [];
	foreach ( $classes as $school => $grades ) {
		foreach ( $grades as $grade ) {
			$users_query = mysql_query(
				"SELECT * FROM users WHERE school_id = " . $school . " and class_id = " . $grade . " AND (user_registered > 0 OR yan = 1) ORDER BY last, first"
			);
			if (mysql_num_rows($users_query) > 0) {
				while ($row = mysql_fetch_assoc($users_query))	{
					$users[$school][$grade][] = $row['user_id'];
					$userNames[$row['user_id']] = $row['first'] . ' ' . $row['last'];
				}
			}
		}
	}
}

/****************** BAL PEH INFORMATION ******************/
require_once( $_SERVER['DOCUMENT_ROOT'].'/class.bpSummary.php' );
$bpsTanya 	= new BpSummary( $tanyaCampaign, 	'school' );
$bpsMishna 	= new BpSummary( $mishnaCampaign, 	'school' );

require_once( $_SERVER['DOCUMENT_ROOT'].'/class.balPehCampaign.php' );
$tanya 	= BalPehCampaign::getInstance(	$tanyaCampaign	);
$mishna = BalPehCampaign::getInstance(	$mishnaCampaign	);
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Edit Tanya / Mishna Lines</title>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<link href="/styles/admin/modal.css"  rel="stylesheet" type="text/css"/>
		<link href="/styles/admin/loader.css"  rel="stylesheet" type="text/css"/>
		<style>
			tr, th, td {
				padding: 2px;	font-size: 11px;
			}
			th, .middle {
				text-align: center;
			}
			.runningTotals {
				line-height: 1.2;	font-size: 14px;
			}
			button:hover{
				cursor: pointer;
			}
			.modal-content {
				margin: 5% auto;
			}
			.mishno span {
				display: inline-block;
				width: 40%;
			}
			.perek {
				width: 45%;
				display: inline-block;
				vertical-align: top;
				padding: 2%;
			}
			.sader > span.title {
				font-size: 1.2em;
				border-bottom: 1px solid #888;
			}
			span.title {
				width: 100%;
				display: inline-block;
				text-align: center;
				font-size: 1em;
				font-weight: bold;
				margin-bottom: 4px;
				border-bottom: 1px solid #aaa;
			}
			span.title:hover {
				cursor: pointer;
			}
			.mesechto, .sader {
				height: 25px;
				overflow: hidden;
				margin: 15px 0px;
			}
			input#mishna_total {
				border: none;
				background: none;
				border-bottom: 1px solid;
				font-size: 1em;
			}
		</style>
	</head>
	
	<body>
		<?php include('admin_header.php'); ?>
		<h1>Edit Tanya / Mishna Lines</h1>

		<?php if ( isset( $_POST['school'] ) && isset( $_POST['grade'] ) ) : ?>
		
		<div class="infobox" style="line-height: 1.2">
			PLEASE NOTE: The amounts which are shown under the Tanya Learned and Mishna Learned columns, will only sync onto the ourbirthdaygift.com website
			if you click "Confirm & Sync" next to each child. The column titled "Parent Entered" is there for your convenience. They do not "sync" anywhere.
			<br /><br />
			If ALL amounts are correct, just click "Confirm All" on the top near the totals.
		</div>
		<br />
		Change Grade to: 
		<select name="grade" id="grade">
				<option value='0'>All Grades</option>
				<?php
				require_once 'class.schoolClasses.php';
				$sc = new SchoolClasses($school);
				$classes2 = $sc->getClasses();
				foreach ($classes2 as $row) {
						$class2 = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
						echo "<option value='" . $row['class_id'] . "'";
						if ($row['class_id'] == $grade) echo " selected";
						echo ">" . $class2 . "</option>";
				}
				?>
		</select>
		<input type="submit" name="submit" value="change" id="changeGrade" />
		<br /><br />						
	<?php
	foreach ($schools as $id => $name) {
		$tanyaSummary = $bpsTanya->getSummary($id) ? $bpsTanya->getSummary($id) : 0;
		$mishnaSummary = $bpsMishna->getSummary($id) ? $bpsMishna->getSummary($id) : 0;
		?>
		<br />
		<div id="school_<?=$id?>">
			<div style="float: right">
				<button class="copyTanya">Copy Parent Entered to Tanya Learned</button><br /><br />
				<button class="copyMishna">Copy Parent Entered to Mishna Learned</button><br />
			</div>
			<div class="runningTotals">
				Total Tanya Learned: <span class="tlearned"></span><br />
				Total Tanya Synced to Ourbirthdaygift.com: <?=$tanyaSummary?><br />
				Total Mishna Learned: <span class="mlearned"></span><br />
				Total Mishna Synced to Ourbirthdaygift.com: <?=$mishnaSummary?><br />
				<input type="hidden" class="schoolID" value="<?=$id?>" />
				<button class="confirmAll">Confirm ALL</button>
			</div>
			<h2><?= $name ?></h2>
			<?php
				$grandTotals['tanya']['pledged'] = 0;
				$grandTotals['tanya']['learned'] = 0;
				$grandTotals['mishna']['pledged'] = 0;
				$grandTotals['mishna']['learned'] = 0;
			?>
			<table id="<?=$id?>">
				<tr>
					<th>Grade</th>
					<th>Student</th>
					<th>Tanya Learned</th>
					<th>Parent Entered</th>
					<th>Mishna Learned</th>
					<th>Parent Entered</th>
					<th colspan="2">Actions</th>
				</tr>
				<?php
                if (isset($classes[$id])) {
                    // for each class...
                    foreach ($classes[$id] as $class) {
                        // for each student...
                        foreach ($users[$id][$class] as $user) {
                            // get the totals of amount learned
                            $totalTanya['learned'] = $tanya->getTotalLearned( 'user', $user );
                            $totalMishna['learned'] = $mishna->getTotalLearned( 'user', $user );

                            // get parent entered
                            $totalTanya['parentEntered'] = $tanya->getParentEntered( $user );
                            $totalMishna['parentEntered'] = $mishna->getParentEntered( $user );

                            // sum them up in the grand totals
                            $grandTotals['tanya']['learned'] += $totalTanya['learned'];
                            $grandTotals['mishna']['learned'] += $totalMishna['learned'];
                            ?>
                            <tr>
                                <td><?=$classNames[$class]?></td>
                                <td>
                                    <?=$userNames[$user]?>
                                    <input type='hidden' class='userID' value='<?=$user?>' />
                                    <input type='hidden' class='classID' value='<?=$class?>' />
                                    <input type='hidden' class='schoolID' value='<?=$id?>' />
                                </td>
                                <td class='middle'>
                                    <input type='text' size='5' class='tanya learn tlearn' value='<?=$totalTanya['learned']?>' />
                                </td>
                                <td class='parentTanyaEntered'>
                                    <?=($totalTanya['parentEntered'] > 0 ? $totalTanya['parentEntered'] : '')?>
                                </td>
                                <td class='middle'>
                                    <input type='text' size='5' class='mishna learn mlearn' value='<?= $totalMishna['learned'] ?>' />
                                </td>
                                <td class='parentMishnaEntered'>
                                    <?=($totalMishna['parentEntered'] > 0 ? $totalMishna['parentEntered'] : '')?>
                                </td>
                                <td>
                                    <button class='by_mishna'>Calcuate Mishna</button>
                                </td>
                                <td>
                                    <button class='sync'>Confirm & Sync</button>
                                </td>
                            </tr>
                        <?php
                        } // end foreach student
				    } // end foreach class
                }
				?>
				<script>
					var tlearned = <?=$grandTotals['tanya']['learned']?>;
					var mlearned = <?=$grandTotals['mishna']['learned']?>;
					
					var id = <?=$id?>;
					var school_id = "#school_" + id;
					$(school_id + " .tlearned").text(tlearned);
					$(school_id + " .mlearned").text(mlearned);
				</script>
			</table>
		</div>
	<? } // end foreach school ?>
	<div class="modal" id="mishna-selector">
		<div class="modal-content">
			<h1>
				Mishna Lines Calculator
				<span class="close">X</span>
			</h1>
			<p>
				Use this calculator to deterimne the number of lines based on the amount of perokim that the child compleated.
			</p>

			<p>
				Current Line Total: <input type="number" id="mishna_total" value="0"/>
				<a class="button" id="set_mishna_total">Set Lines</a>
			</p>

			<div id="mishna_selector">
				<div class="loader"></div>
			</div>
		</div>
	</div>

	<script>
		var campaigns = {
			tanyaCampaign:  <?=$tanyaCampaign?>,
    	    mishnaCampaign: <?=$mishnaCampaign?>
		}
		// after yud alef nissan disable all entries
		// $("button").attr('disabled', true);
		// $("input").attr('disabled', true);
	</script>
	<script src="/js/admin/components/modal.js"></script>
	<script src="/js/admin/editSoldierLines2.js"></script>
	<script>
		$("#changeGrade").click( function() {
			var school = <?= $_POST['school'] ?>;
			var grade = $("#grade").val();
			var form = "<form action='editSoldierLines2.php' method='post'><input type='hidden' name='school' value='" + school + "' /><input type='hidden' name='grade' value='" + grade + "' /></form>";
			$(form).appendTo('body').submit();
		});
	</script>

	<? else : ?>
		<form action='editSoldierLines2.php<?=isset( $debug ) ? "?debug=true" : ""?>' method="post">				
				<select name="school" id="school">
						<?php
						if (count($schools) > 1) {
								echo "<option value='0'>Choose School</option>";
						}
						foreach ($schools as $id => $name) {
								echo "<option value='" . $id . "'>" . $name . "</option>";
						}
						?>
				</select><br />
				<br />				
												
				<select name="grade" id="grade">
						<option value='0'>All Grades</option>
						<?php
						if (count($schools) == 1) {
                            $id = key($schools);
                            //echo $id;
                            require_once 'class.schoolClasses.php';
                            $sc = new SchoolClasses($id);
                            $classes = $sc->getClasses();
                            foreach ($classes as $row) {
                                    $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
                                    echo "<option value='" . $row['class_id'] . "'>" . $grade . "</option>";
                            }
						}
						?>
				</select>
				<br /><br />
				<input type="submit" name="submit" value="Submit" />
		</form>

		<script>        
        $("#school").change( function() {
            var school = $(this).val();
            $.get('ajax/getClasses.php?flat=true', { id : school }, function( info ) {
                var grades = JSON.parse( info );
                var html = "<option value='0'>Choose Grade</option>";
                for (var g in grades) {
                    html += "<option value='" + grades[g][0] + "'>" + grades[g][1] + "</option>";
                }
                $("#grade").empty();
                $("#grade").append( html );
            });
        });
    </script>
	<? endif; ?>

	</body>
</html>