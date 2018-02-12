<?php
$admin_auth = array('school');
require 'header.php';
?>
<!DOCTYPE html>
<html>
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Students</title>
<style>
table {
	width: 100%;
}
th, td {
	border: none !important;
	vertical-align: text-top;
	padding: 0 !important;
    font-size: 12px;
}
#newStudent td {
    padding: 3px !important;
}
</style>
</head>

<body>
<? include('admin_header.php');?>
<h1>Students Info</h1>
<?php
//print_r( $admin_user['auths'] );
$students = array();
$sql = "select a.*, ut.subject_id, u.class_id from users u
		join user_tracks ut using (user_id) 
        join admin_auths aa on aa.id = u.user_id
        join admins a using (admin_id)
        where aa.role_id = 1
        and u.heb_year in (5777,5778)  
        and u.school_id in (" . implode(',', $admin_user['auths']['school']) . ")";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $students[] = $row;
}

$grades = array();
$sql = "select c.school_id, c.class_id, c.class_grade, c.class_sub, s.school_name from classes c
        join schools s using (school_id)
        where class_era = 0 and school_id in (" . implode(',', $admin_user['auths']['school']) . ")
        order by school_id, class_grade, class_sub";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $grades[] = $row;
}
?>
<p>
    <a href="#new">create new student</a>
</p>
<p>
	<button id='save'>Save</button>
</p>
<table id='editTable'>
    <tr>
		<th></th>
        <th>First</th>
        <th>Last</th>
        <th>Username</th>
        <th>Password</th>
		<th>Type</th>
		<th>Class</th>
        <th>Email</th>
        <th>Phone</th>
    </tr>
    <?php
    foreach ($students as $row) {
        echo "<tr id=" . $row['admin_id'] . "><td><input type='checkbox' class='saveThis' /></td>
			<td><input type='text' size='8' class='fname' value='" . $row['first'] . "' /></td> 
            <td><input type='text' size='8' class='lname' value='" . $row['last'] . "' /></td>
            <td><input type='text' size='10' class='username' value='" . $row['username'] . "' /></td>
            <td><input type='text' size='8' class='password' value='" . $row['password'] . "' /></td>
			<td><select name='type' class='type'>";
		for ($i = 2; $i < 5; $i++) {
			echo "<option value='" . $i . "'";
			if ($i == $row['subject_id']) echo " selected='selected'";
			echo " />";
			switch ($i) {
				case 2:
					echo "Hoo";
					break;
				case 3:
					echo "FC";
					break;
				case 4:
					echo "Personal";
					break;
			}
			echo "</option>";
		}
		echo "</select></td>";
		echo "<td><select name='grade' class='grade'>";
		foreach ($grades as $grade) {
			echo "<option value='" . $grade['class_id'] . "'";
			if ($grade['class_id'] == $row['class_id']) echo " selected ";
			echo ">" . $grade['school_name'] .
				': ' . $grade['class_grade'] . (empty($grade['class_sub']) ? '' : '-' . $grade['class_sub']) . "</option>";
		}
		echo "</select></td>
			<td><input type='text' size='20' class='email' value='" . $row['admin_email'] . "' /></td>
            <td><input type='text' size='8' class='phone' value='" . $row['admin_phone_mobile'] . "' /></td>
            <!--<td><button>update</button></td>--></tr>";
	}
    ?>
</table>

<h2 id="new">Create New Student</h2>
<table id="newStudent">
    <tr>
        <td>
            <select name='grade' class='grade'>
                <option value='0'>Select Class</option>
                <?php foreach ($grades as $grade) {
                    echo "<option value='" . $grade['school_id'] . ':' . $grade['class_id'] . "'>" . $grade['school_name'] .
                        ': ' . $grade['class_grade'] . (empty($grade['class_sub']) ? '' : '-' . $grade['class_sub']) . "</option>";
                }
                ?>
            </select>
        </td>
    <tr>
        <td>
            <input type='text' size='15' class='fname' placeholder='First Name' />
        </td>
    </tr>
    <tr>
        <td>
            <input type='text' size='15' class='lname' placeholder='Last Name' />
        </td>
    </tr>
    <tr>
        <td>
            <input type='text' size='10' class='username' placeholder='Username' />
        </td>
    </tr>
    <tr>
        <td>
            <input type='text' size='22' class='email' placeholder='Email' />
        </td>
    </tr>
    <tr>
        <td>
            <input type='text' size='10' class='phone' placeholder='Phone Number' />
        </td>
    </tr>
    <tr>
        <td>
            <select name="type" class="type">
                <option value="2">Hoo</option>
                <option value="3">Friendship Circle</option>
                <option value="4">Personal</option>
            </select>
        </td>
    </tr>
    <tr>
        <td>
            <input type="submit" name="submit" value="Create Student" id="create" />
        </td>
    </tr>
</table>

</body>
<script>
	$(function() {
		$("#save").click( function() {
			var total = 0;
			var updated = 0;
			var errors = [];
			
			$(".saveThis").each( function() {
				if ($(this).is(":checked")) {
					total++;
				}
			});

			$(".saveThis").each( function() {
				if ($(this).is(":checked")) {
					var tr = $(this).parent().parent();
					var id = $(tr).attr('id');
					var fname = $(tr).find('.fname').val().trim();
					var lname = $(tr).find('.lname').val().trim();
					var username = $(tr).find('.username').val().trim();
					var password = $(tr).find('.password').val().trim();
					var email = $(tr).find('.email').val().trim();
					var phone = $(tr).find('.phone').val().trim();
					var type = $(tr).find('.type').val();
					var grade = $(tr).find('.grade').val();
					if (fname == '' || lname == '' || username == '' || password == '' || email == '') {
						alert('You must have all fields except for phone number filled out.');
						return false;
					}
					
					$.post('ajax/updateAdmin.php', {
						id : id,
						fname : fname,
						lname : lname, 
						username : username,
						password : password,
						email : email,
						phone : phone,
						type : type,
						grade : grade
					}, function( success ) {
						if (parseInt(success) == 0) {
							updated++;
						} else {
							errors.push("Error updating " + first + ' ' + last + ".\n");
						}
						if (--total == 0) {
							if (errors.legth) {
								alert(errors);
							} else {
								alert('Updated.');
							}
						}
					});
				}
			});
		});
		/*
		$("button").click(function() {
			var tr = $(this).parent().parent();
			var id = $(tr).attr('id');
            var fname = $(tr).find('.fname').val().trim();
            var lname = $(tr).find('.lname').val().trim();
			var username = $(tr).find('.username').val().trim();
			var password = $(tr).find('.password').val().trim();
			var email = $(tr).find('.email').val().trim();
			var phone = $(tr).find('.phone').val().trim();
			
			if (fname == '' || lname == '' || username == '' || password == '' || email == '' || phone == '') {
				alert('You must have all fields filled.');
				return false;
			}

            $.post('ajax/updateAdmin.php', {
                id : id,
                fname : fname,
                lname : lname, 
                username : username,
                password : password,
                email : email,
                phone : phone
            }, function( success ) {
                if (parseInt(success) == 0) {
                    alert('Updated.');
                } else {
                    alert(success);
                }
            });
		});
        */
        $("#create").click( function() {
            var tr = $(this).parent().parent().parent();
            var fname = $(tr).find('.fname').val().trim();
            var lname = $(tr).find('.lname').val().trim();
			var username = $(tr).find('.username').val().trim();
			var email = $(tr).find('.email').val().trim();
			var phone = $(tr).find('.phone').val().trim();
            var type = $(tr).find('.type').val();
            var grade = $(tr).find('.grade').val();
            
            if (grade.indexOf(':') == -1) {
                alert('You must choose a class.');
                return false;
            }
			
			if (fname == '' || lname == '' || username == '' || email == '' || phone == '') {
				alert('You must have all fields filled.')
				return false;
			}
            
            $.post('ajax/createAdmin.php', {
                fname : fname,
                lname : lname, 
                username : username,
                email : email,
                phone : phone,
                type : type,
                grade : grade
            }, function( success ) {
                if (parseInt(success) == 0) {
                    alert('New Student Created.');
                    location.href = "info_report.php";
                } else {
                    alert(success);
                }
            });
        });
	});
</script>
</html>

