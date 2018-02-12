
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" dir="<?=$dir?>">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<script src="../../camps/scripts/jquery.tools.min.js"></script>
		<script src="../../scripts/jquery.placeholder.js"></script>				
		
		<script>
			$(document).ready(function() {
			 
				$("input[name='new_button']").live('click', function() {
					
					for (a = 0; a < 10000; a++) {
						$(this).after('<input type="button" name="new_button" value="NEW BUTTON">');
					}
					
				});
			 
				//$("input[name='new_button']").click(function(){
				//	alert("new button");
					
				//	$(this).after('<input type="button" name="new_button" value="NEW BUTTON">');
				//});
				
			});
		</script>
	</head>

	<body>
	
		<input type="button" name="new_button" value="NEW BUTTON">
		
	</body>
	
</html>