<?php
require 'db.php';
$admins = array();
/*
$sql = "select a.* from admins a 
        join admin_auths aa using (admin_id)
        join users u on u.user_id = aa.id 
        where u.school_id = 1";
*/
$sql = "select * from achos.admins where admin_email in (
'perelbliz@gmail.com',
'brikmandoba@gmail.com',
'rickle1468@gmail.com',
'risha11213@gmail.com',
'lovesmiling00@gmail.com',
'chayablachman4a@gmail.com',
'Chayale678@gmail.com',
'chanabbutman@gmail.com',
'rikula077@gmail.com',
'peri.rimler@gmail.com',
'smilyhenny@gmail.com',
'mushkareiter@gmail.com',
'chanaleehrenreich@gmail.com',
'drainitz@gmail.com',
'rivkahblum@gmail.com',
'saraschusterman@gmail.com',
'sarachnj@gmail.com',
'devorah.reiter@gmail.com',
'rochelmunitz@gmail.com',
'mushkacousin@gmail.com',
'Raizel@juno.com',
'tziviadeitsch3@gmail.com') 
and admin_id > 700";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $admins[] = $row;
}

// To send HTML mail, the Content-type header must be set
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$headers .= 'From: Brhighschool5777@gmail.com' . "\r\n";
$headers .= 'Reply-to: Brhighschool5777@gmail.com' . "\r\n";

$subject = "Your personalized account for BRhighschool.com";

$sent = 0;
foreach ($admins as $admin) {
    $to = $admin['admin_email'];
    $msg = "Hi there!<br /><br />
Welcome to your personalized account for BRhighschool.com! This account allows you to keep track of all of your Tzarche Tzibbur hours.<br /><br />
Here are your account details.
<br /><br />
Username: " . $admin['username'] . "<br />
Password: " . $admin['password'] . "<br />
<br />
Every week, right after completing your hour, log in to your account the time you have spent actively doing your tzarchei tzibbur. To ensure accuracy and honesty please fill it in as soon as possible.
<br /><br />
On your account, there will be two totals. One for points, and the other for visits.
<br /><br />
Any visit which is less than 60 min will not register in the system.
<br /><br />
Any visit which is at least 60 min, counts as 1 visit and for each 60 min counts as 1 point.
So if a you go on a visit of 90 min, then you have 1 visit with 1 point (and 30 minutes extra in the bank).
Once you go on your next visit, the minutes saved in the bank from your previous visit will now be added. If it's another 90 min then you would end up with 2 visits and 3 points.
<br /><br />
You are able to access your account from a desktop computer or use it as a mobile site on  your phone. There will also be the option iy”h to log in your hours on a computer set up in school. 
<br /><br />
Keep on changing the world one good deed at a time and remember to log it all in to BRhighschool.com!
<br /><br />
Looking forward to seeing all those hours adding up,
<br /><br />
The Chessed Committee";

   if (mail($to, $subject, $msg, $headers)) $sent++;
}
echo "Emailed: " . $sent;
?>