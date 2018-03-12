<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!$_COOKIE['admin_id']) {
	$page = '/home.php';
	header('Location: http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . $page);
}
else {
	$admin_id = $_COOKIE['admin_id'];
}

if(isset($_POST["prize_name"])) {
	require $_SERVER['DOCUMENT_ROOT'] . '/../includes/globals.php'; // import the global files...
	$link = mysql_connect($global_db_host.":3306", $global_db_user, $global_db_pass) or trigger_error_server('Failed to connect to mysql', E_USER_ERROR);
	mysql_query('SET NAMES utf8');
	mysql_query('SET CHARACTER_SET utf8');
	mysql_select_db('mashpia') || trigger_error_server('Failed to select db.', E_USER_ERROR);

	// upload image
	if (!empty($_FILES['image']['name']) && $_FILES['image']['size'] > 0) {
		do {
			if($count++ > 100000)
				trigger_error('could not get ID', E_USER_ERROR);

			$id = mysql_result(mq('SELECT FLOOR(RAND() * 4294967295)'),0);
		} while(mysql_result(mq("SELECT COUNT(*) FROM files WHERE file_id = $id"),0) != 0);

		if (!mq("INSERT INTO files (file_id, file_name, file_content_type, file_data) VALUES ($id, " . ms($_FILES['image']['name']) . ', ' . ms(mime_content_type($_FILES['image']['tmp_name'])) . ', ' . ms(file_get_contents($_FILES['image']['tmp_name'])) . ')'))
			$er_message = "Error uploading image";
	}

	if ($_POST['prize_id']) {
		$sql = "UPDATE prizes_camp SET prize_name = '" . $_POST['prize_name'] . "', ";
		$sql .= " prize_description = '" . $_POST['prize_description'] . "', ";
		$sql .= " prize_points = '" . $_POST['prize_points'] . "', ";

		if ($id)
			$sql .= " prize_image_id = '" . $id . "', ";

		$sql .= " prize_available = '" . $_POST['prize_available'] . "' where prize_id = '" . $_POST['prize_id'] . "'";
	}
	else {
		$sql = "INSERT INTO prizes_camp SET ";
		$sql .= "camp_id='" . $camp_id . "', ";
		$sql .= "prize_name='" . $_POST["prize_name"] . "', ";
		$sql .= "prize_description='" . $_POST["prize_description"] . "', ";
		$sql .= "prize_points='" . $_POST["prize_points"] . "', ";
		$sql .= "prize_available='" . $_POST["prize_available"] . "', ";
		$sql .= "prize_image_id='" . $id . "' ";
	}

	$query = mysql_query($sql);
	if(!$query)
		$er_message = mysql_error();
	else
		$er_message = "success";

}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title>Hachayal Kiosk - Admin</title>
		<link rel="alternate" media="print" href="index.php">
		<link href="styles/reset.css" rel="stylesheet" type="text/css" />
		<link href="styles/styles.css" rel="stylesheet" type="text/css" />
		<link href="styles/print.css" rel="stylesheet" type="text/css" media="print" />
		<script src="scripts/jquery.tools.min.js"></script>
		<script src="scripts/jquery.color.min.js"></script>
		<script src="scripts/jquery.styleselect.js"></script>
		<script src="scripts/jquery.form.js"></script>
		<script src="scripts/jquery.jeditable.min.js"></script>
		<script src="scripts/jquery.editableText.js"></script>
		<script src="scripts/scripts.js"></script>
	</head>

	<body>

		<div id="wrapper">

			<div id="nav">

				<div class="col_title_bg">
				</div>

				<div class="col_title">
					Menu
				</div>

				<ul class="list_first">

					<li class="list_parent">
						<a href="content.php?output=home"><span class="icon"><img src="images/icon_dashboard.png" width="28" height="28" alt="Dashboard" /></span>Dashboard</a>
					</li>

					<ul class="list_second">

						<li>
							<a href="content.php?output=staff_profile&admin_id=<?=$admin_id;?>">
								<span class="icon">
									<img src="images/icon_settings.png" width="22" height="22" alt="Settings" />
								</span>
								My Profile
							</a>
						</li>
					</ul>

					<!-- START -->
					<li class="list_parent">
						<a href="content.php?output=points"><span class="icon"><img src="images/icon_points.png" width="28" height="28" alt="Points" /></span>Points</a>
					</li>

					<ul class="list_second">
					</ul>

					<li class="list_parent"><a href="#print"><span class="icon"><img src="images/icon_print.png" width="28" height="28" alt="Print Center" /></span>Print Center</a></li>
						<ul class="list_second">
							<li><a href="content.php?output=rankcards"><span class="icon"><img src="images/icon_rank_card.png" width="28" height="28" alt="Settings" /></span>Rank Cards</a></li>
							<li><a href="content.php?output=mission_sheets"><span class="icon"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /></span>Mission Sheets</a></li>
							<li><a href="#"><span class="icon"><img src="images/icon_add.png" width="22" height="22" alt="Add" /></span>Print Cards</a></li>
						</ul>

					<li class="list_parent"><a href="#control"><span class="icon"><img src="images/icon_control.png" width="28" height="28" alt="Settings" /></span>Control Panel</a></li>
						<ul class="list_second">
							<li><a href="content.php?output=campprofile"><span class="icon"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /></span>Camp Profile</a></li>
							<li><a href="content.php?output=grouptypes"><span class="icon"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /></span>Groups</a></li>
							<li><a href="content.php?output=missions_dash"><span class="icon"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /></span>Missions</a></li>
							<li><a href="content.php?output=campers"><span class="icon"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /></span>Campers</a></li>
							<li><a href="content.php?output=staff"><span class="icon"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /></span>Staff</a></li>
							<li><a href="content.php?output=store"><span class="icon"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /></span>Store</a></li>
							<li><a href="content.php?output=approvals"><span class="icon"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /></span>Approvals</a></li>
							<li><a href="content.php?output=gettingstarted"><span class="icon"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /></span>Getting Started</a></li>
						</ul>

					<li class="list_parent">
						<a href="#" onclick="window.location = 'logout.php';"><span class="icon"><img src="images/icon_door.png" width="28" height="28" alt="Logout" />
								</span>
								Logout
							</a>
					</li>

				</ul>

			</div>

			<div id="content">
				<div class="col_title_bg"></div>
				<div class="slider_container">
					<? include("includes/home.php"); ?>
				</div>
			</div>

		</div>

		<div id="overlay">
			<div class="content"></div>
		</div>

		<? if ($er_message) : ?>
			<script type="text/javascript">
				var url = 'content.php?output=store';
				$.get(url,'',function(data) {
					$('.slider_container').append(data);
					$('.slider_container .slider:last .col_title').append('<a class="slider_back"></a>');
					$('.slider_container .slider:last .col_title a').html($('.slider:last .col_title span').html());
					$('.slider_container').data('url',url);
					hideLoader();
					initialize();
					slide_width = 773;
					$('.slider_container').animate({'margin-left':parseInt($('.slider_container').css('margin-left')) - slide_width + 'px'}, 500, hideLoader());
				});
			</script>
		<? endif; ?>

	</body>

</html>
