<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Update Parshos</title>
</head>

<body>
<? include('admin_header.php');?>
<? if ($admin->auth == 'super') : ?>
<h1>Update Parshos</h1>
<?
$handle = fopen("5783-5784 - Sheet1.csv", "r");
$contents = stream_get_contents( $handle );
$arrRows = preg_split("/[\n\r]+/", $contents);
foreach ($arrRows as $strLine) {
    $data = explode("\t", $strLine);
    $start = $data[0];
    $end = $data[1];
    $parsha = $data[2];
    $year = $data[3];
    
    $sql = "insert into parshos values('', $start, $end, '$parsha', '$year')";
    mysql_query( $sql );
}

/*
include_once('db.php');
 * 
$start = 2456194;
$end = 2456200;

$success = 0;
$errors = array();

for ($i = 0; $i < 48; $i++) {
	$sql = "insert into parshos values(null, $start, $end, '', 5773)";
	if ($result = mysql_query($sql)) $success++;
	else $errors[$i] = mysql_error();
	$start += 7;
	$end += 7;
}

echo "Success: " . $success . "<br />";
if (count($errors) > 0) foreach ($errors as $err => $msg) echo "Error for id#" . $err . ": " . $msg . "<br />";
 * 
 */
?>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
