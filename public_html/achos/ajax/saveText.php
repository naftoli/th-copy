<?
$text = $_POST['text'];
$file = fopen('../home_text.txt', 'w');
$success = false;
if ($file) {
	if (fwrite($file, $text) !== false) {
		$success = true;
	}
	fclose($file);
}

if ($success) {
	echo 'saved.';
} else {
	echo 'error saving.';
}
?>