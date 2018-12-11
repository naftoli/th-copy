<?
$file = fopen('home_text.txt', 'r');
if ($file) {
	while (!feof($file)) {
		$line = fgetss($file);
		if ($student) {
			echo str_replace('|', '<br />', $line); 
		} else {
			echo str_replace('|', '|<br />', $line);
		}
	}
}
fclose($file);
?>