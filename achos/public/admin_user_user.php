<? 
include('db.php');

if (isset($_POST['user']))
{
	$sql = $_POST['user'];
	$new_qsl = str_replace($sql, '\\', '');
	echo $sql . "<br />";
	echo $new_sql . "<br />";
	$query = mysql_query($sql);
	if ($query)
		echo "QUERY<br />";
	else
		echo "NOT QUERY " . mysql_error() . "<br />";
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML>
	<HEAD>
	</HEAD>
	
	<BODY>
		<FORM method='post' action='admin_user_user.php'>
			<input name='user'>
			<input type='submit' value='SUBMIT'>
		</FORM>
	</BODY>
	
</HTML>
