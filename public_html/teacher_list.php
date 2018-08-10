<?php
$admin_auth = array('school');
require 'header.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Teachers</title>
<style>
table {
	width: 100%;
}
th, td {
	border: 1px solid black;
	vertical-align: text-top;
	padding: 6px;
    font-size: 12px;
}
</style>
</head>

<body>
<? include('admin_header.php');?>
<h1>Teacher Logins</h1>
<div class='infobox'>
    <p>If your teachers already have an account with their E-mail address we will connect your platoon to that account.</p>
    <p><strong>Please note that if this is the case the teachers existing account information will override what you enter.</strong></p>
    <p>Please refresh the page when you are done to see the updated information.</p>
    <p>
        If a teacher has access to more then one grade they will be combined to one row. 
        To split them up please email <a href='mailto:bugs@tzivoshashem.org?subject=Remove Teacher From Platoon'>Bugs@tzivoshashem.org</a> for now <strong>with details.</strong>
        (If you do not clearly express which platoon you want removed from which teacher your email may be ignored)
    </p>
</div>
<?
$teachers = array();
require_once 'class.adminSchools.php';       
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();
foreach ($schools as $id => $school) {
    $sql = "select class_id, class_grade, class_sub, email, cell, class_teacher 
			from classes
			where school_id = " . $id . "
			and class_era = 0
            order by class_grade, class_sub";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $sql2 = "select a.* from admins a 
                join admin_auths aa using (admin_id)
                where aa.id = " . $row['class_id'] . " and aa.auth = 'class'";
        $result2 = mysql_query($sql2);
        $row2 = mysql_fetch_assoc($result2);
        $row2['grade'] = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
        $row2['email'] = empty($row2['admin_email']) ? $row['email'] : $row2['admin_email'];
        $row2['cell'] = empty($row2['admin_phone_mobile']) ? $row['cell'] : $row2['admin_phone_mobile'];
		$row2['last'] = empty($row2['last']) ? $row['class_teacher'] : $row2['last'];
		$id = $row2['admin_id'];
        if (!$id) $id = 'class' . $row['class_id'];
        $row2['id'] = $row2['admin_id'] ? $row2['admin_id'] : 'class' . $row['class_id'];

        if ( isset( $teachers[$school][$id] ) ) {
            $teachers[$school][$id]['grade'] .= '<hr style="display: block;">'.$row2['grade'];
        } else {
            $teachers[$school][$id] = $row2;
        }
    }    
}

foreach ($teachers as $school => $info) { ?>
    <table>
        <caption><?=$school?></caption>
        <tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Grade(s)</th>
            <th>Username</th>
            <th>Password</th>
            <th>Email</th>
            <th>Cell Phone</th>
			<th></th>
        </tr>
        <?
        foreach ($info as $class_id => $row) { ?>
            <tr id="<?=$class_id?>">
                <td>
                    <input type='text' size='14' class='first' value='<?=$row['first']?>'/>
                </td>
                <td>
                    <input type='text' size='14' class='last' value='<?=$row['last']?>'/>
                </td>
                <td><?=$row['grade']?></td>
                <td>
                    <input type='text' size='6' class='username' value='<?=$row['username']?>' />
                </td>
                <td>
                    <input type='text' size='6' class='password' value='<?=$row['password']?>' />
                </td>
                <td>
                    <input type='text' size='18' class='email' value='<?=$row['email']?>' />
                </td>
                <td>
                    <input type='text' size='10' class='cell' value='<?=$row['cell']?>' />
                </td>
                <td>
                    <button>update</button>
                </td>
            </tr>
        <?php
		}
        ?>
    </table>
	<p></p>
<? } ?>
</body>
<script>
	$(function() {
		$("button").click(function() {
			var tr = $(this).parent().parent();
			var id = $(tr).attr('id');
			var username = $(tr).find('input.username').val().trim();
			var password = $(tr).find('input.password').val().trim();
            var first = $(tr).find('input.first').val().trim();
            var last = $(tr).find('input.last').val().trim();
			var email = $(tr).find('input.email').val().trim();
			var cell = $(tr).find('input.cell').val().trim();
			var class_id;
			if (id.indexOf('class') != -1) {
				class_id = id.substring(5);
			} else {
				class_id = 0;
			}
			
			if ( username == '' || password == '' || first == '' || last == '' || email == '' || cell == '') {
				alert('You must have all fields filled.')
				return false;
            }
			
			$.post('ajax/updateTeacher.php', {
				id : id,
				username : username,
				password : password,
				class_id : class_id,
                first : first,
                last : last,
				email : email,
				cell : cell
			}, function( success ) {
				if (parseInt(success) == 0) {
					alert('Updated.');
				} else {
					alert(success);
				}
			});
		});
	});
</script>
</html>

