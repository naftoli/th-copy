<?
$val = strtolower(trim($_POST['childAdded']));
if ($val) {
	header("Location: addchild.html");
	exit;
} else {
	header("Location: re_register.html");
	exit;
}
?>