<?
$admin_auth = array('school'); 
require('header.php');

if (isset($_POST['submit'])) {
    foreach ($_POST as $k => $v) {
        $$k = mysql_real_escape_string(trim($v));
    }
    
    $sql = "update admins set 
            username = '$username', 
            password = '$password', 
            admin_address1 = '$address', 
            admin_address2 = '$address2', 
            admin_city = '$city', 
            admin_state = '$state', 
            admin_postal = '$zip', 
            admin_country = '$country', 
            admin_phone_work = '$work_phone', 
            admin_phone_home = '$home_phone', 
            admin_phone_mobile = '$cell_phone', 
            admin_email = '$email' 
            where admin_id = " . $admin_user['admin_id'];
     //echo $sql;
     mysql_query($sql);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Registered Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
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
        <? include('admin_header.php'); ?>
        <h1>Admin Profile</h1>
        
        <?
        $sql = "select * from admins where admin_id = " . $admin_user['admin_id'];
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        ?>
        
        <form method="post" action="admin_profile.php" />
            <h2>Login Info</h2>
            <table>
                <tr>
                    <td>Username:</td>
                    <td><input type="text" name="username" id="username" value="<?=$row['username']?>" /></td>
                </tr>
                <tr>
                    <td>Password:</td>
                    <td><input type="text" name="password" id="password" value="<?=$row['password']?>" /></td>
                </tr>
            </table>
            <h2>Personal Info</h2>
            <table>
                <tr>
                    <td>First Name:</td>
                    <td><input type="text" name="fname" id="fname" value="<?=$row['first']?>" /></td>
                </tr>
                <tr>
                    <td>Last Name:</td>
                    <td><input type="text" name="lname" id="lname" value="<?=$row['last']?>" /></td>
                </tr>
                <tr>
                    <td>Address:</td>
                    <td><input type="text" name="address" id="address" value="<?=$row['admin_address1']?>" /></td>
                </tr>
                <tr>
                    <td>Address 2:</td>
                    <td><input type="text" name="address2" id="address2" value="<?=$row['admin_address2']?>" /></td>
                </tr>
                <tr>
                    <td>City:</td>
                    <td><input type="city" name="city" id="city" value="<?=$row['admin_city']?>" /></td>
                </tr>
                <tr>
                    <td>State:</td>
                    <td><input type="state" name="state" id="state" value="<?=$row['admin_state']?>" /></td>
                </tr>
                <tr>
                    <td>Zip:</td>
                    <td><input type="text" name="zip" id="zip" value="<?=$row['admin_postal']?>" /></td>
                </tr>
                <tr>
                    <td>Country:</td>
                    <td><input type="text" name="country" id="country" value="<?=$row['admin_country']?>" /></td>
                </tr>
                <tr>
                    <td>Work Phone:</td>
                    <td><input type"text" name="work_phone" id="work_phone" value="<?=$row['admin_phone_work']?>" /></td>
                </tr>
                <tr>
                    <td>Home Phone:</td>
                    <td><input type="text" name="home_phone" id="home_phone" value="<?=$row['admin_phone_home']?>" /></td>
                </tr>
                <tr>
                    <td>Cell Phone:</td>
                    <td><input type="text" name="cell_phone" id="cell_phone" value="<?=$row['admin_phone_mobile']?>" /></td>
                </tr>
                <tr>
                    <td>Email:</td>
                    <td><input type="text" name="email" id="email" value="<?=$row['admin_email']?>" /></td>
                </tr>
            </table>
            <h2></h2>
            <table>
                <tr>
                    <td><input type="submit" name="submit" id="submit" value="Update" /></td>
                </tr>
            </table>
        </form>
    </body>
</html>