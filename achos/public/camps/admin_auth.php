<?
$admin_user = array();

	//$lang = agr($_POST, 'login_lang', agr($_COOKIE, 'lang'));
if(isset($_POST['login_lang'])) setcookie('lang', $lang, time()+90*24*60*60, '/');
	//require('lang.php');

	//if(!function_exists('check_auth_admin')) {
function check_auth_admin($admin_id, $auth) {
  global $admin_user, $admin_auth, $login_message, $lang;

  if($admin_id && !empty($auth)) {
	$sql = "SELECT username, password, auth, first, last, lang FROM admins WHERE admin_id = " . $admin_id;
    $result = mysql_query("SELECT username, password, auth, first, last, lang FROM admins WHERE admin_id = $admin_id") or die('Query failed');
    if($row = mysql_fetch_assoc($result)) {
      if($auth == hash_hmac('ripemd128', strtolower($row['username']).$row['password'], '53fdc95857aac68970159dd07e7c3782')) {
        $admin_user['username'] = $row['username'];
        $admin_user['admin_id'] = $admin_id;
        $admin_user['first'] = $row['first'];
        $admin_user['last'] = $row['last'];
        $admin_user['display'] = ($row['first'] ? $row['first'] . ' ' . $row['last'] : $row['username']);
        $admin_user['lang'] = $lang = $row['lang'];
        $admin_user['auth'] = $row['auth'];

//         if($row['auth'] == 'inactive') die('This account is not active, please contact Tzivos Hashem.');
        $allow = $row['auth'] == 'super'; //not super? then start at false, and check below
        foreach($admin_auth as $type) {
          $admin_user['auths'][$type] = mysql_fetch_column(mq("SELECT id FROM admin_auths WHERE admin_id = $admin_id AND auth = " . ms($type)));
          if(count($admin_user['auths'][$type])) $allow = true;
        }
        if(!$allow) die('Unauthorized. Your account type has no access to this page.');
        $admin_user['inst_ids'] = mysql_fetch_column(mq("
SELECT inst_id FROM schools JOIN admin_auths ON (schools.school_id = admin_auths.id) WHERE admin_id = $admin_id AND admin_auths.auth = 'school'
UNION
SELECT inst_id FROM classes JOIN schools USING (school_id) JOIN admin_auths ON (classes.class_id = admin_auths.id) WHERE admin_id = $admin_id AND admin_auths.auth = 'class'
UNION
SELECT inst_id FROM teams JOIN schools USING (school_id) JOIN admin_auths ON (teams.team_id = admin_auths.id) WHERE admin_id = $admin_id AND admin_auths.auth = 'team'
UNION
SELECT inst_id FROM users JOIN schools USING (school_id) JOIN admin_auths ON (users.user_id = admin_auths.id) WHERE admin_id = $admin_id AND admin_auths.auth = 'user'
"));
        return true;
      }
    }
  }
  $login_message = T_('Please login');
  return false;
}
	//}

/*
policy:
Access:
  To access pages via user, team, or class auth, you must pass in the exact ID requested.
  Merely having access to those types of pages is not enough.
  However for school access merely having access is enough, however a specifically passed in ID for one of the others will take priority.

Default:
  No default ID will be set for non-school access.
  For school access, if there is only one, it will be set.
*/
	//if (!function_exists('check_id_access')) {

	function check_id_access($school='school_id', $class='class_id', $team='team_id', $user='user_id') {
		global $admin_auth, $admin_user;

		// only check the passed in ID, and find the highest level access possible
		if(in_array('school', $admin_auth) && in_array(gri($school), $admin_user['auths']['school'])) {
			return 'school';
		}

		if(in_array('class', $admin_auth) && in_array(gri($class), $admin_user['auths']['class'])) {
			$row = mysql_fetch_assoc(mq('SELECT school_id FROM classes WHERE class_id = ' . gri($class)));
			if($row) {
				sgr($school, $row['school_id']);
				return 'class';
			}
		}

		if(in_array('team', $admin_auth) && in_array(gri($team), $admin_user['auths']['team'])) {
			$row = mysql_fetch_assoc(mq('SELECT school_id FROM teams WHERE team_id = ' . gri($team)));
			if($row) {
				sgr($school, $row['school_id']);
				return 'team';
			}
		}

		if(in_array('user', $admin_auth) && in_array(gri($user), $admin_user['auths']['user'])) {
			$row = mysql_fetch_assoc(mq('SELECT school_id, class_id, team_id FROM users WHERE user_id = ' . gri($user)));
			if($row) {
				sgr($school, $row['school_id']);
				sgr($class, $row['class_id']);
				sgr($team, $row['team_id']);
				return 'user';
			}
		}

		if($admin_user['auth'] == 'super') 
			return 'super';

		// the others require the specific ID requested, but school has a menu, and therefor allows you if you have any school access (and none of the others matched)
		if(in_array('school', $admin_auth) && count($admin_user['auths']['school'])) {
			//set a default school if the person only has one defined
			if(count($admin_user['auths']['school']) == 1) { //set default, if only one
				sgr($school, $admin_user['auths']['school'][0]);
			} 
			elseif(!in_array(gri($school), $admin_user['auths']['school'])) { //make sure there is access to the passed in ID
				sgr($school, NULL);
			}
			return 'school';
		}
		//proposal: check each of the others in descending order, and if the person only has one, set it as the default. But if the person has eg. 2 class, and 1 user, user should not be the default. Because of ambiguities this is not implemented. If the pages had menus for the others types, then they could work like school - except if you have both class, and user, you should get both menus, which is not compatible with the design of this function.

		die('In general you have access to this page, but you must pass in the resource ID requested. Try accessing this page from your home page/main admin page.');
	}
	//}

if(!function_exists('check_school_setting')) {
function check_school_setting($user_id, $req_setting) {
  $row = mysql_fetch_assoc(mq("SELECT school_id, school_settings FROM users JOIN schools USING (school_id) WHERE user_id = $user_id"));
  if(!$row || $req_setting !== '' && !in_array($req_setting, explode(',', $row['school_settings']))) die("This user's school is not configured to allow access to this page.");
}
}

//transitional function
if(!function_exists('assure_id_school')) {
function assure_id_school($name) {
  check_id_access();
}
}

if(!function_exists('check_login_admin')) {
function check_login_admin() {
  global $login_message, $lang, $_GETPOST;

  if(isset($_POST['new_login']) && isset($_POST['login_username']) && isset($_POST['login_password'])) {
    $username = $_POST['login_username'];
    $password = $_POST['login_password'];
    setcookie('admin_username_default', $username, time()+90*24*60*60, '/');
    $result = mysql_query('SELECT admin_id FROM admins WHERE username = ' . ms($username) . " AND password != '' AND password = " . ms($password)) or die('Query failed');
    if($row = mysql_fetch_assoc($result)) {
      $auth = hash_hmac('ripemd128', strtolower($username).$password, '53fdc95857aac68970159dd07e7c3782');
      setcookie('admin_id', $row['admin_id'], 0, '/');
      setcookie('admin_auth', $auth, 0, '/');
      return check_auth_admin($row['admin_id'], $auth);
    } else {
      error_log("Failed login u:$username p:$password\n", 3, '/tmp/.ht_login_errors');
      setcookie('admin_id', '', time() - 86400, '/');
      setcookie('admin_auth', '', time() - 86400, '/');
      $login_message = T_('Login failed');
      return false;
    }
  } else {
    //return check_auth_admin(agri($_COOKIE, 'admin_id'), agr($_COOKIE, 'admin_auth'));
  }
}
}

if(!isset($no_login)) {
if(!check_login_admin() && !$dual_auth) {
  include('home.php');
  exit;
}

}

require('lang.php');
?>
