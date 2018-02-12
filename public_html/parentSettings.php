<?
$admin_auth = array('user'); 
require('header.php'); 

include("classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
$admin->get_markable_children();
//echo "<pre>"; print_r($admin->children); echo "</pre>";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Mission Settings</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style>
        	fieldset {
                border: 1px solid white;
                padding: 10px;
                padding-top: 0px;
                -moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px;
                font-size: 16px;
            }
            legend {
                margin-left: 20px;
                padding: 5px;
                color: purple;
            }
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
            .middle {
            	text-align: center;
            	margin: auto;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Mission Settings</h1>
        
        <fieldset>
        	<legend>Mission Settings</legend>
        	<table id="users">
        		<tr>
        			<th>Child</th>
        			<th>Grade</th>
        			<th>No Picture Missions</th>
        			<th>Small Picture Missions</th>
        		</tr>
        		<? foreach ($admin->children as $child) { ?>
        			<tr>
        				<td><?=$child->first . ' ' . $child->last?></td>
        				<td><?=$child->school_class->class_grade . 
			        		(empty($child->school_class->class_sub) ? '' : '-' . 
			        		$child->school_class->class_sub)?></td>
	        			<td class='middle'>
	        				<input class='user' type='radio'  
	        					name='<?=$child->user_id?>' value='1' 
	        					<? if ($child->pic_mission_type == 1) echo "checked"; ?> />
	        			</td>
	        			<td class='middle'>
	        				<input class='user' type='radio'  
	        						name='<?=$child->user_id?>' value='2' 
	        						<? if ($child->pic_mission_type == 2) echo "checked"; ?> />
	        			</td>
	        		</tr>
	        	<? } ?>
        	</table>
        </fieldset>
        
        <br />
        <fieldset>
        	<legend>Mission Language</legend>
        	<table id="lang">
        		<tr>
        			<th>Child</th>
        			<th>Grade</th>
        			<th>English</th>
        			<th>Yiddish</th>
        		</tr>
        		<? foreach ($admin->children as $child) { ?>
        			<tr>
        				<td><?=$child->first . ' ' . $child->last?></td>
        				<td><?=$child->school_class->class_grade . 
			        		(empty($child->school_class->class_sub) ? '' : '-' . 
			        		$child->school_class->class_sub)?></td>
	        			<td class='middle'>
	        				<input class='child' type='radio' 
	        					name='|<?=$child->user_id?>' value='1' 
	        					<? if ($child->lang_id == 1) echo "checked"; ?> />
	        			</td>
	        			<td class='middle'>
	        				<input class='child' type='radio'  
	        						name='|<?=$child->user_id?>' value='2' 
	        						<? if ($child->lang_id == 2) echo "checked"; ?> />
	        			</td>
	        		</tr>
	        	<? } ?>
        	</table>
        </fieldset>
        
	</body>
	
    <script type="text/javascript">
    	$( function() {				    				
			$(".user").click( function() {
				if ($(this).is(":checked")) {
    				var id = $(this).attr('name');
    				var val = $(this).val();
        			$.post('ajax/updateMarking.php', {
        					id : id, setting : val, type : 'user', field : 'pic_mission_type'
        				}, function(data) {
        				if (data == 1) {
        					alert("Updated!");
        				} else {
        					alert("No updates were performed.");
        				}
        			});
        		}
    		});
    		
    		$(".child").click( function() {
				if ($(this).is(":checked")) {
    				var name = $(this).attr('name');
    				var id = name.substring(1);
    				var val = $(this).val();
        			$.post('ajax/updateMarking.php', {
        					id : id, setting : val, type : 'user', field : 'lang_id'
        				}, function(data) {
        				if (data == 1) {
        					alert("Updated!");
        				} else {
        					alert("No updates were performed.");
        				}
        			});
        		}
    		});
    	});
    </script>
</html>