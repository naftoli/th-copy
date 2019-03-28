<?php
class ImportController extends Zend_Controller_Action
{
	private $_user_session_data;
	private $objPermission;

	function init()
	{

	}

	function preDispatch()
	{
		$query = new QueryGen();
		$arrParams = $this->_request->getParams();
		unset($arrParams["controller"], $arrParams["action"], $arrParams["module"]);
		$strParam = preg_replace("/[&=]+/", "/", http_build_query($arrParams));

		// Load thie session array
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		/*
		if (
			!$this->_user_session_data->user_id
			|| !$this->_user_session_data->permission_id
			|| !$this->_user_session_data->permission
			|| !$this->_user_session_data->institution_id
		)
			$this->_redirect('logout/index/' . $strParam);
		$this->objPermission = reset($query->permissions__select(array(
			"user_id" => $this->_user_session_data->user_id,
			"permission_id" => $this->_user_session_data->permission_id,
			"permission" => $this->_user_session_data->permission,
			"institution_id" => $this->_user_session_data->institution_id
		)));
		if (!$this->objPermission)
			$this->_redirect('logout/index/' . $strParam);
		*/
	}

	public function injectclassesAction()
	{
		$objInsertClases = new Classes();
		$objInsertClases->inject_classes();
	}

	public function institutionsAction()
	{
		$objInsertInstitutions = new Classes();
		$objInsertInstitutions->importInstitutions();
		exit;
	}

	public function studentsAction()
	{
		$objInsertInstitutions = new Classes();
		$objInsertInstitutions->importStudents();
		exit;
	}

	public function adminsAction()
	{
		$objInsertInstitutions = new Classes();
		$objInsertInstitutions->importAdmins();
		exit;
	}

	public function parentsAction()
	{
		$objInsertInstitutions = new Classes();
		$objInsertInstitutions->importParents();
		exit;
	}

	public function permissionsAction()
	{
		$objInsertInstitutions = new Classes();
		$objInsertInstitutions->importPermissions();
		exit;
	}

	public function allAction()
	{
		$objInsertInstitutions = new Import();
		/*exit;*/
		//$objInsertInstitutions->importInstitutions();
		//$objInsertInstitutions->importStudents();
		//$objInsertInstitutions->importAdmins();
		//$objInsertInstitutions->importPermissions();
		//$objInsertInstitutions->importClasses();
		//$objInsertInstitutions->importStudentClasses();
		//$objInsertInstitutions->importPartialStudentClasses();
		//$objInsertInstitutions->importUserInfo();
		//$objInsertInstitutions->importUserRanks();
		//$objInsertInstitutions->importUserAddons();
		//$objInsertInstitutions->importParentsChildrenRelationship();
		//$objInsertInstitutions->import_parents();
		exit;
	}

	public function truncateAction()
	{
		$objClass = new Import();
		//exit;
		//$objClass->truncate('users');
		//$objClass->truncate('institutions');
		//$objClass->truncate('permissions');
		//$objClass->truncate('legacy_lookup');
		//$objClass->truncate('classes');
		//$objClass->truncate('user_classes');
		exit;
	}

	public function testAction()
	{
		$form = new Zend_Form();
		$form->setAction("/import/processpost")
				->setMethod("post")
				->addElement('text', 'username')
				->addElement('text', 'password')
				->addElement('submit', 'Submit the stuff...');

		$this->view->form = $form;
	}

	public function processpostAction()
	{
		$form = $this->getForm();
        if (!$form->isValid($_POST)) {
            // Failed validation; redisplay form
            $this->view->form = $form;
            return $this->render('form');
        }
	}

	/**
	 *
	 * This function only imports student pictures
	 *
	 */
	public function imagesAction()
	{
		$import = new Import();
		set_time_limit(86400); // 24 hours
		if(DEV_ENV == "staging" || DEV_ENV == 'devel'){
			$students = $import->getStudents();
		}else{
			print "Sorry, you cannot run this script!";
		}

		exit;

	}
	/**
	 *
	 * This function only imports institution pictures
	 *
	 */
	public function institutionimagesAction()
	{
		$import = new Import();
		set_time_limit(86400); // 24 hours
		if(DEV_ENV == "staging" || DEV_ENV == 'production'){
			$students = $import->getInstitutions();
		}else{
			print "Sorry, you cannot run this script!";
		}

		exit;

	}

	public function getForm()
	{
		return $form;
	}
}
?>