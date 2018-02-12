<?
$user = array();
$lang = agr($_POST, 'login_lang', agr($_COOKIE, 'lang'));
if(isset($_POST['login_lang'])) setcookie('lang', $lang, time()+90*24*60*60, '/');
require('lang.php');
if(!function_exists('check_auth')) {
function check_auth($user_id, $auth) {
  global $user, $login_message, $lang;

  if($user_id && !empty($auth)) {

	$sql = "SELECT username, user_code, school_type_setting, password, first, last, lang, school_type_id, IFNULL(school_id, -1) school_id, school_settings, inst_id, IFNULL(team_id, -1) team_id, IFNULL(class_id, -1) class_id, users.camp_id, IF(user_registered IS NULL, 0, 1) user_registered FROM users LEFT JOIN schools USING (school_id) LEFT JOIN school_types USING (school_type_id) WHERE user_id=" . $user_id;
	$result = mysql_query($sql) or die('Query failed ' . $sql);
    if($row = mysql_fetch_assoc($result)) {
      if($auth == hash_hmac('ripemd128', strtolower($row['username']).$row['password'].$row['user_code'], '2c9e328c5710ded701e7b3bad70eeea6')) {
        $user['username'] = $row['username'];
        $user['settings'] = $row['school_type_setting'];
        $user['user_id'] = $user_id;
        $user['first'] = $row['first'];
        $user['last'] = $row['last'];
        $user['display'] = ($row['first'] ? $row['first'] . ' ' . $row['last'] : $row['username']);
        $user['lang'] = $lang = $row['lang'];
        $user['school_type_id'] = $row['school_type_id'];
        $user['school_id'] = $row['school_id'];
        $user['school_settings'] = explode(',', $row['school_settings']);
        $user['inst_id'] = $row['inst_id'];
        $user['team_id'] = $row['team_id'];
        $user['class_id'] = $row['class_id'];
        $user['registered'] = $row['user_registered'];
				$user['camp_id'] = $row['camp_id'];
        return true;
      }
    }
  }
  $login_message = '';
  return false;
}
}

if(!function_exists('check_login')) {
function check_login() {
  global $login_message, $lang, $_GETPOST;

  if(isset($_POST['new_login']) && (isset($_POST['user_code']) || (isset($_POST['login_username']) && isset($_POST['login_password'])))) {
    if(isset($_POST['user_code'])) {
      $user_code = $_POST['user_code'];
      if(preg_match('/[^0-9]/', $user_code)) {
        $login_message = sprintf(T_('Code %s failed, must contain only numbers.'), $user_code);
        return false;
      } elseif(strlen($user_code) != 20) {
        $login_message = sprintf(T_('Code %s failed, was not 20 digits in length.'), $user_code);
        return false;
      } elseif($user_code[0] != 3) {
        $login_message = sprintf(T_('Code %s failed, does not appear to be a login card. Perhaps it\'s a point card.'), $user_code);
        return false;
      }
      $result = mysql_query('SELECT user_id, user_code, username, password FROM users WHERE user_code = ' . substr($user_code, 1)) or die('Query failed 4');
    } else {
      $username = $_POST['login_username'];
      $password = $_POST['login_password'];
      setcookie('username_default', $username, time()+90*24*60*60, '/');
      $result = mysql_query('SELECT user_id, user_code, username, password FROM users WHERE username = ' . ms($username) . " AND password != '' AND password = " . ms($password)) or die('Query failed 5');
    }
    if($row = mysql_fetch_assoc($result)) {
      $auth = hash_hmac('ripemd128', strtolower($row['username']).$row['password'].$row['user_code'], '2c9e328c5710ded701e7b3bad70eeea6');
      setcookie('user_id', $row['user_id'], 0, '/');
      setcookie('auth', $auth, 0, '/');
      return check_auth($row['user_id'], $auth);
    } else {
      setcookie('user_id', '', time() - 86400, '/');
      setcookie('auth', '', time() - 86400, '/');
      if(isset($user_code)) {
        error_log("Failed login c:$user_code\n", 3, '/tmp/.ht_login_errors');
        $login_message = sprintf(T_('Scan code %s not found.'), $user_code);
      } else {
        error_log("Failed login u:$username p:$password\n", 3, '/tmp/.ht_login_errors');
        $login_message = T_('Login failed');
      }
      return false;
    }
  } else {
    return check_auth(agri($_COOKIE, 'user_id'), agr($_COOKIE, 'auth'));
  }
}
}

if(!isset($no_login)) {
	if(!check_login() && !$dual_auth) {
	  include('login.php');
	  exit;
	}
	/*
	if($user['auth'] == 'super' || $user['auth'] == 'admin') {
	  $auth_become = gri('auth_become');
	  if($auth_become === -1) {
		setcookie('auth_become', '', time() - 86400, '/');
		$auth_become = NULL;
		} elseif(!$auth_become) {
		$auth_become = agri($_COOKIE, 'auth_become');
	  }

	  if($auth_become) {
		$result = mysql_query("SELECT username, first, last, school_type_id, school_id, IFNULL(team_id, -1) team_id FROM users WHERE user_id = $auth_become" . ($user['auth'] != 'super' ? " AND school_id = {$user['school_id']}" : '')) or die('Query failed');
		if($row = mysql_fetch_assoc($result)) {
		  $ruser = $user;
		  setcookie('auth_become', $auth_become, '/');
		  $user['username'] = $row['username'];
		  $user['user_id'] = $auth_become;
		  $user['first'] = $row['first'];
		  $user['last'] = $row['last'];
		  $user['display'] = ($row['first'] ? $row['first'] . ' ' . $row['last'] : $row['username']);
		  $user['school_type_id'] = $row['school_type_id'];
		  $user['school_id'] = $row['school_id'];
		  $user['team_id'] = $row['team_id'];
		}
	  }
	}
	*/
}
require('lang.php');

//users school_type_setting must be ONE of $req_school_type_setting
if(isset($req_school_type_setting) && !in_array($user['settings'], $req_school_type_setting)) die('The Tzivos Hashem type of your school does not allow you to view this page.');
//users school_settings must be ALL of $req_school_settings
if(isset($req_school_settings) && count(array_diff($req_school_settings, $user['school_settings']))) die('The school setting of your school does not allow you to view this page.');
?>
