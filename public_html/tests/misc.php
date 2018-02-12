<pre>
<?php
$todayHe = jdtojewish(unixtojd());
$arrHe = explode('/', $todayHe);
print_r($arrHe);

echo (function_exists('sha1') ? "sha1" : "md5")."\n";

require_once($_SERVER["DOCUMENT_ROOT"].'/helpdesk/control/connect.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/helpdesk/control/functions.php');

//echo SECRET_KEY;
