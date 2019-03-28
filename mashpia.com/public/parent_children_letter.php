<?php
$admin_auth = array('school'); 
require('header.php');

$parents = array();
$sql = "select a.* from admins a
        join admin_auths aa using (admin_id)
        join users u on u.user_id = aa.id 
        where u.school_id = " . $admin_user['auths']['school'][0];
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $parents[$row['admin_id']] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
    </head>
    <body>
        <?php foreach ($parents as $admin) : ?>
            <div>
            <?=$admin['admin_address1']?><br />
            <?=$admin['admin_city'] . ', ' . $admin['admin_state'] . ' ' . $admin['admin_postal']?>
            <br /><br />            
            Dear <?=$admin['first'] . ' ' . $admin['last']?>,
            <br /><br />
            A parent account has been created for your child/ren in Chayolei Tzivos Hashem.
            <br /><br />
            With it, you’ll able to mark you children’s missions daily, straight from any smartphone (or computer). You’ll also be able to check in on their progress reports, personalize their growth, and stay up-to-date on Tzivos Hashem news from bases around the world.  
            <br /><br />
            Darchei Hachassidus will come alive in your home as managing your kids’ Chayolei Tzivos Hashem accounts becomes easier than ever. Help your young soldier reach the greatest heights in Hashem’s army. 
            <br /><br />
            Your Username is: <?=$admin['username']?><br />
            Your Password is: <?=$admin['password']?> 
            <br /><br />
            To change your username/password simply log into your account on tzivoshashem.com/mobile and click 'edit profile' on the top right hand corner. 
            <br /><br />
            For any questions, help, or feedback, contact your school's Base Commander. 
            <br /><br />
            Wishing you much Yiddishe and Chassidishe Nachas, 
            <br /><br />
            CTH Headquarters</div>
            <div style="page-break-after: always"><br /><br /></div>
        <?php endforeach; ?>
    </body>
</html>