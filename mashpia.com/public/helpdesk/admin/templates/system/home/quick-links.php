<?php if (!defined('PATH')) { exit; }

/*
  QUICK LINKS
  ---------------------------------------

  Add Links here to be quickly accessible via the admin homescreen.
  To have separate quick links file for any user, make a copy of this file and rename it
  with the support team members ID. For example, John Smith is a support team member
  and his ID is 5. Create the following file:

  admin/templates/system/home/quick-links-5.php

  These links would only be visible to John.

*/

?>
<i class="fa fa-angle-right fa-fw"></i> <a href="?p=settings">Helpdesk Settings</a><br>
<i class="fa fa-angle-right fa-fw"></i> <a href="https://www.maiansupport.com" onclick="window.open(this);return false">Maian Support Website</a><br>
<i class="fa fa-angle-right fa-fw"></i> <a href="https://www.maianscriptworld.co.uk" onclick="window.open(this);return false">MSWorld Website</a><br>
<i class="fa fa-angle-right fa-fw"></i> <a href="../index.php" onclick="window.open(this);return false">Your Helpdesk</a><br>
<i class="fa fa-angle-right fa-fw"></i> <a href="../docs/" onclick="window.open(this);return false">Your Helpdesk Docs</a>
<hr>
<span class="quickText"><?php echo $msadminlang3_1[21]; ?>:<br>admin/templates/system/home/quick-links.php</span>