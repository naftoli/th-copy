<?
$admin_user = array();

$lang = agr($_POST, 'login_lang', agr($_COOKIE, 'lang'));
if(isset($_POST['login_lang'])) setcookie('lang', $lang, time()+90*24*60*60, '/');
require('lang.php');

if (!function_exists('check_auth_admin')) {
	function check_auth_admin($admin_id, $auth) {
		global $admin_user, $admin_auth, $login_message, $lang;
		global $school_and_camp;

		if ($admin_id && !empty($auth)) {
			
			$sql = "SELECT username, password, auth, first, last, lang, camp_id, is_parent, admin_email FROM admins WHERE admin_id=" . $admin_id;
			$result = mysql_query($sql) or die('Query failed 1');
			
			if ($row = mysql_fetch_assoc($result)) {
				// check the hash from the cookie
				$auth_2 = hash_hmac('ripemd128', strtolower($row['username']) . $row['password'], '53fdc95857aac68970159dd07e7c3782'); // nice random seed
				
				if ($auth == hash_hmac('ripemd128', strtolower($row['username']) . $row['password'], '53fdc95857aac68970159dd07e7c3782')) {
					// set the admin_user to the result of the query
					$admin_user['username'] 	= $row['username'];
					$admin_user['admin_id'] 	= $admin_id;
					$admin_user['admin_email'] 	= $row['admin_email'];
					$admin_user['first'] 		= $row['first'];
					$admin_user['last'] 		= $row['last'];
					$admin_user['display'] 		= ($row['first'] ? $row['first'] . ' ' . $row['last'] : $row['username']);
					$admin_user['lang']			= $lang = $row['lang'];
					$admin_user['auth'] 		= $row['auth'];
					$admin_user["is_parent"] 	= $row["is_parent"];
					$admin_user["no_of_children"] = 0;
					
					$allow = $row['auth'] == 'super'; //not super? then start at false, and check below

					// check if user is in list of users allowed to access the page
					foreach($admin_auth as $type) {

						// if plain old users are here and they are a parent then let them in
						if ($type == "user" && $admin_user["is_parent"] == true) {
							$allow = true;
						}
						// get all auths of that user that belong to this auth class	
						$sql = "SELECT id FROM admin_auths WHERE admin_id=" . $admin_id . " AND auth=" . $type;
						// mq defined: db.php#275 -> returns formatted error message if sql statement fails
						// ms defined: db.php#270 -> runs mysql_real_escape_string and adds ' to each side of result
						$admin_user['auths'][$type] = mysql_fetch_column(mq("SELECT id FROM admin_auths WHERE admin_id = $admin_id AND auth = " . ms($type)));
						// if there is a row then the user has this permission class
						if (count($admin_user['auths'][$type]) > 0) {
							$allow = true;
							// get the number of children they have via the number of instances of `auth` for this user in the table
							if ($type == "user") {
								$admin_user["no_of_children"] = count($admin_user['auths'][$type]);
							}
						}
					}
					
					//if ($row['camp_id'] > 0 && count($admin_user['auths']['school']) > 0) {
					//	$school_and_camp = true;
						//header("Location:http://www.mashpia.com/camps/index.php"); 
					//}
					
					if (!$allow) {
						die('Unauthorized. Your account type has no access to this page.');
					}
					
					$admin_user['inst_ids'] = mysql_fetch_column(mq("SELECT inst_id FROM schools JOIN admin_auths ON (schools.school_id = admin_auths.id) WHERE admin_id = $admin_id AND admin_auths.auth = 'school' UNION SELECT inst_id FROM classes JOIN schools USING (school_id) JOIN admin_auths ON (classes.class_id = admin_auths.id) WHERE admin_id = $admin_id AND admin_auths.auth = 'class' UNION SELECT inst_id FROM teams JOIN schools USING (school_id) JOIN admin_auths ON (teams.team_id = admin_auths.id) WHERE admin_id = $admin_id AND admin_auths.auth = 'team' UNION SELECT inst_id FROM users JOIN schools USING (school_id) JOIN admin_auths ON (users.user_id = admin_auths.id) WHERE admin_id = $admin_id AND admin_auths.auth = 'user'"));
					
					// log the user into the helpdesk system as well
					if(!defined("USES_WORDPRESS")){
						require_once($_SERVER["DOCUMENT_ROOT"].'/helpdesk/control/connect.php');
						require_once($_SERVER["DOCUMENT_ROOT"].'/helpdesk/control/functions.php');
						if(mswIsValidEmail($row['admin_email'])) {
							$portal_login_sql = mysql_query("SELECT * FROM tickets.msp_portal WHERE email='".$row['admin_email']."';");
							if(mysql_num_rows($portal_login_sql) > 0){
								$_SESSION[mswEncrypt(SECRET_KEY) . '_msw_support'] = $row['admin_email']; // set the correct key in the session to the users email address
							}
						}
					}
					
					return true;
				}
			}
		}
	  
		$login_message = T_('Please login');
		return false;
	  
	}
}


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
if (!function_exists('check_id_access')) {

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

		if($admin_user['auth'] == 'super') {
			return 'super';
		}

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

		//die('In general you have access to this page, but you must pass in the resource ID requested. Try accessing this page from your home page/main admin page.');
	}
}

if(!function_exists('check_school_setting')) {
function check_school_setting($user_id, $req_setting) {
  $row = mysql_fetch_assoc(mq("SELECT school_id, school_settings FROM users JOIN schools USING (school_id) WHERE user_id = $user_id"));
  $sql = "SELECT school_id, school_settings FROM users JOIN schools USING (school_id) WHERE user_id = $user_id";
  //echo $sql;
  if(!$row || $req_setting !== '' && !in_array($req_setting, explode(',', $row['school_settings'])) && $row['school_id'] != 61) die("This user's school is not configured to allow access to this page.");
}
}

//transitional function
if (!function_exists('assure_id_school')) {
	
	function assure_id_school($name) {
		check_id_access();
	}
}

if (!function_exists('check_login_admin')) 
{

	function check_login_admin() 
	{
		global $login_message, $lang, $_GETPOST;
		
		if (isset($_POST['new_login']) && isset($_POST['login_username']) && isset($_POST['login_password'])) // if the user has posted a new username and password
		{ // log them in
			$username = $_POST['login_username'];
			$password = $_POST['login_password'];			
			// show the user Query Failed 2 if there is a database issue
			$result = mysql_query('SELECT admin_id FROM admins WHERE username = ' . ms($username) . " AND password != '' AND password = " . ms($password)) or die('Query failed 2');
			// get the first username and password (will return false if not)
			if ($row = mysql_fetch_assoc($result)) {
				// hash the usernamepasswod combo
				$auth = hash_hmac('ripemd128', strtolower($username).$password, '53fdc95857aac68970159dd07e7c3782');
				// set the relevent cookies
				setcookie('admin_id', $row['admin_id'], time()+90*24*60*60, '/');
				setcookie('admin_auth', $auth, time()+90*24*60*60, '/');
				setcookie('admin_username_default', $username, time()+90*24*60*60, '/');
				// check if the user is valid (see above for function)
				return check_auth_admin($row['admin_id'], $auth);
			} 
			else { // there is no record with that username and password. log the attempted login
				error_log("Failed login u:$username p:$password\n", 3, '/tmp/.ht_login_errors');
				// clear the cookies
				setcookie('admin_id', '', time() - 86400, '/');
				setcookie('admin_auth', '', time() - 86400, '/');
				// set the message for the user
				// T_ defined in lang.php#29
				$login_message = T_('Login failed');
				//the user has not logged in
				return false;
			}			
		} 
		else 
		{
			//if ( isset($_SESSION['admin_id']) )
			//{
			//	echo "<input type='hidden' name='*** IS SET ***' value='*** IS SET ***'>\n";
				//return check_auth_admin($_SESSION['admin_id'], $_SESSION['admin_auth']);
			//}
			//else
			//{
			//	echo "<input type='hidden' name='IS NOT SET' value='IS NOT SET'>\n";
				return check_auth_admin(agri($_COOKIE, 'admin_id'), agr($_COOKIE, 'admin_auth'));
			//}
		}
		
	}
	
}

if(!isset($no_login)) {
	
	if(!check_login_admin() && !$dual_auth) {  
		include('login.php');
		exit;
	} elseif (!$admin_user['admin_email'] && !isset($_POST['email']) && count($admin_user['auths']['school']) > 0) { // if there is no email and one is not being sent and it is a school account
		include('missing_email.php');
		exit;
	}
}

require('lang.php');
?>
