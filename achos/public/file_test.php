<?
$file = fopen('home_text.txt', 'w');
fwrite($file, 'just testing');
fclose($file);
?>