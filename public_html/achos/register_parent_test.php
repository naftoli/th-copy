<?php

	$admin_id  = isset($_POST['admin_id']) ? $_POST['admin_id'] : 0; 	
	$submitted  = isset($_POST['admin_id']) ? $_POST['submitted'] : 0; 	
	if ($submitted)	{
		setcookie('admin_id', $admin_id, 0, '/');
		header('Location: register_parent.php');		
	}
	
?>


<FORM action="" method="post">
Enter User ID to put in cookie <input type="text" name="admin_id" /><br />
<input type="hidden" name="submitted" value='yes'/><br />
<input type="submit" value="Update cookie and register"><br>
</form> 