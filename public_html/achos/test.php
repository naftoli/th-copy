<html>
<head>
</head>
<body>
<?
if (isset($_POST['submit'])) {
	$barcode = $_POST['barcode'];
	$num = $_POST['num'];
	$value = $barcode;
	$barcode--;
	while ($barcode > 0) {
		$value *= $barcode;
		$barcode--;
	}
	$value /= $num;
	echo "Number of cards per child: " . number_format($value);
	exit;
}
?>
<form method="post" action="test.php">
Max number of children: <input name="num"><br />
Number of digits in barcode: <input name="barcode"><br />
<input type="submit" name="submit" value="submit">
</form>
</body>
</html>