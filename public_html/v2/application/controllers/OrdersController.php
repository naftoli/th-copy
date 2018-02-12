<?php
class OrdersController extends Zend_Controller_Action
{
	private $_user_session_data;
	public $_verbose;

	function init()
	{
		$this->_verbose = $this->_request->getParam("verbose");
	}

	function preDispatch()
	{
		$query = new QueryGen();
		$arrParams = $this->_request->getParams();
		unset($arrParams["controller"], $arrParams["action"], $arrParams["module"]);
		$strParam = preg_replace("/[&=]+/", "/", http_build_query($arrParams));

		// Load thie session array
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		if (!$this->_user_session_data->institution_id) {
			$this->_redirect('logout');
		}
		/*
		if (
			!$this->_user_session_data->user_id
			|| !$this->_user_session_data->permission_id
			|| !$this->_user_session_data->permission
			|| !$this->_user_session_data->institution_id
		)
			$this->_redirect('logout/index/' . $strParam);
		$this->objPermission = first($query->permissions__select(array(
			"user_id" => $this->_user_session_data->user_id,
			"permission_id" => $this->_user_session_data->permission_id,
			"permission" => $this->_user_session_data->permission,
			"institution_id" => $this->_user_session_data->institution_id
		)));
		if (!$this->objPermission)
			$this->_redirect('logout/index/' . $strParam);
		*/
	}

	public function indexAction()
	{}

	public function ordershistoryAction()
	{
		$query = new QueryGen();
		$objOrders = new Orders();
		$objPrizes = new Store();
		$objUsers = new Users();
		$objRoles = new Roles();
		$objClasses = new Classes();

		$arrOrders = array();
		$arrPrizeHash = array();
		$arrUserHash = array();

		if ($this->_request->isPost())
		{
			$boolRedeem = $this->_request->getParam("redeem");
			$arrPost = $this->_request->getPost();
			$arrUserPrizeIds = array();
			foreach ($arrPost as $intKey => $boolValue)
			{
				if (preg_match("/^[0-9]+$/", $intKey))
				{
					$arrUserPrizeIds[] = $intKey;
				}
			}
			if (!count($arrUserPrizeIds))
			{
				print 1;
				exit;
			}
			foreach ($arrUserPrizeIds as $intPrize)
			{
				$query->user_prizes__update(array(
					"where" => array(
						"user_prize_id" => $intPrize
					),
					"values" => array(
						"status" => "Checked Out"
					)
				));
			}
			print 1;
			exit;
		}

		$this->view->class_id = $intClass = intval($this->_request->getParam("class_id"));
		if ($intClass > 0 || $objRoles->isRole("Teacher"))
		{
			/*
			$arrUserClassParams = array(
				"class_id" => $intClass,
				"class_role" => "Student"
			);
			if ($intClass < 1)
			{
				$objTeacherClass = $query->user_classes__select(array(
					"user_id" => $this->_user_session_data->user_id,
					"class_role" => "Teacher"
				));
				$arrUserClassParams["class_id"] = array_keys(array_stack("class_id", $objTeacherClass));
			}
			else
				$arrUserClassParams["class_id"] = $intClass;
			$arrStudentClasses = $query->user_classes__select($arrUserClassParams);
			*/
			$arrStudentClasses = $objClasses->getMashpiaUsers(array($intClass));
			$arrUserIds = array_keys(array_stack("user_id", $arrStudentClasses));
		}
		$arrSql = array(
			"institution_id" => $this->_user_session_data->institution_id
		);
		if (isset($arrUserIds))
			$arrSql["user_id"] = $arrUserIds;
		$arrSql["resource_name"] = 'store';
		$arrUserPoints = array_hash('user_prize_id', $query->user_points__select($arrSql));
		$arrUserPrizes = array_hash('user_prize_id', $query->user_prizes__select(array(
			'user_prize_id' => first(array_extract2('user_prize_id', $arrUserPoints))
		)));
		
		/*
		$arrUserClasses = array_hash('user_id', $query->user_classes__select(array(
			'user_id' => array_stack('user_id', $arrUserPrizes)
		)));
		$arrClasses = array_hash('class_id', $objClasses->_classes_select(array(
			'class_id' => array_stack('class_id', $arrUserClasses)
		)));
		$this->view->arrClasses = array_hash('class_hierarchy', $arrClasses);
		//dumper($arrClasses,1,1);
		*/
		
		$arrUsers = array_hash('user_id', $objUsers->getMashpiaUserById(
			array_stack('user_id', $arrUserPrizes)
		));
		
		$arrUserClasses = array_bubble_hash('user_id', $objClasses->getMashpiaClasses(
			array_stack('user_id', $arrUserPrizes)
		));
		
		$arrClasses = array_hash('class_id', $objClasses->_classes_select(array(
			'class_id' => first(array_extract2('class_id', $arrUserClasses)),
			'institution_id' => $this->_user_session_data->institution_id
		)));
		
		$this->view->arrClasses = array_hash('class_id', $arrClasses);
		
		foreach ($arrUserPoints as $intKey => $objUserPoint)
		{
			$objUserPrize = $arrUserPrizes[$objUserPoint->user_prize_id];
			if (!isset($arrPrizeHash[$objUserPrize->prize_id]))
				$arrPrizeHash[$objUserPrize->prize_id] = first($query->prize__select(array(
					"prize_id" => $objUserPrize->prize_id
				)));
			if (!isset($arrUserHash[$objUserPrize->user_id]))
				$arrUserHash[$objUserPrize->user_id] = first($objUsers->getMashpiaUserById(array(
					$objUserPrize->user_id
				)));
			if (
				!$arrPrizeHash[$objUserPrize->prize_id]
				|| !$arrUserHash[$objUserPrize->user_id]
			)
				continue;
			//$objClass = $arrClasses[$arrUserClasses[$objUserPrize->user_id]->class_id];
			$arrOrders[$objUserPrize->status][$objUserPrize->modified . ":" . $arrUserHash[$objUserPrize->user_id]->last. ":" . $arrUserHash[$objUserPrize->user_id]->first . ":" . $intKey] = array(
				"objUser" => $arrUserHash[$objUserPrize->user_id],
				"objPrize" => $arrPrizeHash[$objUserPrize->prize_id],
				"objUserPrize" => $objUserPrize,
				"objUserPoint" => $objUserPoint
			);
			krsort($arrOrders[$objUserPrize->status]);
		}
		$this->view->arrOrders = $arrOrders;
	}

	public function ordersonlineAction()
	{
		$query = new QueryGen();
		$objOrders = new Orders();
		$objPrizes = new Store();
		$objUsers = new Users();
		$objRoles = new Roles();
		$objClasses = new Classes();

		$arrOrders = array();
		$arrPrizeHash = array();

		$arrGet = $this->_request->getParams();
		if ($this->_request->isPost())
		{
			if (@$arrGet['order'] == 'reverse')
			{
				$arrPost = $this->_request->getPost();
				$arrUserPrizeIds = array();
				foreach ($arrPost as $intKey => $boolValue)
				{
					if (preg_match("/^[0-9]+$/", $intKey))
					{
						$arrUserPrizeIds[] = $intKey;
					}
				}
				if (!count($arrUserPrizeIds))
				{
					print 1;
					exit;
				}
				$arrUserPrizes = $query->user_prizes__select(array(
					'user_prize_id' => $arrUserPrizeIds
				));
				foreach ($arrUserPrizes as $objUserPrize)
				{
					$objUserPoint = first($query->user_points__select(array(
						'user_prize_id' => $objUserPrize->user_prize_id
					)));
					if (!$objUserPoint)
						continue;
					$query->user_points__insert(array(
						'reversed_user_point_id' => $objUserPoint->user_point_id,
						'prize_id' => $objUserPrize->prize_id,
						'user_prize_id' => $objUserPrize->user_prize_id,
						'user_id' => $objUserPrize->user_id,
						'institution_id' => $objUserPrize->institution_id,
						'points' => -$objUserPoint->points,
						'resource_name' => 'transaction_manager_store',
						'description' => @$arrPost['reverse_order_reason']
					));
					$query->user_prizes__update(array(
						'where' => array(
							'user_prize_id' => $objUserPrize->user_prize_id
						),
						'values' => array(
							'is_reversed' => 1
						)
					));
					// add back to stock
					$data = array(
						'prize_count' => new Zend_Db_Expr('prize_count + ' . $objUserPrize->quantity)
					);
					$where = 'prize_id = ' . $objUserPrize->prize_id;
					$db = Zend_Registry::get('db');
					$db->update('prizes', $data, $where);
				}
				print 1;
				exit;
			}
			else if (@$arrGet['order'] == 'redeem')
			{
				$arrPost = $this->_request->getPost();
				$arrUserPrizeIds = array();
				foreach ($arrPost as $intKey => $boolValue)
				{
					if (preg_match("/^[0-9]+$/", $intKey))
					{
						$arrUserPrizeIds[] = $intKey;
					}
				}
				if (!count($arrUserPrizeIds))
				{
					print 1;
					exit;
				}

				$objPrizes->_user_prizes_update(array(
					"where" => array(
						"user_prize_id" => $arrUserPrizeIds
					),
					"values" => array(
						"status" => "Redeemed"
					)
				));
				print 1;
				exit;
			}
		}

		$this->view->class_id = $intClass = intval($this->_request->getParam("class_id"));
		$arrUserClassParams = array();
		if ($intClass > 0 || $objRoles->isRole("Teacher"))
		{
			/*
			$arrUserClassParams = array(
				"class_id" => $intClass,
				"class_role" => "Student"
			);
			if ($intClass < 1)
			{
				$objTeacherClass = $query->user_classes__select(array(
					"user_id" => $this->_user_session_data->user_id,
					"class_role" => "Teacher"
				));
				$arrUserClassParams["class_id"] = array_keys(array_stack("class_id", $objTeacherClass));
			}
			$arrStudentClasses = $query->user_classes__select($arrUserClassParams);
			*/
			$arrStudentClasses = $objClasses->getMashpiaUsers(array($intClass));
			$arrUserIds = array_keys(array_stack("user_id", $arrStudentClasses));
		}
		$arrSql = array(
			"institution_id" => $this->_user_session_data->institution_id
		);
		if (isset($arrUserIds))
			$arrSql["user_id"] = $arrUserIds;
		$arrSql["resource_name"] = 'store';
		
		$arrUserPoints = array_hash('user_prize_id', $query->user_points__select($arrSql));
		
		$arrUserPrizes = array_hash('user_prize_id', $query->user_prizes__select(array(
			'user_prize_id' => first(array_extract2('user_prize_id', $arrUserPoints)),
			'is_reversed' => 0
		)));
		
		$arrPrizes = array_hash('prize_id', $query->prize__select(array(
			'prize_id' => array_stack('prize_id', $arrUserPrizes)
		)));
		
		/*
		$arrUsers = array_hash('user_id', $query->users__select(array(
			'user_id' => array_stack('user_id', $arrUserPrizes)
		)));
		$arrUserClasses = array_bubble_hash('user_id', $query->user_classes__select(array(
			'user_id' => array_stack('user_id', $arrUserPrizes)
		)));
		$arrClasses = array_hash('class_id', $objClasses->_classes_select(array(
			'class_id' => first(array_extract2('class_id', $arrUserClasses)),
			'institution_id' => $this->_user_session_data->institution_id
		)));
		$this->view->arrClasses = array_hash('class_hierarchy', $arrClasses);
		*/
		
		$arrUsers = array_hash('user_id', $objUsers->getMashpiaUserById(
			array_stack('user_id', $arrUserPrizes)
		));
		
		$arrUserClasses = array_bubble_hash('user_id', $objClasses->getMashpiaClasses(
			array_stack('user_id', $arrUserPrizes)
		));
		$arrClasses = array_hash('class_id', $objClasses->_classes_select(array(
			'class_id' => first(array_extract2('class_id', $arrUserClasses)),
			'institution_id' => $this->_user_session_data->institution_id
		)));
		$this->view->arrClasses = array_hash('class_id', $arrClasses);
		
		foreach ($arrUserPoints as $intKey => $objUserPoint)
		{
			if (!isset($arrUserPrizes[$objUserPoint->user_prize_id]))
				continue;
			$objUserPrize = $arrUserPrizes[$objUserPoint->user_prize_id];
			if (!isset($arrUserClasses[$objUserPrize->user_id]))
				continue;
			foreach ($arrUserClasses[$objUserPrize->user_id] as $objUserClass)
			{
				if (!isset($arrClasses[$objUserClass->class_id]))
					continue;
				$objClass = $arrClasses[$objUserClass->class_id];
				if (
					(
						isset($arrUserClassParams['class_id'])
						&& is_array($arrUserClassParams['class_id'])
						&& !in_array($objClass->class_id, $arrUserClassParams['class_id'])
					) || (
						isset($arrUserClassParams['class_id'])
						&& $objClass->class_id!=$arrUserClassParams['class_id']
					)
				)
						continue;
				if (!isset($arrPrizes[$objUserPrize->prize_id]))
				{
					// seems prizes must have been deleted at one point
					// in time so this we must skip over these occurances
					continue;
				}
				$arrOrders[$objUserPrize->status][$objClass->class_id][$arrUsers[$objUserPrize->user_id]->last . ":" . $arrUsers[$objUserPrize->user_id]->first . ":" . $intKey] = array(
					"objUser" => $arrUsers[$objUserPrize->user_id],
					"objPrize" => $arrPrizes[$objUserPrize->prize_id],
					"objUserPrize" => $objUserPrize,
					"objUserPoint" => $objUserPoint
				);
			}
		}
		foreach ($arrOrders as $strStatus => $arrStatus)
		{
			ksort($arrOrders[$strStatus]);
			foreach ($arrStatus as $intHierarchy => $arrHierarchy)
			{
				ksort($arrOrders[$strStatus][$intHierarchy]);
			}
		}
		$this->view->arrOrders = $arrOrders;
	}

	public function orderseditorAction()
	{
		$query = new QueryGen();
		$objOrders = new Orders();
		$objPrizes = new Store();
		$objUsers = new Users();
		$objRoles = new Roles();
		$objClasses = new Classes();

		$arrOrders = array();
		$arrPrizeHash = array();
		$arrUserHash = array();

		if ($this->_request->isPost())
		{
			$boolRedeem = $this->_request->getParam("redeem");
			$arrPost = $this->_request->getPost();
			$arrUserPrizeIds = array();
			foreach ($arrPost as $intKey => $boolValue)
			{
				if (preg_match("/^[0-9]+$/", $intKey))
				{
					$arrUserPrizeIds[] = $intKey;
				}
			}
			if (!count($arrUserPrizeIds))
			{
				print 1;
				exit;
			}

			$objPrizes->_user_prizes_update(array(
				"where" => array(
					"user_prize_id" => $arrUserPrizeIds
				),
				"values" => array(
					"status" => "Redeemed"
				)
			));
			print 1;
			exit;
		}

		$intClass = intval($this->_request->getParam("class_id"));
		if ($intClass > 0 || $objRoles->isRole("Teacher"))
		{
			/*
			$arrUserClassParams = array(
				"class_id" => $intClass,
				"class_role" => "Student"
			);
			if ($intClass < 1)
			{
				$objTeacherClass = $query->user_classes__select(array(
					"user_id" => $this->_user_session_data->user_id,
					"class_role" => "Teacher"
				));
				$arrUserClassParams["class_id"] = array_keys(array_stack("class_id", $objTeacherClass));
			}
			$arrStudentClasses = $query->user_classes__select($arrUserClassParams);
			$arrUserIds = array_keys(array_stack("user_id", $arrStudentClasses));
			*/
			$arrStudentClasses = $objClasses->getMashpiaUsers(array($intClass));
			$arrUserIds = array_keys(array_stack("user_id", $arrStudentClasses));
		}
		$arrSql = array(
			"institution_id" => $this->_user_session_data->institution_id
		);
		if (isset($arrUserIds))
			$arrSql["user_id"] = $arrUserIds;
		$arrUserPrizes = $query->user_prizes__select($arrSql);
		foreach ($arrUserPrizes as $intKey => $objUserPrize)
		{
			if (!isset($arrPrizeHash[$objUserPrize->prize_id]))
				$arrPrizeHash[$objUserPrize->prize_id] = first($query->prize__select(array(
					"prize_id" => $objUserPrize->prize_id
				)));
			if (!isset($arrUserHash[$objUserPrize->user_id]))
				$arrUserHash[$objUserPrize->user_id] = first($query->users__select(array(
					"user_id" => $objUserPrize->user_id
				)));
			if (
				!$arrPrizeHash[$objUserPrize->prize_id]
				|| !$arrUserHash[$objUserPrize->user_id]
			)
				continue;
			$arrOrders[$objUserPrize->status][$arrUserHash[$objUserPrize->user_id]->last_name . ":" . $arrUserHash[$objUserPrize->user_id]->first_name . ":" . $intKey] = array(
				"objUser" => $arrUserHash[$objUserPrize->user_id],
				"objPrize" => $arrPrizeHash[$objUserPrize->prize_id],
				"objUserPrize" => $objUserPrize
			);
			ksort($arrOrders[$objUserPrize->status]);
		}

		$this->view->arrOrders = $arrOrders;
	}

	public function ordersprintvouchersAction()
	{
		$query = new QueryGen();

		$arrVouchers = array();
		$arrPost = $this->_request->getPost();

		$objOrders = new Orders();
		$objPrizes = new Store();
		$objUsers = new Users();
		$objInstitutions = new Institutions();

		$arrPrizeHash = array();
		$arrUserHash = array();
		$arrInstitutionHash = array();
		$arrPrintedItems = array();

		$arrUserPrizeIds = array();
		foreach ($this->_request->getPost() as $intKey => $intValue)
		{
			if (is_int($intKey) && $intValue != 0)
				$arrUserPrizeIds[] = $intKey;
		}

		if (!count($arrUserPrizeIds))
		{
			print text("Sorry, there was an error") . ": CO-OPV101-SD8F7D";
			exit;
		}

		$arrUserPrizes = $objPrizes->_user_prizes_select(array(
			"user_prize_id" => $arrUserPrizeIds
		));
		foreach ($arrUserPrizes as $objUserPrize)
		{
			if (!isset($arrPrizeHash[$objUserPrize->prize_id]))
				$arrPrizeHash[$objUserPrize->prize_id] = first($query->prize__select(array(
					"prize_id" => $objUserPrize->prize_id
				)));
			if (!isset($arrUserHash[$objUserPrize->user_id]))
				$arrUserHash[$objUserPrize->user_id] = first($query->users__select(array(
					"user_id" => $objUserPrize->user_id
				)));
			if (!isset($arrUserHash[$objUserPrize->institution_id]))
				$arrUserHash[$objUserPrize->institution_id] = first($query->institutions__select(array(
					"institution_id" => $objUserPrize->institution_id
				)));
			if (
				!$arrPrizeHash[$objUserPrize->prize_id]
				|| !$arrUserHash[$objUserPrize->institution_id]
				|| !$arrUserHash[$objUserPrize->user_id]
			)
				continue;
			$arrVouchers[$objUserPrize->status][] = array(
				"objUser" => $arrUserHash[$objUserPrize->user_id],
				"objPrize" => $arrPrizeHash[$objUserPrize->prize_id],
				"objUserPrize" => $objUserPrize,
				"objInstitution" => $arrUserHash[$objUserPrize->institution_id]
			);
			$arrPrintedItems[] = $objUserPrize->user_prize_id;
		}
		if (count($arrPrintedItems))
		{
			$objPrizes->_user_prizes_update(array(
				"where" => array(
					"user_prize_id" => $arrUserPrizeIds
				),
				"values" => array(
					"status" => "Printed"
				)
			));
		}
		$this->view->arrVouchers = $arrVouchers;
	}


	public function validateOrderAction()
	{
		$user_session = new Zend_Session_Namespace('user_session_data');
		$kiosk = new Kiosk();
		$serial = trim($this->_request->getParam("serial"));

		//validate user input
		if(!$serial || !is_numeric($serial)){
			 $this->view->msg = "You didn't enter a valid serial number";
			 return;
		}

		//validate order
		try
		{
			 $result = $kiosk->validateOrder($serial);
		}
		catch (Zend_Exception $e)
		{
			 $this->view->msg = $e->getMessage();
			 return;
		}

		if($result == 0	){
			 $this->view->msg = "This item doesn't exist on our system or it has been already updated.";
		}
		else{
			 $this->view->msg = "Your order has been updated successfully.";
		}
	}

	public function showOrderAction()
	{
		$user_session = new Zend_Session_Namespace('user_session_data');
		$kiosk = new Kiosk();
		$serial = trim($this->_request->getParam("serial"));

		//validate user input
		if(!$serial || !is_numeric($serial)){
			 $this->view->msg = "You didn't enter a valid serial number";
			 return;
		}

		//validate order
		try
		{
			 $result = $kiosk->getOrder($serial);
		}
		catch (Zend_Exception $e)
		{
			 $this->view->msg = $e->getMessage();
			 return;
		}

		if(!$result){
			 $this->view->msg = "This item doesn't exist on our system.";
			 $this->view->row = $result;
			 $this->view->serial = '';
		}
		else{
			 $this->view->msg = "";
			 $this->view->row = $result;
			 $this->view->serial = $serial;
		}
	}

    public function ordershostsAction()
    {
		$objInstitutions = new Institutions();
		$this->view->objHosts = $objInstitutions->get_all_active_hosts();
    }

    public function ordersnetworksAction()
    {
		$this->view->intHost = $this->_request->getParam("host_id");

		$objInstitutions = new Institutions();
		$this->view->objNetworks =
			$objMission =
				$objInstitutions->_institutions_select(
					array(
						"institution_type" => "Network",
						"host_id" => $this->view->intHost,
						"is_active" => 1
					)
				);
    }

    public function ordersinstitutionsAction()
    {
		$this->view->intHost = $this->_request->getParam("host_id");
		$this->view->intNetwork = $this->_request->getParam("network_id");

		$objInstitutions = new Kiosk();
		$this->view->objInstitutions =
			$objMission =
				$objInstitutions->orders_select_institutions($this->view->intHost, $this->view->intNetwork);
    }

	public function ordersclasslistAction()
	{

		$host_id = $network_id = $institution_id = $intInstitution = 0;

		if(isset($this->_request->institution_id)){
		   $this->view->institution_id = $intInstitution = $institution_id = $this->_request->getParam("institution_id");
		}elseif(isset($this->_request->network_id)){
		   $this->view->network_id = $intInstitution = $network_id = $this->_request->getParam("network_id");
		}elseif(isset($this->_request->host_id)){
		   $this->view->host_id = $intInstitution = $host_id = $this->_request->getParam("host_id");
		}

		$objClass = new Classes();

        $this->view->arrClasses = $objClass->classes_select(array('institution_id' =>$intInstitution));
	}

	public function ordersuserslistAction()
	{
		$this->view->objRoles = new Roles();
		$this->view->is_active = ($this->_request->getParam("status") == "active") ? 1 : 0;
		$this->view->user_type = $this->_request->getParam("usertype");
		$this->view->host_id = $this->_request->getParam("host_id");
		$this->view->network_id = $this->_request->getParam("network_id");
		$this->view->institution_id = $this->_request->getParam("institution_id");
		$this->view->class_id = $this->_request->getParam("class_id");
		$this->view->user_id = $this->_request->getParam("id");

		// Validate user type
		$arrUserType = array(
			"super" => "Super Administrator",
			"host" => "Host Administrator",
			"network" => "Network Administrator",
			"institution" => "Institution Administrator",
			"teachers" => "Teacher",
			"students" => "Student"
		);
		if (isset($arrUserType[$this->view->user_type]))
		{
			$this->view->user_type = $arrUserType[$this->view->user_type];
		}
		else
		{
			$this->view->user_type = "";
		}

		$objUsers = new Users();
		$this->view->arrUsers = $objUsers->_users_select_hierarchal(
			array(
				"host_id" => $this->view->host_id,
				"network_id" => $this->view->network_id,
				"institution_id" => $this->view->institution_id,
				"class_id" => $this->view->class_id,
				"permission" => $this->view->user_type
			)
		);

	}

	public function orderslistAction()
	{
		$kiosk = new Kiosk();
        $objClasses = new Classes();
		$mode = $this->_request->getParam("mode");
		if(isset($this->_request->student_id)){
			// Get all orders for the student
			$this->view->arrOrders = $kiosk->get_orders_by_user_id($this->_request->student_id);
		}elseif(isset($this->_request->class_id)){
			// Get all orders for the class
			$this->view->arrOrders = $kiosk->orders_select_by_class_id($this->_request->class_id);
		}elseif(isset($this->_request->institution_id))
        {
			$this->view->arrClasses = $objClasses->classes_select(array(
				"institution_id" => $this->_request->institution_id,
				"_ORDER" => "sub"
			));
			$this->view->arrOrders = $kiosk->orders_select_by_institution_id_goup_by_classes($this->_request->institution_id);
            // check if mode is set and is grouped
            if(isset($mode) && $mode="grouped")
            {
                $this->view->mode = $mode;
            }
                // Get all orders for the institution
			//$this->view->arrOrders = $kiosk->orders_select_by_institution_id($this->_request->institution_id);
		}elseif(isset($this->_request->network_id)){
			// Get all orders for the network
			$this->view->arrOrders = $kiosk->orders_select_by_institution_id($this->_request->network_id);
		}elseif(isset($this->_request->host_id)){
			// Get all orders for the host
			$this->view->arrOrders = $kiosk->orders_select_by_institution_id($this->_request->host_id);
		}else{
			// Get all orders
            $this->view->arrOrders = $kiosk->orders_select_by_institution_id(0);
		}

		// build data set
		$arrCachedClasses = array();
		$arrVoucherTypes = array("Checked Out", "Printed", "Redeemed");
		$arrCheckedOut = array();
		foreach ($arrVoucherTypes as $strVoucherType)
		{
			if (isset($this->view->arrClasses))
			{
				foreach ($this->view->arrClasses as $objClass)
				{
					foreach ($this->view->arrOrders as $objOrder)
					{
						if (
							$objOrder->class_id == $objClass->class_id
							&& $objOrder->status == $strVoucherType
						) {
							$arrCheckedOut[$strVoucherType][$objClass->class_id][] = $objOrder;
							if (!isset($arrCachedClasses[$objClass->class_id]))
								$arrCachedClasses[$objClass->class_id] = $objClass;
						}
					}
				}
			}
			else
			{
				foreach ($this->view->arrOrders as $objOrder)
				{
					if (
						$objOrder->status == $strVoucherType
					) {
						$arrCheckedOut[$strVoucherType][$this->_request->class_id][] = $objOrder;
					}
				}
			}
		}
		//var_dump($arrCheckedOut);exit;
		$this->view->arrCheckedOut = $arrCheckedOut;
		$this->view->arrCachedClasses = $arrCachedClasses;
	}

	public function orderseditAction()
	{

		$kiosk = new Kiosk();
		if(isset($this->_request->order_id)){
			$this->view->order = $kiosk->get_order($this->_request->order_id);
		}else{
			$this->view->order = array('');
		}

		if($this->_request->isPost()){

			if($this->_request->updateMode != "Print"){

				foreach($_POST['user_prize_id'] as $key => $value){
					//echo "key id $key and value is $value";
					//echo "Description is: " . $_POST['prize_name_fx'][$key	];
					//echo "Update mode: " . $this->_request->updateMode;

					$arrUpdate = array(	"user_prize_id"	=> $value,
									"user_id"		=> $_POST['student_id'][$key],
									"description"	=> $_POST['prize_name'][$key],
									"price"			=> $_POST['price'][$key],
									"currency"		=> $_POST['currency'][$key],
									"serial"		=> $_POST['serial'][$key],
									"status"		=> $this->_request->updateMode);
					$result = $kiosk->update_order($arrUpdate);

				}
			} else {
				//print vouchers here

			}

			echo 1;
			exit;
		}
	}

	public function ordersprintAction()
	{
		$kiosk = new Kiosk();
		if(!isset($_POST['user_prize_id'])){
			$this->view->arrVouchers = array();
            if(DEV_ENV =="devel" || DEV_ENV =="staging")
            {
                print text("Sorry, there was an error") . ": OC-OP101-FG45454";
                exit;
            }
            else
            {
                print "Please select a voucher";
            }
            //return empty array
		} else {
			foreach($_POST['user_prize_id'] as $key => $value){
			$arrIds[] = $value;
			}
            //var_dump($arrIds); exit;
			$this->view->arrVouchers = $kiosk->batch_print($arrIds);
        }
		$this->view->arrVouchers = $kiosk->batch_print($arrIds);
	}
}

?>