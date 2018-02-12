<?php
$admin_auth = array('school'); 
require('header.php');

require_once 'class.adminSchools.php';       
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$year = 5777;
$children = array();
$sql = "select u.first, u.last from users u
        join th_chidon tc using (user_id)
        where tc.shabbaton = 1
        and tc.year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $children[] = $row['first'] . ' ' . $row['last'];
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            body {
                font-family: sans-serif;
                font-size: 12px;
                width: 7in;
                margin: auto;
                padding: 20px;
            }
        </style>
    </head>
    <body>
        <?php foreach ($children as $child) : ?>
        <div>
            Dear Parents, 
<h3>Mazal Tov! Mazal Tov!</h3>
<b><?=ucwords($child)?></b> has worked so hard studying Sefer Hamitzvos throughout this year. We hope you have enjoyed much nachas from the hours of Torah study and the incredible knowledge your child has accumulated. 
<br /><br />We are proud to inform you that he has earned a qualifying mark on the tests and is eligible to join the International Chidon Shabbaton.
<h3>Shabbaton</h3>
Chidon Shabbaton will be taking place in Crown Heights, beginning on a Thursday and ending the following Sunday. Our school will be sending a chaperone to take responsibility for your child during the Shabbaton.
<br /><br />Girls’ Dates: Thursday Yud Ches Adar (March 16) at 8:00 a.m. – Chof Alef Adar (March 19) at 4:30 p.m.
<br /><br />Boys’s Dates: Thursday Chof Hey Adar (March 23) at 8:00 a.m. – Chof Ches Adar (March 26) at 4:30 p.m. 
<h3>Enrollment</h3>
Chidon enrollment will open on Monday, Chof Daled Shvat (February 20) and will remain open for one week only.
<br /><br />Please go to mashpia.com/mobile and log in, click on the button “Shabbaton Enrollment” under your child's name and follow the steps on the screen.
<br /><br />It is the parent's’ responsibility to make sure that all the information is entered correctly. The enrollment will not be processed if any information is left out.
<br /><br />The cost for the Shabbaton is $250 per child. Thanks to Tzivos Hashem and their sponsors we are able to charge parents a 50% discounted rate of $125 per child. 
<br /><br />Registration and payment information must be completed before <b>Monday, Alef Adar (February 27) at 11:59 p.m.</b> We apologize in advance that there will be no exceptions. 
<br /><br />Your child will receive a packet explaining the guidelines for the Chidon Shabbaton including Shabbaton Schedule, packing list, rules and instructions for the final. Please take the time to review it with him/her and ensure that your child is properly prepared. 
<h3>Waivers</h3>
Your child will also be receiving waiver. Please fill it out and return it to the Chidon coordinator in your school. Without a waiver, your child will not be permitted to take part in the trip. 
<h3>Whatsapp Broadcast for Parents</h3>
A WhatsApp broadcast will keep parents connected throughout the Shabbaton. Save +1 (718) 907- 8853 to your phone and send a whatsapp message “Boys,” “Girls,” or “Both” to receive updates and pictures throughout the Shabbaton.
<h3>Game Show Tickets</h3>
Tickets for the Chidon Event and Award Ceremony are now available to purchase on chidon613.com. We are waiting until next week to make Chidon tickets available to the public. Last year the Chidon was a sold out event- be sure to purchase your tickets now!
<br /><br />We hope the Chidon Sefer Hamitzvos will continue to motivate your child to learn and master the 613 mitzvos, giving them a knowledge and appreciation for Hashem’s mitzvos for the rest of their lives. May we merit the coming of Moshiach, when we will finally be able to keep all 613 mitzvos.
<br /><br />For more details feel free to reach out and contact me at (Chidon Coordinator’s contact info),
<br /><br />Much Hatzlocha,
<br /><br />Chidon Coordinator’s name
        </div>
        <div style="page-break-after: always"></div>
        <?php endforeach; ?>
    </body>
</html>