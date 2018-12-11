<?php

//-------------------------------------------------------------
// CONSTANTS
// Edit values on right, DO NOT change values in capitals
//-------------------------------------------------------------

define('SCRIPT_VERSION', '3.6');
define('B8_VERSION', '3');
define('SCRIPT_NAME', 'Maian Support');
define('SCRIPT_URL', 'maiansupport.com');
define('SCRIPT_ID', 10);

define('GLOBAL_PATH', substr(dirname(__file__),0,strpos(dirname(__file__),'control')-1) . '/');
define('MSW_PHP', (version_compare(PHP_VERSION, '7.1.0', '<') ? 'old' : 'new'));
define('DEFAULT_DATA_PER_PAGE', 25);
define('SUBLINK_SEPARATOR', ' / ');
define('DISPLAY_LOGIN_MSG', 1);

?>