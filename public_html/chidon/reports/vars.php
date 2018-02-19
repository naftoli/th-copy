<?php
require '../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if (isset($_GET['boys'])) {
	$gender = 'M';
} else if (isset($_GET['girls'])) {
	$gender = 'F';
}
?>