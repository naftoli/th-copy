<!DOCTYPE html>
<html>
	<head>
		<title>Testing Async</title>
	</head>
	<body>
		<div class='main'></div>
	</body>
	
	<script src="jquery-1.8.1.min.js"></script>
	<script>
		$(function() {
			$.post('ajax/getUser.php', { 
				user: 4555 
			}).done( function() {
				$(".main").text( 'updated' );
			});
		});
	</script>
</html>