<?php

if (!defined('PARENT')) {
  $HEADERS->err403();
}

//-------------------------
// Database connection..
//-------------------------

include(PATH . 'control/connect.php');

//----------------------------
// Savant template engine..
//----------------------------

include(PATH . 'control/lib/Savant3.php');

//------------------------
// Other include files..
//------------------------

include(PATH . 'control/functions.php');
include(PATH . 'control/system/constants.php');
mswfileController();
include(PATH . 'control/system/core/sys-controller.php');
include(PATH . 'control/classes/class.parser.php');
include(PATH . 'control/classes/mailer/class.send.php');
include(PATH . 'control/classes/class.datetime.php');
include(PATH . 'control/classes/class.system.php');
include(PATH . 'control/classes/class.imap.php');
include(PATH . 'control/classes/class.tickets.php');
include(PATH . 'control/classes/class.accounts.php');
include(PATH . 'control/classes/class.fields.php');
include(PATH . 'control/classes/class.faq.php');
include(PATH . 'control/classes/class.bbcode.php');
include(PATH . 'control/classes/class.page.php');
include(PATH . 'control/classes/class.json.php');
include(PATH . 'control/classes/class.headers.php');
include(PATH . 'control/classes/class.recaptcha.php');
include(PATH . 'control/timezones.php');

//--------------------------
// Login credentials..
//--------------------------

define('MS_PERMISSIONS', mswIsUserLoggedIn());

//----------------------
// Load settings..
//----------------------

$SETTINGS = @mysqli_fetch_object(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "settings`"));
if (!isset($SETTINGS->id)) {
  header("Location: install/index.php");
  exit;
} else {
  $mswLangSetLoader = ($SETTINGS->langSets ? unserialize($SETTINGS->langSets) : array());
}

//-----------------------
// Manual schema fix
//-----------------------

mswManSchemaFix($SETTINGS);

//--------------------------
// For search..
//--------------------------

if (isset($_GET['keys'])) {
  $_GET['p'] = 'search';
}

//----------------------
// Default vars..
//----------------------

$cmd               = (isset($_GET['p']) ? $_GET['p'] : 'home');
$page              = (isset($_GET['next']) && $_GET['next'] > 0 ? (int) $_GET['next'] : '1');
$title             = '';
$eString           = array();
$eFields           = array();
$ticketAttachments = array();
$attachString      = '';
$ticketSystemMsg   = '';
$limit             = (isset($_GET['limit']) ? (int) $_GET['limit'] : 25);
$limitvalue        = $page * $limit - ($limit);
$ms_js_css_loader  = array();
$mswUploadDropzone = array();

//------------------------
// Initiate classes..
//------------------------

$MSPARSER           = new msDataParser();
$MSDT               = new msDateTime();
$MSYS               = new msSystem();
$MSTICKET           = new tickets();
$MSBB               = new bbCode_Parser();
$MSFIELDS           = new customFieldManager();
$MSMAIL             = new msMail();
$FAQ                = new msFAQ();
$MSACC              = new accountSystem();
$MSJSON             = new jsonHandler();
$HEADERS            = new htmlHeaders();
$GRECAP             = new gRecaptcha();
$MSPARSER->bbCode   = $MSBB;
$MSPARSER->settings = $SETTINGS;
$MSDT->settings     = $SETTINGS;
$MSTICKET->parser   = $MSPARSER;
$MSTICKET->settings = $SETTINGS;
$MSTICKET->datetime = $MSDT;
$MSTICKET->fields   = $MSFIELDS;
$MSTICKET->system   = $MSYS;
$MSYS->settings     = $SETTINGS;
$MSYS->datetime     = $MSDT;
$MSFIELDS->parser   = $MSPARSER;
$MSACC->settings    = $SETTINGS;
$GRECAP->settings   = $SETTINGS;
$FAQ->settings      = $SETTINGS;

//---------------------------------
// Loaders
//---------------------------------

if ($SETTINGS->language == '' || !is_dir(PATH . 'content/language/' . $SETTINGS->language)) {
  if (is_dir(PATH . 'content/language/english')) {
    $SETTINGS->language = 'english';
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "settings` SET `language` = 'english'");
  } else {
    die('Error: Language folder <b>' . PATH . 'content/language/' . $SETTINGS->language . '</b> does NOT exist');
  }
}

if (MS_PERMISSIONS != 'guest') {
  $LI_ACC = $MSACC->ms_user();
  define('LANG_PATH', PATH . 'content/language/' . ($LI_ACC->language && is_dir(PATH . 'content/language/' . $LI_ACC->language) ? $LI_ACC->language : $SETTINGS->language) . '/');
  define('MSTZ_SET', (in_array($LI_ACC->timezone, array_keys($timezones)) ? $LI_ACC->timezone : $SETTINGS->timezone));
  date_default_timezone_set(MSTZ_SET);
  define('MS_TEMPLATE_SET', (isset($mswLangSetLoader[$LI_ACC->language]) && is_dir(PATH . 'content/' . $mswLangSetLoader[$LI_ACC->language]) ? $mswLangSetLoader[$LI_ACC->language] : '_default_set'));
  $cmd = (isset($_GET['p']) ? $_GET['p'] : 'dashboard');
} else {
  define('LANG_PATH', PATH . 'content/language/' . $SETTINGS->language . '/');
  define('MSTZ_SET', $SETTINGS->timezone);
  date_default_timezone_set($SETTINGS->timezone);
  define('MS_TEMPLATE_SET', (isset($mswLangSetLoader[$SETTINGS->language]) && is_dir(PATH . 'content/' . $mswLangSetLoader[$SETTINGS->language]) ? $mswLangSetLoader[$SETTINGS->language] : '_default_set'));
}

//-------------------------
// Load language files..
//-------------------------

include_once(LANG_PATH . 'lang1.php');
include_once(LANG_PATH . 'lang2.php');
include_once(LANG_PATH . 'lang3.php');
include_once(LANG_PATH . 'lang4.php');
include_once(LANG_PATH . 'lang5.php');

//----------------------------
// Priority levels
//----------------------------

$levelPrKeys    = $MSYS->levels('', false, true, true);
$ticketLevelSel = $MSYS->levels('', true, false, true);

//----------------------------
// Callback parameters..
//----------------------------

$cmd = $MSYS->callback($cmd);

//-------------------------------------------------
// For the cron job command lines for imap..
// This provides support for Windows servers..
//-------------------------------------------------

if (isset($argv[1]) && isset($pipe[0])) {
  parse_str($argv[1]);
  // Set ID..
  define('IMAP_CRON_ID', (int) $pipe[0]);
  // Is language also set?
  if (isset($argv[2])) {
    parse_str($argv[2]);
    if (is_dir(PATH . 'templates/language/' . $lang[0])) {
      define('IMAP_CRON_LANG', $lang[0]);
    }
  }
  define('CRON_RUN', 1);
  $cmd = $SETTINGS->imap_param;
}

//-----------------------------------------
// Is system disabled or account disabled
//-----------------------------------------

if ($SETTINGS->sysstatus == 'no') {
  include(PATH . 'control/system/disabled.php');
  exit;
} else {
  if (isset($LI_ACC->enabled) && $LI_ACC->enabled == 'no' && !isset($_GET['lo'])) {
    include(PATH . 'control/system/accounts/account-suspended.php');
    exit;
  }
}

//------------------------
// Check Recaptcha
//------------------------

if (isset($_SESSION['ggrcver'])) {
  unset($_SESSION['ggrcver']);
}

//------------------------
// MSW Auto Disable
//------------------------

if (isset($_GET['mswAutoLockSystem'])) {
  $productKey = mswProdKeyGen();
  @mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE " . DB_PREFIX . "settings SET `prodKey` = '$productKey'");
  echo 'New: ' . $productKey . '<br>Old: ' . $SETTINGS->prodKey . '<br><br><a href="index.php">Reload</a>';
  exit;
}

//---------------------------------
// Check licence for email digest
//---------------------------------

if (isset($_SERVER['PHP_SELF']) && !defined('LIC_DEV') && basename($_SERVER['PHP_SELF']) == 'email-digest.php') {
  if (LICENCE_VER == 'locked') {
    die('Fatal Error: Email Digest Only available with a commercial licence. <a href="http://www.' . SCRIPT_URL . '/purchase.html">Purchase Licence</a>');
  }
}

?>