<?
$admin_auth = array('school');
require_once 'header.php';

require_once 'class.achosCustomization.php'; 
$ac = new AchosCustomization;

if (isset($_GET['start'])) {
    $start = $_GET['start'];
    $end = $_GET['end'];
    $ac->setDates($start, $end);
}

require_once 'class.parshos.php';
$p = new Parshos;
$parshos = $p->getParshos();
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <link href="styles/achosCustomization.css" rel="stylesheet" type="text/css">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script type="text/javascript">
        	$(function() {
        		$(".button").hide();
        		$(".task").focus( function() {
        			$(".button").hide();
        			$(this).find('button').show();
        		});
        		$(".button").click( function() {       			
        			var action = $(this).text();
        			if (action == 'save') {
        				var task = $(this).prev('span');
        				var oldName = task.attr('id');
	        			var newName = task.text();
	        			if (oldName != newName) {
		        			$.post('ajax/editTask.php', {oldName : oldName, newName : newName}, function(data) {
		        				alert(data);
		        			});
		        		}
		        	} else if (action == 'delete') {
		        		var task = $(this).prev().prev('span');
        				var oldName = task.attr('id');
		        		$.post('ajax/deleteTask.php', {name : oldName}, function(data) {
		        			alert(data);
		        			if (data == 'deleted.') {
		        				window.location.href = 'editTasks.php';
		        			}
		        		});
		        	}
        		});
        	});
        </script>
    </head>
    
    <body>
        <? require 'admin_header.php'; ?>
        <h1>Edit Tasks</h1>
        
        <div class="infobox2">
        	INSTRUCTIONS: Click into the box that you want to edit.<br />Highlight the words you would like to change and then just type over it.<br />Click "save".
        </div>
        
        <? 
        $tasks = $ac->getTaskNames(); 
        if (!empty($tasks)) {
        ?>
        <table>
        	<tr>
        		<th>Task Name</th>
        		<th>Created By</th>
        	</tr>
        	<?
        	foreach ($tasks as $task) {
        		echo "<tr><td class='task' contenteditable='true'><span id='" . $task['name'] . "'>" . $task['name'] . 
        			"</span> <button class='button'>save</button> <button class='button'>delete</button>" . 
        			"</td><td>" . $task['first'] . ' ' . $task['last'] . "</td></tr>";
        	}
        	?>
        </table>
        <? } ?>
        <pre>
        	<?//=print_r($tasks)?>
        </pre>
	</body>
</html>