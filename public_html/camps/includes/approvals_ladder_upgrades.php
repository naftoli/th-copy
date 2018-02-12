<?php
$VERBOSE = 0;
require_once("/home/mashpia/public_html/kiosk/campaigns/tanya/config.php");
require_once("/home/mashpia/public_html/kiosk/campaigns/classes/class.DBI.php");
require_once("/home/mashpia/public_html/kiosk/campaigns/tanya/source/class.Tanya.php");
$objDBIHandle = new DBI($VERBOSE);
if (
	isset($_GET["request_id"])
	&& is_numeric($_GET["request_id"])
) {
	$objDBIHandle->open();
	// Select the current ladder requests
	$strSql = "
		SELECT
			*
		FROM
			" . tanya_requests_table . "
		WHERE
			request_id=" . $_GET["request_id"];
	$objResult = $objDBIHandle->query($strSql);
	if (
		$objResult
		&& mysql_num_rows($objResult)
	) {
		// Set the ladder on the tanya_user_table to the ladder on the tanya_requests_table
		$objRequestRow = mysql_fetch_assoc($objResult);

		$strSql = "
			UPDATE
				" . tanya_user_table . " users_TO,
				" . tanya_requests_table . " requests_FROM
			SET
				users_TO.desired_chapter_goal = requests_FROM.line_goal,
				users_TO.ladder = requests_FROM.to_ladder
			WHERE
				requests_FROM.request_id = " . $_GET["request_id"] . "
				AND users_TO.id = requests_FROM.user_id";
		$objDBIHandle->query($strSql);

		// Do alot of crap for a small job to save time is the way to run mergeMissions //
		$strSql = "
			SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2, dob,
				   user_city, user_state, user_postal, user_country, user_phone,
				   user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id,
				   team_name, school_name, school_number, school_city, school_state, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, rank_name, rank_image_id, rank_color
			FROM users
				 LEFT JOIN schools USING (school_id)
				 LEFT JOIN institutions USING (inst_id)
				 LEFT JOIN classes USING (school_id, class_id)
				 LEFT JOIN teams USING (school_id, team_id)
				 LEFT JOIN (SELECT user_id, MAX(rank_ord) rank_ord FROM rank_marks WHERE user_id = {$objRequestRow["user_id"]} GROUP BY user_id) rank USING (user_id)
				 LEFT JOIN ranks USING (rank_ord)
			WHERE
				user_id = {$objRequestRow["user_id"]}
			ORDER BY
				class_grade, class_sub, last, first
		";
		mysql_select_db("mashpia", $objDBIHandle->objHandle);
		$objResult = $objDBIHandle->query($strSql);
		$user_row = mysql_fetch_assoc($objResult);
		$objTanya = new Tanya($VERBOSE);
		$objTanya->loadUser();
		$objMissions = new TanyaMissions($objTanya->objUserHandle, 0);
		$arrMissionRanges = $objMissions->missionEntryRange(
			6, // Number of pages to scale
			0, // Include lines
			0  // Verbose
		);
		$objDBIHandle->open();

		// Gather the pending lines from the pending mission
		$strSql = "
			SELECT
				DISTINCT task_TO.line_number
			FROM
				" . tanya_tasks_table . " task_TO
			WHERE
				task_TO.foreign_mission_id IN (
					SELECT
						missions_FROM.mission_id
					FROM
						" . tanya_missions_table . " missions_FROM
					WHERE
						missions_FROM.tested=0
						AND missions_FROM.user_id=" . $objRequestRow["user_id"] . "
				)
		";
		$objResult = $objDBIHandle->query($strSql);
		$arrPendingLines = array();
		while ($objRow = mysql_fetch_assoc($objResult)) {
			$arrPendingLines[$objRow["line_number"]] = 1;
		}
		// Delete any pending missions and tasks due to their previous ladder configuration
		$strSql = "
			DELETE FROM
				" . tanya_tasks_table . "
			WHERE
				foreign_mission_id IN (
					SELECT
						mission_id
					FROM
						" . tanya_missions_table . " missions_FROM
					WHERE
						missions_FROM.tested=0
						AND missions_FROM.user_id=" . $objRequestRow["user_id"] . "
				)
		";
		$objDBIHandle->query($strSql);
		$strSql = "
			DELETE FROM
				" . tanya_missions_table . "
			WHERE
				tested=0
				AND user_id=" . $objRequestRow["user_id"];

		$objDBIHandle->query($strSql);
		$arrChecked = array();
		foreach ($arrMissionRanges["design"] as $arrPage) {
			for ($intMission=$arrPage["mission_start"]; $intMission!=$arrPage["mission_start"]+$arrPage["mission_count"]; $intMission++) {
				for ($intItr=$arrPage["line_start"]; $intItr!=$arrPage["line_start"]+$arrPage["line_count"]; $intItr++) {
					if ($arrPendingLines[$intItr+1])
						$arrChecked[1][$intMission][] = $intItr+1;
				}
			}
		}
		$objMissions->mergeTasks($arrChecked);
		$objMissions->mergeMissions($arrMissionRanges["design"]);
		$objDBIHandle->open();
		$strSql = "
			DELETE
				FROM " . tanya_requests_table . "
			WHERE
				request_id=" . $_GET["request_id"];
		$objDBIHandle->query($strSql);

		////////////////////////////////////////////////////////////////////////////////
	} else {
		print "Invalid request id";
	}
	exit;
}
?>
 <script>
	 $(document).ready(function() {
		$(".register .button").click(
			function(event) {
				var list_item = $(this).parents('li');
				var info = $(list_item).attr("id").split("_");
				var user_id = info[1];
				var function_name = "register_camper";
				var parameters = [user_id];
				var url = "content.php?output=approvals_ladder_upgrades&request_id=" + info[1];

				$.getJSON(url, function(success) {
					if (success == false)  {
						alert("Could not register camper. Please try again");
					}
					else {
						$(list_item).addClass("selected");
						$(list_item).find('.action').append('<span class="progress">Progress</span>').find('.progress').show().delay(500).fadeOut(500,function(){
							$(list_item).slideUp('fast',function(){
								$(list_item).remove();
							});
						});
					}
				});
			}
		);
	});
</script>
			<div class="slider">

				<div class="col_title">
					<span>Pending Ladders</span>
				</div>

				<div class="col_content">

					<div class="module lists" id="lists-grouptypes">

						<div class="module_content">


							<ul>




<?
$strSql = "
	SELECT
		*
	FROM
		" . tanya_requests_table . ",
		" . tanya_user_table . ",
		mashpia.users
	WHERE
		" . tanya_requests_table . ".user_id = mashpia.users.user_id
		AND " . tanya_requests_table . ".user_id = " . tanya_user_table . ".id
";
$objDBIHandle->open();
$objDBIHandle->query("SET NAMES 'utf8'");
$objResult = $objDBIHandle->query($strSql);
while ($objRow = mysql_fetch_assoc($objResult)) {
?>
								<li id="u_<?=$objRow["request_id"]?>">

									<a class="link" href="#">
										<div class="image">
																					<img src="images/generic_user_small.png" height="32" />
																				</div>

										<div class="name"><?=$objRow["first"]?>	<?=$objRow["last"]?> - From: <?=$objRow["ladder"]?> To: <?=$objRow["to_ladder"]?>
										</div>
										<div class="dropdowns"></div>
									</a>

									<span class="action">

                                    </span>

									<span class="action">
										<span class="register">
											<a href="#" class="button">
												<span class="icon"></span>Confirm
											</a>
										</span>
                                    </span>
								</li>
<?
}
$objDBIHandle->close();
?>

							</ul>


						</div> <!-- <div class="module_content"> -->

					</div> <!-- <div class="module lists" id="lists-grouptypes"> -->

				</div> <!-- <div class="col_content"> -->

			</div> <!-- <div class="slider"> -->