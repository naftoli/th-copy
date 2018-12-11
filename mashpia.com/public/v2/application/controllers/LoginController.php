<?php
class LoginController extends Zend_Controller_Action
{
    function init()
    {}

    function preDispatch()
    {}

	public function indexAction()
	{
		$query = new QueryGen();

		$strPathExtra = '';
		$strTStyle = $this->_request->getParam("tstyle");
		// Check if the form has been posted
		if ($this->_request->isPost())
		{
			// Collect the data from the form & strip all tags
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			$f = new Zend_Filter_StripTags();
			$email = $f->filter($this->_request->getPost('email'));
			$password = $f->filter($this->_request->getPost('password'));
			$email = trim($email);
			$password = trim($password);
			$email = mysql_real_escape_string($email);
			$password = mysql_real_escape_string($password);

		 	// Validate in case someone is trying to be smart
			if (empty($email))
			{
				print json_encode(array('status'=>'failure','reason'=>'Please enter your email'));
			    exit;
			}
			elseif (empty($password))
			{
				print json_encode(array('status'=>'failure','reason'=>'Please enter your password'));
			    exit;
			}

			// Authenticate the user
			$Users = new Users();
			$authenticate = $Users->Authenticate($email, $password, false, $strTStyle);

			if ($authenticate > 0)
				$user_session = new Zend_Session_Namespace('user_session_data');
			if ($authenticate == 1)
			{
				if ($email == 'admin@mashpia.com')
					$strTStyle = '';
				$objPermission = first($query->permissions__select(array(
					"user_id" => $user_session->user_id,
					"template_style" => $strTStyle
				)));
				if (!$objPermission)
				{
					print json_encode(array(
						'status'=>'failure',
						'reason'=>'Access not available.'
					));
					exit;
				}
			}
			if (isset($user_session) && $user_session->template_style)
			{
				$strPathExtra .= "/tstyle/" . $user_session->template_style;
			}
			else if ($strTStyle)
			{
				$strPathExtra .= "/tstyle/" . $strTStyle;
				if (isset($user_session))
					$user_session->template_style = $strTStyle;
			}
			if (is_int($authenticate))
			{
				switch ($authenticate)
				{
					case 0: 	print json_encode(array('status'=>'failure','reason'=>'Authentication error'));							break;
					case -1: 	print json_encode(array('status'=>'failure','reason'=>'Invalid email address'));						break;
					case -3: 	print json_encode(array('status'=>'failure','reason'=>'Invalid password'));								break;
					case -4: 	print json_encode(array('status'=>'failure','reason'=>'You are not registered here.'));								break;
					case -999:	print json_encode(array('status'=>'failure','reason'=>'Inactive user'));								break;
					case 1:		print json_encode(array('status'=>'success','reason'=>'Authenticated... Redirecting','redirect'=>1,'url'=>$this->getRequest()->getBaseUrl().'/dashboard/index' . $strPathExtra));	break;
					default:	print json_encode(array('status'=>'failure','reason'=>'Authentication error'));							break;
				}
				exit;
			}
			else
			{
				print json_encode(array('status'=>'failure','reason'=>'Authentication error'));
			    exit;
			}
		}
		$this->_redirect('logout/index' . $strPathExtra);
	}

	public function switchAction()
	{
		// With this function - A user who has access to multiple accounts can switch from one to the other
		$institution_id = $this->_request->getParam('institution_id');
		$user_id = $this->_request->getParam('user_id');

		// Get the user's permission which will validate whether they have access to this institution_id or not
		$User = new Users();
		$user_details = $User->get_user_permission_by_institution_id($user_id,$institution_id);

		if ($user_details)
		{
			// create a new zend session
			$user_session = new Zend_Session_Namespace('user_session_data');

			// Reset their institution, network, and host id's + permission
			$user_session->institution_id = $user_details->institution_id;
			$user_session->permission = $user_details->permission;
		}
		$this->_redirect('dashboard');
	}


	/**
	 * This login routine is used temporarily and logs in administrative users
	 * from the old system at www.mashpia.com. Param passed to validate is
	 * users.old_user_id
	 * */
	public function frommashpiaAction()
	{
		$user_session = new Zend_Session_Namespace('user_session_data');
		$user_session->referer_url = @$_SERVER["HTTP_REFERER"];
		$user_session->frommashpia = true;
		$user_session->template_style = 'schoolstemplate';
		$user_session->institution_id = $this->_request->getParam('school_id');
		$user_session->user_id = $this->_request->getParam('admin_id');
		$user_session->permission = "Institution Administrator";
		if ($this->_request->getParam('class_id')) {
			$user_session->permission = "Teacher";
			$user_session->class_id = $this->_request->getParam('class_id');
			$user_session->achievement = $this->_request->getParam('achievement');
			$user_session->store = $this->_request->getParam('store');
		}
		$user_session->setExpirationSeconds(36000); // 10 hours
		$this->_redirect('dashboard/index/tstyle/schoolstemplate1');
		/*		
		$boolDebug = intval($this->_request->getParam('debug'));
		$strInstitutionType = $this->_request->getParam('instituiton_type');
		$intLegacyInstitutionId = $this->_request->getParam('institution_id');
		$intUser = intval($this->_request->getParam('user_id'));
		if (!$intUser)
		{
			print text("Sorry, there was an error") . ": CL-FM101-3FIDIS";
			exit;
		}
		
		$objUsers = new Users();
		$objLegacy = new Legacy();
		$objPermissions = new Permissions();

		$objAdmin = $objLegacy->import_admin(array(
			"user_id" => $intUser
		));
		$intPermission = false;

		if ($intLegacyInstitutionId)
		{
			$objSchool = $objLegacy->import_school(array(
				"legacy_school_id" => $intLegacyInstitutionId
			));
			$objPermission = first($objPermissions->_permissions_select(array(
				"permission" => $strInstitutionType,
				"institution_id" => $objSchool->institution_id
			)));
			if ($objPermission)
			{
				$intPermission = $objPermission->permission_id;
			}
		} else if ($strInstitutionType)
		{
			$objPermission = first($objPermissions->_permissions_select(array(
				"permission" => $strInstitutionType,
				"user_id" => $objAdmin->user_id
			)));
			if ($objPermission)
			{
				$intPermission = $objPermission->permission_id;
			}
		}
		$intResult = $objUsers->Authenticate($objAdmin->email, MASTER_PASSWORD_X32G0SS8P, $intPermission);
		if ($intResult > 0)
		{
			$strDashExtra = "";
			if ($this->_request->getParam("dashextra"))
				$strDashExtra .= base64_decode($this->_request->getParam("dashextra"));
			$user_session = new Zend_Session_Namespace('user_session_data');
			$user_session->referer_url = @$_SERVER["HTTP_REFERER"];
			$user_session->frommashpia = true;
			if ($strDashExtra == "/index/tstyle/schoolstemplate1")
				$user_session->template_style = 'schoolstemplate';
			$this->_redirect('dashboard' . $strDashExtra);
		}
		else
		{
			print $intResult;
		}
		exit;
		*/
	}

	public function dummyLoginAction()
	{

		$type = $this->_request->getParam('type');

		switch($type){
				case "super":
						$user_session = new Zend_Session_Namespace('user_session_data');
						$user_session->setExpirationSeconds(9999999999);
						$user_session->user_id = 2;
						$user_session->is_user_active = 1;
						$user_session->institution_id = 1;
						$user_session->permission = "Super Administrator";
						$user_session->full_name = "Andy Dear";
						$user_session->institution_name = "IMS";
						//$this->_redirect("/dashboard");
						break;
				case "host":
						$user_session = new Zend_Session_Namespace('user_session_data');
						$user_session->setExpirationSeconds(9999999999);
						$user_session->user_id = 9;
						$user_session->is_user_active = 1;
						$user_session->institution_id = 1;
						$user_session->permission = "Host Administrator";
						$user_session->full_name = "George Calder";
						$user_session->institution_name = "IMS";
						//$this->_redirect("/dashboard");
						break;
				case "network":
						$user_session = new Zend_Session_Namespace('user_session_data');
						$user_session->setExpirationSeconds(9999999999);
						$user_session->user_id = 2;
						$user_session->is_user_active = 1;
						$user_session->institution_id = 3;
						$user_session->permission = "Network Administrator";
						$user_session->full_name = "Johnny Networkadmin";
						$user_session->institution_name = "IMS 1 network";
						//$this->_redirect("/dashboard");
						break;
				case "institution":
						$user_session = new Zend_Session_Namespace('user_session_data');
						$user_session->setExpirationSeconds(9999999999);
						$user_session->user_id = 2;
						$user_session->is_user_active = 1;
						$user_session->institution_id = 1;
						$user_session->permission = "Institution Administrator";
						$user_session->full_name = "Mike Schooluser";
						$user_session->institution_name = "Network 2 school";
						//$this->_redirect("/dashboard");
						break;
				case "parent":
						$user_session = new Zend_Session_Namespace('user_session_data');
						$user_session->setExpirationSeconds(9999999999);
						$user_session->user_id = 2;
						$user_session->is_user_active = 1;
						$user_session->institution_id = 1;
						$user_session->permission = "Parent";
						$user_session->full_name = "Tom Parent";
						$user_session->institution_name = "IMS";
						//$this->_redirect("/dashboard");
						break;
				case "teacher":
						$user_session = new Zend_Session_Namespace('user_session_data');
						$user_session->setExpirationSeconds(9999999999);
						$user_session->user_id = 2;
						$user_session->is_user_active = 1;
						$user_session->institution_id = 1;
						$user_session->permission = "Teacher";
						$user_session->full_name = "Bob Teacher";
						$user_session->institution_name = "IMS";
						//$this->_redirect("/dashboard");
						break;
				default;
		}

		$this->view->msg = '
						<b>Usage:</b><br>
						<a href="/login/dummy-login/type/super">Super Administrator</a><br>
						<a href="/login/dummy-login/type/host">Host Administrator</a><br>
						<a href="/login/dummy-login/type/institution">Institution Administrator</a><br>
						<a href="/login/dummy-login/type/network">Network Administrator</a><br>
						<a href="/login/dummy-login/type/parent">Parent</a><br>
						<a href="/login/dummy-login/type/teacher">Teacher</a><br>
						';
	}

	public function autoAction()
	{
		$user = new Users();
		$old_user_id = $this->_request->getParam("user_id");
		$objUser = $user->get_new_user_id_and_email($old_user_id);

		$user_id = $objUser->user_id;
		$email = $objUser->email;
		$password = $this->_request->getParam("password");

		$authentic = $user->AuthenticateMD5($email, $password);

		if ($authentic == 1)
		{
			// create a new zend session
			$user_session = new Zend_Session_Namespace('user_session_data');
			$user_session->setExpirationSeconds(9999999999);
			$user_session->is_user_active = 1;
			$user_session->user_id = $user_id;
			$user_session->full_name = $user->get_user_full_name($user_id);

			$this->_redirect('dashboard');
		}

		// Get the default user permission
		//$objPermission = $user->get_user_permission($user_id);
		//$user_session->permission = $objPermission->permission;
		//$user_session->institution_id = $objPermission->institution_id;

		//$this->_redirect('dashboard');
	}

	public function multiaccountsAction()
	{
		$permission_id = $this->_request->permission_id;

		if(!isset($permission_id)){
			echo "There was an error logging in, please try again."; //error
			exit;
		}

		$objUser = new Users();
		$result = $objUser->reset_user_session($permission_id);
		echo 1;
		exit;
	}
}
?>
