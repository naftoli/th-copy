<?
$admin_auth = array('school'); 
require('header.php');

if ($admin_user['auth'] != 'super') {
	header("Location: admin.php");
	exit;
} 

if (isset($_FILES['file'])) {
	//check that file has doc or docx extension
	$name = $_FILES['file']['name'];
	if (!strpos($name, '.docx') && !strpos($name, 'doc')) {
		echo "You can only upload Word files. Please try again.";
		exit;
	}
	if (is_uploaded_file($_FILES['file']['tmp_name'])) {
		if (move_uploaded_file($_FILES['file']['tmp_name'], "letters/" . $name)) {
			//update db
			$desc = mysql_real_escape_string(trim($_POST['desc']));
			$sql = "insert into communicate_files values(null, '$name', '$desc')";
			mysql_query($sql);
		}
	}
}

$files = array();
$sql = "select * from communicate_files";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$files[] = $row;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<link href="communicate.css" rel="stylesheet" type="text/css">
		<title>Manage Files for Parent Communication</title>
		<script src="jquery-1.8.1.min.js"></script>
        <script src="scripts/jquery.styleselect.js"></script>
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
		<h1>Manage Files for Parent Communication</h1>
		
		<h2>Upload new File</h2>
		<form action="communicate_files.php" enctype="multipart/form-data" method="post">
			<table>
				<tr>
					<td>File:</td>
					<td><input type="file" name="file" id="file" /></td>
				</tr>
				<tr>
					<td>Description:</td>
					<td><input type="text" name="desc" id="desc" size="50" /></td>
				</tr>
				<tr>
					<td><input type="submit" name="submit" id="submit" value="Upload" /></td>
					<td></td>
				</tr>
			</table>
		</form>
		
		<h2>Manage files</h2>		
		<table>
			<tr>
				<th>File</th>
				<th>Description</th>
				<th>Action</th>
			</tr>
			<?
			foreach ($files as $file) {
				echo "<td>" . $file['file'] . "</td><td>" . $file['description'] . "</td>
					<td><a href='#' class='del'>delete</a></td></tr>";
			}
			?>
		</table>
		
		<script>
			$(".del").click(function() {
				var val = $(this).parent().prev('td').prev('td').text();
				var c = confirm("Are you sure you want to delete " + val + " ?");
				if (c) {
					$.post('ajax/file.php', {
						action : 'delete', 
						file : val, 
						folder : 'letters'
					}, function(data) {
						window.location = 'communicate_files.php';
					});
				}
			});
			
			$("#submit").click(function() {
				if ($("#file").val() == '') {
					alert("You must add a file to upload.");
					return false;
				}
			});
		</script>
	</body>
</html>