<? 
$admin_auth = array('school'); 
require('header.php');

include('classes/school.php');
$school_id = 82;
$school = new school($school_id);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE>School Student</TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="functions.js"></SCRIPT>
		
		<SCRIPT type="text/javascript">
		</SCRIPT>		
	</HEAD>

	<body>
	
		<? include('admin_header.php'); ?>
		
		<DIV CLASS="body">
		
			
			<DIV class="left_menu">
				<? include('admin_inc.php'); ?>
			</DIV>
			
			<H1>
				Students
			</H1>
				
		</DIV> 
		
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
