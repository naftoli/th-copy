<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

//---------------------------------
// Admin defined include files..
//---------------------------------

include(PATH . 'control/user-defined/defined.inc.php');

//-------------------------
// Database connection..
//-------------------------

include(REL_PATH . 'control/connect.php');

//------------------------
// Other include files..
//------------------------

include(REL_PATH . 'control/functions.php');
include(REL_PATH . 'control/system/constants.php');
mswfileController();
include(REL_PATH . 'control/system/core/sys-controller.php');
include(REL_PATH . 'control/timezones.php');
include(REL_PATH . 'control/classes/class.datetime.php');
include(PATH . 'control/functions.php');
include(REL_PATH . 'control/classes/class.system.php');
include(REL_PATH . 'control/classes/class.parser.php');
include(REL_PATH . 'control/classes/mailer/class.send.php');
include(REL_PATH . 'control/classes/class.page.php');
include(PATH . 'control/classes/class.users.php');
include(PATH . 'control/classes/class.settings.php');
include(PATH . 'control/classes/class.tickets.php');
include(PATH . 'control/classes/class.fieldmanager.php');
include(REL_PATH . 'control/classes/class.bbcode.php');
include(REL_PATH . 'control/classes/class.bootstrap.php');
include(REL_PATH . 'control/classes/class.json.php');
include(REL_PATH . 'control/classes/class.headers.php');

//-----------------------
// Fetch settings..
//-----------------------

$SETTINGS = @mysqli_fetch_object(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM " . DB_PREFIX . "settings LIMIT 1"));

//-------------------------------------------------------
// Check settings. If nothing, direct to installer..
//-------------------------------------------------------

if (!isset($SETTINGS->id)) {
  header("Location: ../install/index.php");
  exit;
}

//----------------------------
// Load language files..
//----------------------------

include_once(REL_PATH . 'content/language/' . $SETTINGS->language . '/lang1.php');
include_once(REL_PATH . 'content/language/' . $SETTINGS->language . '/lang2.php');
include_once(REL_PATH . 'content/language/' . $SETTINGS->language . '/lang3.php');
include_once(REL_PATH . 'content/language/' . $SETTINGS->language . '/lang4.php');
include_once(REL_PATH . 'content/language/' . $SETTINGS->language . '/lang5.php');
include(PATH . 'control/arrays.php');

//---------------------------------
// Mail base path for templates..
//---------------------------------

define('LANG_PATH', REL_PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/');
define('LANG_BASE_PATH', REL_PATH . 'content/language/');

//---------------------
// Default vars..
//---------------------

$cmd        = (isset($_GET['p']) ? strip_tags($_GET['p']) : 'home');
$page       = (isset($_GET['next']) && $_GET['next'] > 0 ? (int) $_GET['next'] : '1');
$count      = 0;
$limit      = (isset($_GET['limit']) ? (int) $_GET['limit'] : DEFAULT_DATA_PER_PAGE);
$limitvalue = $page * $limit - ($limit);
$eString    = array();
$title      = '';
$tabIndex   = 0;
$attString  = array();
$attPath    = array();

//-------------------------------------
// Get support team information..
//-------------------------------------

if ($cmd != 'reset') {
  if ((isset($_SESSION[mswEncrypt(SECRET_KEY) . '_ms_mail']) && isset($_SESSION[mswEncrypt(SECRET_KEY) . '_ms_key']) && mswIsValidEmail($_SESSION[mswEncrypt(SECRET_KEY) . '_ms_mail'])) || (isset($_COOKIE[mswEncrypt(SECRET_KEY) . '_msc_mail']) && isset($_COOKIE[mswEncrypt(SECRET_KEY) . '_msc_key']) && mswIsValidEmail($_COOKIE[mswEncrypt(SECRET_KEY) . '_msc_mail']))) {
    $qStaff = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "users`
              WHERE `email`  = '" . (isset($_SESSION[mswEncrypt(SECRET_KEY) . '_ms_mail']) ? $_SESSION[mswEncrypt(SECRET_KEY) . '_ms_mail'] : $_COOKIE[mswEncrypt(SECRET_KEY) . '_msc_mail']) . "'
              AND `accpass`  = '" . (isset($_SESSION[mswEncrypt(SECRET_KEY) . '_ms_key']) ? $_SESSION[mswEncrypt(SECRET_KEY) . '_ms_key'] : $_COOKIE[mswEncrypt(SECRET_KEY) . '_msc_key']) . "'
              LIMIT 1
              ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    $MSTEAM = mysqli_fetch_object($qStaff);
    if (!isset($MSTEAM->name) && !in_array($cmd, array(
      'login',
      'logout'
    ))) {
      unset($_SESSION[mswEncrypt(SECRET_KEY) . '_ms_mail'], $_SESSION[mswEncrypt(SECRET_KEY) . '_ms_key']);
      header("Location: index.php?p=logout");
      exit;
    }
    // Set support team timezone..
    define('MSTZ_SET', (isset($MSTEAM->timezone) && $MSTEAM->timezone != '0' ? $MSTEAM->timezone : $SETTINGS->timezone));
    date_default_timezone_set(MSTZ_SET);
  }
}

//------------------------
// Pass reset check
//------------------------

if ($cmd != 'reset' && !isset($_GET['ajax']) && defined('PASS_RESET')) {
  die('PASS_RESET option exists in defined file. Possibly from password reset. This MUST be commented out or removed.
       Refer to the <a href="../docs/reset.html">documentation</a>.');
}

//------------------------
// Timezone override
//------------------------

if (!defined('MSTZ_SET')) {
  define('MSTZ_SET', $SETTINGS->timezone);
  date_default_timezone_set(MSTZ_SET);
}

//------------------------
// Access pages
//------------------------

if (isset($MSTEAM->name) && $cmd != 'reset') {
  $userAccess          = mswUserPageAccess($MSTEAM);
  include(PATH . 'templates/system/team/team-perms.php');
}

//-------------------
// Load classes..
//-------------------

$MSPARSER           = new msDataParser();
$MSDT               = new msDateTime();
$MSYS               = new msSystem();
$MSBB               = new bbCode_Parser();
$MSMAIL             = new msMail();
$MSTICKET           = new supportTickets();
$MSUSERS            = new systemUsers();
$MSSET              = new systemSettings();
$MSFM               = new fieldManager();
$JSON               = new jsonHandler();
$MSBOOTSTRAP        = new msBootStrap();
$HEADERS            = new htmlHeaders();
$MSSET->datetime    = $MSDT;
$MSSET->settings    = $SETTINGS;
$MSUSERS->settings  = $SETTINGS;
$MSTICKET->settings = $SETTINGS;
$MSPARSER->bbCode   = $MSBB;
$MSPARSER->settings = $SETTINGS;
$MSDT->settings     = $SETTINGS;
$MSTICKET->team     = (isset($MSTEAM->id) ? $MSTEAM : '');

//----------------------------
// Priority levels
//----------------------------

$levelPrKeys    = $MSYS->levels('', false, true);
$ticketLevelSel = $MSYS->levels('', true);

//-------------------
// Var overides.
//-------------------

$cmd = mswCallBackUrls($cmd);

// Does installer still exist..
if (!defined('LIC_DEV') && is_dir(REL_PATH . 'install')) {
  die('Install directory exists on server. Please rename "install" directory or remove it for security, then refresh page.');
}

//---------------------------------------------------
// Set ticket id if coming from link in email..
//---------------------------------------------------

if (isset($_GET['ticket']) && REDIRECT_TO_TICKET_ON_LOGIN) {
  if (isset($MSTEAM->name)) {
    $SUPTICK = mswGetTableData('tickets', 'id', mswReverseTicketNumber($_GET['ticket']));
    if (isset($SUPTICK->id)) {
      header("Location: index.php?p=view-" . ($SUPTICK->isDisputed == 'yes' ? 'dispute' : 'ticket') . "&id=" . $SUPTICK->id);
      exit;
    }
  }
  $_SESSION[mswEncrypt(SECRET_KEY) . 'thisTicket'] = $_GET['ticket'];
}

?>