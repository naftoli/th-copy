<?php
DEFINE("BASE", "/home/mashpia/public_html/kiosk/campaigns/tanya");
DEFINE("BASE_URI", "");

$VERBOSE = 0;
require_once("/home/mashpia/public_html/kiosk/campaigns/tanya/config.php");
require_once("/home/mashpia/public_html/kiosk/campaigns/classes/class.DBI.php");
$objDBIHandle = new DBI($VERBOSE);
$objDBIHandle->open();
mysql_select_db("mashpia", $objDBIHandle->objHandle);
if (
	isset($_GET["mission_id"])
	&& is_numeric($_GET["mission_id"])
	&& isset($_GET["user_id"])
	&& is_numeric($_GET["user_id"])
) {
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
			 LEFT JOIN (SELECT user_id, MAX(rank_ord) rank_ord FROM rank_marks WHERE user_id = {$_GET["user_id"]} GROUP BY user_id) rank USING (user_id)
			 LEFT JOIN ranks USING (rank_ord)
		WHERE
			user_id = {$_GET["user_id"]}
		ORDER BY
			class_grade, class_sub, last, first
	";
	$objResult = $objDBIHandle->query($strSql);
	$user_row = mysql_fetch_assoc($objResult);
	require_once(BASE . "/source/class.Tanya.php");
	$objTanya = new Tanya($VERBOSE);
	$objTanya->loadUser();
	$objMissions = new TanyaMissions($objTanya->objUserHandle, $VERBOSE);
	$arrMissionRanges = $objMissions->missionEntryRange(1, 0);
	$objDBIHandle->open();
	list($intKey) = array_keys($arrMissionRanges["design"]);
	//var_dump($arrMissionRanges["design"][$intKey]);
	$objMissions->loadOldestPendingMission();
	$objDBIHandle->open();
	if ($arrMissionRanges["design"][$intKey]["mission_start"] < $arrMissionRanges["design"][$intKey]["mission_end"]) {
		// Insert missing missions
		$strSql = "
			INSERT
				INTO " . tanya_missions_table . "
				(user_id, mission_number, tested, tested_date, ladder, `real`, sum, virtual_sum, date_created)
			VALUES
		";
		$arrSql = array();
		for (
			$intItr = $arrMissionRanges["design"][$intKey]["mission_start"];
			$intItr != $arrMissionRanges["design"][$intKey]["mission_end"]+1;
			$intItr++
		) {
			$arrSql[] = "({$_GET["user_id"]}, $intItr, 1, UNIX_TIMESTAMP(), "
				. $objTanya->objUserHandle->intLadder
				. ", " . $arrMissionRanges["design"][$intKey]["real"]
				. ", " . $arrMissionRanges["design"][$intKey]["sum"]
				. ", " . $arrMissionRanges["design"][$intKey]["virtual_sum"]
				. ", " . $objMissions->intDateCreated
				. ")";
		}
		$strSql = $strSql . join(",", $arrSql) . "
			ON DUPLICATE KEY UPDATE
				tested=1,
				tested_date=VALUES(tested_date),
				ladder=VALUES(ladder),
				`real`=VALUES(`real`),
				sum=VALUES(sum),
				virtual_sum=VALUES(virtual_sum);
		";
		//print $strSql;
		$objDBIHandle->query($strSql);
	}
	$strSql = "
		UPDATE
			" . tanya_missions_table . "
		SET
			tested = 1,
			tested_date = UNIX_TIMESTAMP()
		WHERE
			mission_id=" . $_GET["mission_id"];
	$objDBIHandle->query($strSql);
	$strSql = "
		UPDATE
			" . tanya_user_table . " users_TO
		SET
			users_TO.lines_after_enrollment = (
				SELECT
					missions_FROM.virtual_sum
				FROM
					" . tanya_missions_table . " missions_FROM
				WHERE
					missions_FROM.mission_id=" . $_GET["mission_id"] . "
			)
		WHERE
			users_TO.id=" .  $_GET["user_id"];
	//print $strSql . "<br>\n";
	$objDBIHandle->query($strSql);
	$intMissions = $arrMissionRanges["design"][$intKey]["mission_end"]-$arrMissionRanges["design"][$intKey]["mission_start"];
	$strSql = "
		INSERT INTO
			mashpia.member_points
			(user_id, points, points_date)
		VALUES
			";
	$arrSql = array();
	for ($intItr=0; $intItr!=$intMissions+1; $intItr++) {
		$arrSql[] = "({$_GET["user_id"]},5,CURDATE())";
	}
	$strSql .= join(",", $arrSql);
	//print $strSql;
	//$objDBIHandle->query($strSql);
	$objDBIHandle->close();
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
				var url = "content.php?output=approvals_pending_missions&mission_id=" + info[1] + "&user_id=" + info[2];

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
					<span>Pending Missions</span>
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
		" . tanya_missions_table . ",
		" . tanya_user_table . ",
		mashpia.users
	WHERE
		" . tanya_missions_table . ".tested = 0
		AND " . tanya_missions_table . ".user_id = mashpia.users.user_id
		AND " . tanya_missions_table . ".user_id = " . tanya_user_table . ".id
	GROUP BY
		" . tanya_missions_table . ".user_id
	ORDER BY
		mission_number ASC
";
$objDBIHandle->query("SET NAMES 'utf8'");
$objResult = $objDBIHandle->query($strSql);
while ($objRow = mysql_fetch_assoc($objResult)) {
?>
								<li id="u_<?=$objRow["mission_id"]?>_<?=$objRow["user_id"]?>">

									<a class="link" href="#">
										<div class="image">
																					<img src="images/generic_user_small.png" height="32" />
																				</div>

										<div class="name"><?=$objRow["first"]?>	<?=$objRow["last"]?> - Mission: <?=$objRow["mission_number"]?>
										</div>
										<div class="dropdowns"></div>
									</a>

									<span class="action">

                                    </span>

									<span class="action">
										<span class="register">
											<a href="#" title="<?=$objRow["mission_id"]?>_<?=$objRow["user_id"]?>" class="button">
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