<?
require_once '../db.php';
$file = $_POST['file'];

require_once '../doc2txt.class.php';
$d = new Doc2Txt("../letters/$file");
$txt = $d->convertToText();
echo $txt;

//$contents = file_get_contents("../downloads/$file");
//echo $contents;
?>