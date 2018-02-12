<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Admin Profile</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
        	body {
        		background: none;
        	}
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
            .page-break {
                page-break-after: always;
            }
        </style>
    </HEAD>

    <BODY>    
        <?
        $sql = "select * from admins where admin_id = " . $admin_user['admin_id'];
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        ?>
        
        <h2>Personal Info</h2>
        <table>
        	<tr>
        		<td>Title:</td>
        		<td><input type="text" name="title" id="title" value="<?=$row['title']?>" /></td>
        	</tr>
            <tr>
                <td>First Name:</td>
                <td><input type="text" name="fname" id="fname" value="<?=$row['first']?>" /></td>
            </tr>
            <tr>
                <td>Last Name:</td>
                <td><input type="text" name="lname" id="lname" value="<?=$row['last']?>" /></td>
            </tr>
            <tr>
                <td>Email:</td>
                <td><input type="text" name="email" id="email" value="<?=$row['admin_email']?>" /></td>
            </tr>
        </table>
        <h2></h2>
        <table>
            <tr>
                <td><input type="button" name="submit" id="submit" value="Update" onclick="update()" /></td>
            </tr>
        </table>
        
        <script src="scripts/jquery-1.8.3.js"></script>
        <script>
	        function update() {
				$.post('ajax/updateAdmin.php', {
					admin : <?=$admin_user['admin_id']?>, 
					title : $("#title").val(), 
					first : $("#fname").val(), 
					last : $("#lname").val(), 
					email : $("#email").val() 
				}, function(data) {
					if (data) {
						alert("updated");
						window.close();
					}
				});
			}
        </script>
    </body>
</html>