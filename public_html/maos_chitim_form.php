<?
$admin_auth = array('school'); 
require('header.php');

require_once 'class.adminSchools.php';    
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();
$schoolIDs = array();
foreach ($schools as $id => $school) {
	if (isset($_POST['school']) && $_POST['school'] > 0) {
		if ($_POST['school'] == $id) {
			$schoolIDs[] = $id;
			break;
		}
	} else {
		$schoolIDs[] = $id;
	}
}

$classes = array();
$classNames = array();
foreach ($schoolIDs as $id) {
	$sql = "select * from classes where school_id = " . $id . " and class_era = 0 order by class_grade, class_sub";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		if (isset($_POST['grade']) && $_POST['grade'] > 0) {
			if ($_POST['grade'] == $row['class_id']) {
				$classes[$id][] = $row['class_id'];
				$classNames[$row['class_id']] = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
				break;
			}
		} else {
			$classes[$id][] = $row['class_id'];
			$classNames[$row['class_id']] = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
		}
	}
}

$users = array();
$userNames = array();
foreach ($classes as $school => $grades) {
	foreach ($grades as $grade) {
		$sql = "select * from users where class_id = " . $grade . " and user_registered > 0 order by last, first";
		$result = mysql_query($sql);
		if (mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_assoc($result))	{
				if (isset($_POST['user_id']) && $_POST['user_id'] > 0) {
					if ($_POST['user_id'] == $row['user_id']) {
						$users[$grade][] = $row['user_id'];
						$userNames[$row['user_id']] = $row['first'] . ' ' . $row['last'];
					}
				} else {
					$users[$grade][] = $row['user_id'];
					$userNames[$row['user_id']] = $row['first'] . ' ' . $row['last'];
				}
			}
		} else {
			$users[$grade][] = 0;
		}
	}
}

require_once 'class.maosChittim.php';
$m = new MaosChittim(5774);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Maos Chitim Form</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <script>
        	$(function() {
        		$("#school").change(function() {
        			var val = $(this).val();
        			if (val > 0) {
        				$("#grade").empty();
        				$("#grade").append("<option value='0'>All</option>");
        				$("#user").empty();
        				$("#user").append("<option value='0'>All</option>");
        				$.get('ajax/getClasses.php', {id : val, hasUsers : 1}, function(data) {
        					var grades = $.parseJSON(data);
        					var str = '';
        					for (id in grades) {
        						str += "<option value=" + id + ">" + grades[id]	+ "</option>";
        					}
        					$("#grade").append(str);
        				});
        			}
        		});
        		
        		<? if (isset($_POST['school'])) : ?>
        			$("#school").trigger('change');
        		<? endif; ?>
        		
        		$("#grade").change(function() {
        			var val = $(this).val();
        			if (val > 0) {
        				$("#user").empty();
        				$("#user").append("<option value='0'>All</option>");
        				$.get('ajax/getUsers.php', {id : val}, function(data) {
        					var users = $.parseJSON(data);
        					var str = '';
        					for (id in users) {
        						str += "<option value=" + id + ">" + users[id]	+ "</option>";
        					}
        					$("#user").append(str);
        				});
        			}
        		});
        		
        		<? if (isset($_POST['grade'])) : ?>
        			$("#grade").trigger('change');
        		<? endif; ?>        		
        		
        		$(".pledged").blur(function() {
        			var val = $(this).val();
        			var user = $(this).attr('id');
        			$.post('ajax/maosChittim.php', {val : val, user : user, type : 'pledged', action : 'set'}, function(data) {
        				if (data) {
        					//alert(data);
        					//alert("updated");
        				} else {
        					//alert("not updated");
        				}
        			});
        		});
        		
        		$(".raised").blur(function() {
        			var val = $(this).val();
        			var user = $(this).attr('id');
        			$.post('ajax/maosChittim.php', {val : val, user : user, type : 'raised', action : 'set'}, function(data) {
        				if (data) {
        					//alert("updated");
        				} else {
        					//alert("not updated");
        				}
        			});
        		});
        	});
        </script>
        <style type='text/css'>
            p {
                font-size: 24px;
            }
            table {
                font-size: 12px;
            }
            th, td {
                padding: 6px 10px;
            }
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
            form td {
            	padding: 2px;
            	font-size: 13px;
            }
            @media print{
	            .no-print {
	            	display: none;
	            }
	        }
        </style>
    </head>
    
    <body>
    	<? include('admin_header.php'); ?>
        <h1 class="no-print">Maos Chitim Form</h1>
        
        <form action="maos_chitim_form.php" method="post">
	        <fieldset>
	        	<legend>Filter</legend>
	        	<table>
		        	<? if (count($schools) > 1) : ?>
		        	<tr>
		        		<td>Choose School:</td> 
			        	<td>
			        		<select name='school' id='school'>
				        		<option value='0'>All</option>
				        		<? 
				        		foreach ($schools as $id => $school) {
				        			if (isset($_POST['school']) && $_POST['school'] == $id) {
				        				$selected = "selected='selected'";
				        			} else {
				        				$selected = '';
				        			}
				        			echo "<option value='$id' $selected>" . $school . "</option>";
								}
								?>        		
			        		</select>
			        	</td>
		        	</tr>
		        	<? endif; ?>
		        	<tr>
			        	<td>Choose Platoon:</td>
			        	<td>
			        		<select name='grade' id='grade'>
				        		<option value='0'>All</option>
				        	</select>
			        	</td>
			        </tr>
			        <tr>
			        	<td>Choose Soldier:</td> 
			        	<td>
			        		<select name='user' id='user'> 
				        		<option value='0'>All</option>
				        	</select>
				        </td>
			        </tr>
			        <tr>
		        		<td><input type="submit" name="submit" value="go" id="submit" /></td>
		        		<td>&nbsp;</td>
		        	</tr>
		        </table>
	        </fieldset>
	    </form>
       	<br />
       	
       	<? foreach ($schools as $id => $name) : ?>
       		<? if (!isset($classes[$id])) continue; ?>
       		<? 
       		if (count($schools) > 1) {
       			echo "<p>" . $name . "</p>";
       		}
			?>
       		
	       	<table>
	       		<tr>
	       			<th>Platoon</th>
	       			<th>Chayol</th>
	       			<th>Maos Chitim Pledges</th>
	       			<th>Maos Chitim Raised</th>
	       		</tr>
	       		
	       		<?
	       		foreach ($classes[$id] as $grade) {
	       			if (!isset($users[$grade])) continue;
	       			foreach ($users[$grade] as $user) {
	       				if ($user) {
	       					if (isset($_POST['user']) && $_POST['user'] > 0 && $_POST['user'] != $user) continue;
		       				echo "<tr><td>" . $classNames[$grade] . "</td><td>" . $userNames[$user] . "</td>";
							$mc = $m->getInfoFor($user, 'user');
							echo "<td>$<input size='5' type='text' class='pledged' id='" . $user . "' value='" . $mc['pledged'] . "' /></td>";
							echo "<td>$<input size='5' type='text' class='raised' id='" . $user . "' value='" . $mc['raised'] . "' /></td></tr>";
						}
	       			}
	       		}
	       		?>
	       	</table>
	       	<br /> 
	    <? endforeach; ?>
    </body>
</html>