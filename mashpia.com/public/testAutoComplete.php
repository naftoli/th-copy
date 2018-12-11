<?
print_r($_POST);
?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
 		<link rel="stylesheet" href="//code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css">
 		<script src="//code.jquery.com/jquery-1.10.2.js"></script>
		<script src="//code.jquery.com/ui/1.11.3/jquery-ui.js"></script>
	</head>
	
	<body>
		<form action="testAutoComplete.php" method="post">
			<input name="users" id="users">
			<input type="hidden" id="user_id" name="user_id" />
			<input type="submit" name="submit" value="submit" />
		</form>
	</body>
	
	<script>
		$(function() {
			$.post('ajax/getStudents.php', {school : 82}, function( success ) {
				var users = $.parseJSON( success );
				var students = [];
				for (var u in users) {
					students.push({
						label : users[u], 
						value : u
					});
				}
				$("#users").autocomplete({
					source : students, 
					select : function(e, ui) {
						$("#users").val( ui.item.label );
						$("#user_id").val( ui.item.value );
						return false;
					}
				});
			});
		});
	</script>
</html>