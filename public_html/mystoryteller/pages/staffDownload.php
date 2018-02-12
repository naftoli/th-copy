<?
$cds = array();
require 'db.php';
$sql = "select id, title, download_link from cds";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$cds[$row['id']] = $row;
}

if (isset($_GET['cd'])) {
	$id = $_GET['cd'];
	$file = "../files/" . $cds[$id]['download_link'];
	
	if (file_exists($file)) {
	    header('Content-Description: File Transfer');
	    header('Content-Type: application/force-download');
	    header('Content-Disposition: attachment; filename="../files/' . basename($file) . '"');
	    header('Expires: 0');
	    header('Cache-Control: must-revalidate');
	    header('Pragma: public');
	    header('Content-Length: ' . filesize($file));
	    readfile($file);
		exit;
	}
}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Staff Download</title>
	</head>
	
	<body>
		<?
		if (isset($_POST['submit'])) {
			$code = trim($_POST['code']);
			if (is_numeric($code)) {
				if ($code == '594837') {
					echo "<ul>";
					foreach ($cds as $cd) {
						echo "<li>" . $cd['title'] . " - <a href='staffDownload.php?cd=" . $cd['id'] . "'>" . $cd['download_link'] . "</a></li>";
					}
					echo "</ul>";
				}
			}
		} else {
		?>
		<form action="staffDownload.php" method="post">
			Enter Code: <input type="text" name="code" /><br />
			<input type="submit" name="submit" value="submit" />
		</form>
		<? } ?>
	</body>
</html>