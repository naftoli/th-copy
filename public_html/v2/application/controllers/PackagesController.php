<?php

/*
	// Table of contents

*/


class PackagesController extends Zend_Controller_Action
{
	private $_user_session_data;
	private $_feeds;

    function init()
	{}

	function preDispatch()
	{
		$this->boolVerbose = 0; // Controller top level verbosity debug

		// Get the session object
		$user_session = new Zend_Session_Namespace('user_session_data');
		$this->_user_session_data = $user_session;

		//instantiate feeds
		$this->_feeds = new Feeds();

		if ($user_session)
		{
			if (!empty($user_session->user_id) && !empty($user_session->institution_id) && !empty($user_session->permission) && $user_session->is_user_active)
			{
				// Send the user's id, their permission, and listing permissions to the view files
				$this->view->user_id = $user_session->user_id;
				$this->view->full_name = $user_session->full_name;
				$this->view->company_id = $user_session->company_id;
				$this->view->permission = $user_session->permission;
				$this->view->institution_name = $user_session->institution_name;
			}
			else
			{
				// Not allowed in
				$this->_redirect('logout');
			}
		}
		else
		{
			// Not allowed in
			$this->_redirect('logout');
		}
	}

	public function indexAction()
	{
		$this->_redirect('dashboard');
	}

	/**
	 * Saves a package into the db
	 *
	 * @param 	all params are passed by POST
	 * @return 	bool
	 *
	 */
	public function packageaddAction()
	{
		$objPackage = new Packages();
		$objUser	= new Users();
		$host_id	= $this->_request->getParam('host_id');
		$network_id	= $this->_request->getParam('network_id');

		$this->view->host_id = $institution_id = $this->_request->getParam('host_id');
		$this->view->network_id = $institution_id = $this->_request->getParam('network_id');

		if(isset($this->_request->network_id)){
			$institution_id = $this->_request->getParam('network_id');
		}elseif(isset($this->_request->host_id)){
			$institution_id = $this->_request->getParam('host_id');
		}else{
			$institution_id	= $this->_user_session_data->institution_id;
		}
		//get all items that are available for the package
		//get all parent ids
		//$parentIds = user_select_parentids($this->_user_session_data-user_id);
		$this->view->arrItems = $objPackage->package_get_items(array("institution_id"	=> $institution_id));

		if ($this->_request->isPost()) // Ajax
		{
			$name 			= $this->_request->getParam('package_name');
			$description 	= $this->_request->getParam('description');
			$price 			= $this->_request->getParam('price');
			$discount_price	= $this->_request->getParam('discount_price');
			$offered_to  	= $this->_request->getParam('offered_to');
			$currency		= $this->_request->getparam('currency');
			$created		= date("Y-m-d H:i:s", time());
			$created_by		= $this->_user_session_data->user_id;

			if(empty($name)){
				echo "You have to specify a name for this item.";
				exit;
			}

			if(empty($price)){
				echo "You have to specify the price for this item.";
				exit;
			}

			if(!is_numeric($price)){
				echo "You have to specify the price as a numerical value.";
				exit;
			}

			if(empty($discount_price)){
				echo "You have to specify the discount price for this item.";
				exit;
			}

			if(!is_numeric($discount_price)){
				echo "You have to specify the discount price as a numerical value.";
				exit;
			}

			$arrInsert = array(
						'institution_id'	=> $institution_id,
						'name' 				=> $name,
						'description'		=> $description,
						'price'				=> $price,
						'discount_price'	=> $discount_price,
						'package_type'		=> $offered_to,
						'currency'			=> $currency,
						'created'			=> $created,
						'created_by'		=> $created_by
			);
			$last_insert = $objPackage->package_insert($arrInsert);
			if($last_insert){
				//insert all items that belong to the package
				$result = $objPackage->package_insert_items($_POST, $last_insert);

				//log event
				$action = ' package '.$name;
				$result = $this->_feeds->add_feed($institution_id, $action, 'Create');
				echo 1;
				exit;
			}else{
				echo "There was an error";
				exit;
			}
		}
	}

	/**
	 * Saves a package item into the db
	 *
	 * @param 	all params are passed by POST
	 * @return 	bool
	 *
	 */
	public function packageitemaddAction()
	{
		$objPackage = new Packages();
		$this->view->host_id = $this->_request->getParam('host_id');
		$this->view->network_id = $this->_request->getParam('network_id');


		if ($this->_request->isPost()) // Ajax
		{
			//get the institution id
			if(isset($this->_request->network_id)){
				$institution_id = $this->_request->getParam('network_id');
			}elseif(isset($this->_request->host_id)){
				$institution_id = $this->_request->getParam('host_id');
			}else{
				$institution_id	= $this->_user_session_data->institution_id;
			}

			$name 		= $this->_request->getParam('item_name');
			$package_item_type 		= $this->_request->getParam('package_item_type');
			$price 		= $this->_request->getParam('price');
			$student_price 		= $this->_request->getParam('student_price');
			$currency	= $this->_request->getparam('currency');
			$type		= $this->_request->getparam('type');
			$created	= date("Y-m-d H:i:s", time());
			$created_by	= $this->_user_session_data->user_id;

			if(empty($name)){
				echo "You have to specify a name for this item.";
				exit;
			}

			if(empty($price)){
				echo "You have to specify the price for this item.";
				exit;
			}

			if(!is_numeric($price)){
				echo "You have to specify the price as a numerical value.";
				exit;
			}
			if(!is_numeric($student_price)){
				echo "You have to specify the price as a numerical value.";
				exit;
			}
			if($package_item_type == 'add-on')
			{
				if(empty($student_price) && !is_numeric($student_price) && $student_price > 0)
				{
					echo "You have to specify the student price as a numerical value.";
					exit;
				}
			}

			$arrInsert = array(
						'institution_id'	=> $institution_id,
						'name' 				=> $name,
						'package_item_type'	=>$package_item_type,
						'price'				=> $price,
						'student_price'		=> $student_price,
						'currency'			=> $currency,
						'created'			=> $created,
						'created_by'		=> $created_by
			);

			if($objPackage->package_item_insert($arrInsert)){
				//log event
				$action = ' package item '.$name;
				$result = $this->_feeds->add_feed($institution_id, $action, 'Edit');
				echo 1;
				exit;
			}else{
				echo "There was an error";
				exit;
			}
		}
	}

	public function packagehostsAction()
	{
		$objInstitutions = new Institutions();
		$this->view->arrHosts = $objInstitutions->get_all_hosts();
	}

	public function packagenetworksAction()
	{
		$objNetworks = new Institutions();
		$this->view->intHost = $this->_request->getParam("host_id");
		$this->view->arrNetworks = $objNetworks->network_list($this->view->intHost);
	}

	public function packageaddhostsAction()
	{
		$objCampaigns = new Campaigns();
    	$this->view->arrCampaigns = $objCampaigns->campaigns_select_hosts();
	}

	public function packageaddnetworksAction()
	{
		$this->view->intHost = $this->_request->getParam("host_id");
		$objCampaigns = new Campaigns();
    	$this->view->arrCampaigns = $objCampaigns->campaigns_select_networks($this->view->intHost);
	}

	public function packageitemaddhostsAction()
	{
		$objCampaigns = new Campaigns();
    	$this->view->arrCampaigns = $objCampaigns->campaigns_select_hosts();
	}

	public function packageitemhostsAction()
	{
		$objCampaigns = new Campaigns();
    	$this->view->arrCampaigns = $objCampaigns->campaigns_select_hosts();
	}

	public function packageitemaddnetworksAction()
	{
		$this->view->intHost = $this->_request->getParam("host_id");
		$objCampaigns = new Campaigns();
    	$this->view->arrCampaigns = $objCampaigns->campaigns_select_networks($this->view->intHost);
	}

	public function packageitemnetworksAction()
	{
		$this->view->intHost = $this->_request->getParam("host_id");
		$objCampaigns = new Campaigns();
    	$this->view->arrCampaigns = $objCampaigns->campaigns_select_networks($this->view->intHost);
	}

	public function packageinstitutionsAction()
	{}

	public function packagelistAction()
	{
		$objPackages = new Packages();
		$this->view->intHost = $this->_request->getParam("host_id");
		$this->view->intNetwork = $this->_request->getParam("network_id");
		$active = 1;

		if(!isset($this->_request->status)){
			$this->view->strStatus = 'active';
		}else{
			$this->view->strStatus = $this->_request->getParam("status");
		}

		if(isset($this->_request->host_id)){
			$institution_id = $this->_request->getParam("host_id");
			$type = 'host';
		}elseif(isset($this->_request->network_id)){
			$institution_id = $this->_request->getParam("network_id");
			$type = 'network';
		}else{
			$institution_id = null;
			$type = null;
		}

		//always retrieve all packages
		$this->view->arrPackages = $objPackages->packages_list($active, $institution_id, $type);
	}

	public function packageitemslistAction()
	{
		$objPackageItems = new Packages();
		$this->view->strStatus = $this->_request->getParam("status");

		if(isset($this->_request->network_id)){
			$this->view->arrPackageItems = $objPackageItems->package_items_by_network($this->_request->getParam('network_id'));
		} elseif(isset($this->_request->host_id)){
			$this->view->arrPackageItems = $objPackageItems->package_items_by_host($this->_request->getParam('host_id'));
		} else {
			$this->view->arrPackageItems = $objPackageItems->package_items_list(
				(isset($this->view->strStatus) == "active" ? 1 :0)
			);
		}
	}

	public function packageeditAction()
	{
		$intId = $this->_request->getParam("package_id");
		if (
			empty($intId)
			&& preg_match("/^[0-9]$/", $intId)
		) {
			print text("Sorry, there was an error") . ": PC-PE101-757KFD";
		}
		$objPackage = new Packages();
        $objRole = new Roles();
	    if(!$this->_request->isPost())
		{
			$this->view->arrPackages = $objPackage->packages_select_id($intId);
			//var_dump($this->view->arrPackages);exit;
			if(count($this->view->arrPackages) == 0)
			{
				print "Sorry the was an error: PC-PE101-343HTG";
			}
			elseif(count($this->view->arrPackages) > 0){
				$this->view->arrItems = $objPackage->package_get_items(array("institution_id"	=> $this->view->arrPackages[0]->institution_id));

			}
			else{
				//get all pacakge_items
				$this->view->arrItems = $objPackage->package_get_items(0);
			}
		}

        if($objRole->isAllowed('Super Administrator')){
            $this->view->is_editable = true;
        } else{
            $this->view->is_editable = false;
        }
		if ($this->_request->isPost()) // Ajax
		{
			// Construct an array of the data to insert if available

			// Declare
			$arrUpdate = array();

			// Define
			$strPackageName = $this->_request->getPost("name");
			$strPackageDescription = $this->_request->getPost("description");
			$strCurrency = $this->_request->getPost("currency");
			$intPrice = $this->_request->getPost("price");
			$intDiscountPrice = $this->_request->getPost("discount_price");
			// arrays with package_item_id and package_id
			$arrPackageItemId = $_POST['item_id'];
			$arrPackageId = $_POST['package_id'];

			//make sure we assign distinct values to our vars
			$arrUpdate["package_id"]     = $intId;
			$arrUpdate["name"]			 = $strPackageName;
			$arrUpdate["description"]	 = $strPackageDescription;
			$arrUpdate["currency"]		 = $strCurrency;
			$arrUpdate["price"]	         = $intPrice;
			$arrUpdate["discount_price"] = $intDiscountPrice;

			// Filter
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			$objFilter = new Zend_Filter_StripTags();
			$strPackageName = $objFilter->filter($strPackageName);
			$strPackageDescription = $objFilter->filter($strPackageDescription);
			$strCurrency = $objFilter->filter($strCurrency);

			// Merge
			if (
				isset($intPackage)
				&& !empty($intPackage)
			) {
				$arrUpdate["package_id"] = $intPackage;
			}
			if (
				isset($strPackageName)
				&& !empty($strPackageName)
			) {
				$arrUpdate["name"] = $strPackageName;
			}
			if (
				isset($strPackageDescription)
				&& !empty($strPackageDescription)
			) {
				$arrUpdate["description"] = $strPackageDescription;
			}
			if (
				isset($strCurrency)
				&& !empty($strCurrency)
			) {
				$arrUpdate["currency"] = $strCurrency;
			}
			if (
				isset($intPrice)
				&& !empty($intPrice)
			) {
				$arrUpdate["price"] = $intPrice;
			}
			if (
				isset($intDiscountPrice)
				&& !empty($intDiscountPrice)
			) {
				$arrUpdate["discount_price"] = $intDiscountPrice;
			}

			// Process
			if (count($arrUpdate)) {
				$strResult = $objPackage->package_update($arrUpdate, $intId);
				$objPackage->package_combos_update($_POST, $arrPackageId);
				//log event
				$action = ' package '.$strPackageName;
				$row = $this->_feeds->get_row('SELECT * FROM packages WHERE package_id = '.$intId);
				$result = $this->_feeds->add_feed($row->institution_id, $action, 'Edit');
			} else {
				$strResult = text("Sorry, there was an error") . ": PC-PE102-123HDW";
			}
			// Result
			print $strResult;
			exit; // Ajax
		}
		// END OF EDIT //
    }

	public function packageitemeditAction()
	{
		$intId = $this->_request->getParam("package_item_id");
		if (
			empty($intId)
			&& preg_match("/^[0-9]$/", $intId)
		) {
			print text("Sorry, there was an error") . ": PC-PIE101-321WJD";
		}
		$objPackageItems = new Packages();
        $objRole = new Roles();
		$this->view->arrPackageItems = $objPackageItems->package_items_select_id($intId);
        if($objRole->isAllowed('Super Administrator')){
            $this->view->is_editable = true;
        } else{
            $this->view->is_editable = false;
        }
		if ($this->_request->isPost()) // Ajax
		{
			// Construct an array of the data to insert if available

			// Declare
			$arrUpdate = array();

			// Define
			$strPackageName = $this->_request->getPost("name");
			$strPackageDescription = $this->_request->getPost("description");
			$strCurrency = $this->_request->getPost("currency");
			$intPrice = $this->_request->getPost("price");
			$intStudentPrice = $this->_request->getPost("student_price");

			//make sure we assign distinct values to our vars
			$arrUpdate["package_item_id"] = $intId;
			$arrUpdate["name"]			  = $strPackageName;
			$arrUpdate["description"]	  = $strPackageDescription;
			$arrUpdate["currency"]		  = $strCurrency;
			$arrUpdate["price"]	          = $intPrice;
			$arrUpdate["student_price"]	  = $intStudentPrice;

			// Filter
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			$objFilter = new Zend_Filter_StripTags();
			$strPackageName = $objFilter->filter($strPackageName);
			$strPackageDescription = $objFilter->filter($strPackageDescription);
			$strCurrency = $objFilter->filter($strCurrency);

			// Merge
			if (
				isset($intPackage)
				&& !empty($intPackage)
			) {
				$arrUpdate["package_id"] = $intPackage;
			}
			if (
				isset($strPackageName)
				&& !empty($strPackageName)
			) {
				$arrUpdate["name"] = $strPackageName;
			}
			if (
				isset($strPackageDescription)
				&& !empty($strPackageDescription)
			) {
				$arrUpdate["description"] = $strPackageDescription;
			}
			if (
				isset($strCurrency)
				&& !empty($strCurrency)
			) {
				$arrUpdate["currency"] = $strCurrency;
			}
			if (
				isset($intPrice)
				&& !empty($intPrice)
			) {
				$arrUpdate["price"] = $intPrice;
			}
			if($this->view->arrPackageItems[0]->package_item_type=="add-on")
			{
				if(isset($intStudentPrice) && !empty($intStudentPrice) && $intStudentPrice > 0)
				{
					$arrUpdate["student_price"] == $intStudentPrice;
				}
				else
				{
					print "You must include a student price for this add-on.";
				}
			}
			// Process
			if (count($arrUpdate)) {
				$strResult = $objPackageItems->package_item_update($arrUpdate, $intId);

				//log event
				$action = ' package item '.$strPackageName;
				$row = $this->_feeds->get_row('SELECT * FROM package_items WHERE package_item_id = '.$intId);
				$result = $this->_feeds->add_feed($row->institution_id, $action, 'Edit');
			} else {
				$strResult = text("Sorry, there was an error") . ": PC-PIE102-454CDS";
			}
			// Result
			print $strResult;
			exit; // Ajax
		}
		// END OF EDIT //
	}
	public function packageselectAction()
	{
		$objPackages = new Packages();
		$intInstitution =$this->_request->getParam("institution_id");
		$this->view->institution_id = $this->_request->getParam("institution_id");
		$this->view->arrPackages = $objPackages->school_purchased_addons($intInstitution);
	}
	public function packagesclassesAction()
	{
		$objPackages = new Packages();
		$objClasses = new Classes();
		$date = date("Y-m-d H:i:s");
		if($this->_request->mode == "delete")
		{
			$intAddonConfigId = $this->_request->getParam("addon_config_id");
			$boolResult = $objPackages->delete_addon_config($intAddonConfigId);
			if($boolResult){
				print 1;
			}
			exit;
		}
		if($this->_request->isPost())
		{
			foreach($_POST['class_id'] as $key => $value)
            {
                $userSelection = explode("_", $value);
				//first check if a configuration for this addon already exists in this school
				$arrCheckAddonConfig = array(
												"class_id" => $userSelection[0],
												"addon_id" => $userSelection[1],
												"institution_id" => $userSelection[2]
											);

				$boolAddonConfig = $objPackages->check_addon_config($arrCheckAddonConfig);
				//var_dump($boolAddonConfig); exit;
				if(count($boolAddonConfig) > 0)
				{
					print $this->view->message = "A configuration setting already exists for this class(es). \n
					Please select another class to which you would like to add this add-on";
					exit;
				}
                $arrInsert = array(
                     "class_id"		     => $userSelection[0],
                     "addon_id"	 		 => $userSelection[1],
                     "institution_id"	 => $userSelection[2],
					 "is_mandatory"		 => $_POST["input_mandatory"][$key],
                     "created"		     => $date,
					 "modified"			 => " ",
                     "created_by"        => $this->_user_session_data->user_id
                );
				//var_dump($arrInsert); //exit;
				$boolInsert = $objPackages->insert_addon_configurations($arrInsert);
			}
			if($boolInsert)
			{
				$action = 'association addon to a class';
				$result = $this->_feeds->add_feed($this->_user_session_data->institution_id, $action, 'Create');
				print 1;
				exit;
			}
			else{
				print $this->view->message = text("Sorry, there was an error") . ": PC-PCA101-345HTM";
				exit;
			}
		}
		else
		{
			$intInstitution = $this->_request->getParam("institution_id");
			$this->view->addon_id = $this->_request->getParam("addon_id");
			$this->view->institution_id = $this->_request->getParam("institution_id");
			$this->view->arrClasses = $objClasses->classes_select(
														array(
																"institution_id"	=> $intInstitution
															)
														);
			$this->view->arrClassesAddonSettings = $objPackages->select_addon_config(
														array(
																"institution_id"	=> $intInstitution,
																"addon_id"			=> $this->view->addon_id
															)
														);
		}
	}
	public function storefrontAction()
	{
		$objAddons = new Packages();
		if($this->_request->isPost())
		{
			$date = date("Y-m-d H:i:s", time());
			foreach($_POST["addon_id"] as $key => $value)
			{
				$arrInsertPurchase = array(
							"user_id"			=> $this->_request->getParam("user_id"),
							"institution_id"	=> $this->_user_session_data->institution_id,
							"price"				=> $_POST["addon_price"][$key],
							"payment_status"	=> "Pending",
							"is_active"			=> 1,
							"credit"			=> "",
							"created_by"		=> $this->_request->getParam("user_id"),
							"created"			=> $date
							);
				// insert into the purchases table
				$lastInsertId = $objAddons->insert_institution_addons_purchase($arrInsertPurchase);
				// get the last insert id= purchase_id
				//build an array for pruchase_details table
				$arrInsertPurchaseDetails = array(
							"purchase_id"		=> $lastInsertId,
							"pack_item_id"		=> $_POST["addon_id"][$key],
							"pack_item_type"	=> 'add-on',
							"pack_item_price"	=> $_POST["addon_price"][$key],
							"item_name"			=> $_POST["addon_name"][$key],
							"item_description"	=> $_POST["addon_description"][$key],
							"is_active"			=> 1,
							"created_by"		=> $this->_request->getParam("user_id"),
							"created"			=> $date
							);
				//insert into the purchase_details table
				$objAddons->insert_institution_addons_purchase_deteails($arrInsertPurchaseDetails);
			}
			$this->view->message = "You have successfully completed your purchase";
			print 1;
			exit;
		}
		else{
			$intInstitution = $this->_request->getParam("institution_id");
			//show a list of add-ons purchases previously. this select will be made from purchases and purchase_details tables

			$arrAddons = $objAddons->school_purchased_addons($intInstitution);
			//show a list of addons this is not found in the $arrAddons. This select will be made from package_items table
			//prepare addon_ids and load then into an array
			if(count($arrAddons) > 0)
			{
				$this->view->arrAddons = $arrAddons;
				foreach($arrAddons as $objAddon)
				{
					$arrAddonIds[] = $objAddon->pack_item_id;
				}
				$intAddonsId = join(",",$arrAddonIds);
				//var_dump($intAddonsId); exit;
				//look up all addons which are not found in the array $arrAddonIds, that will be available for purchase by the institution
				$arrAddonsFound = $objAddons->addons_select($intAddonsId);
				if(count($arrAddonsFound) >0)
				{
					$this->view->arrAddonsFound = $arrAddonsFound;
				}
				else{
					$this->view->message = "You have no other add-ons available for purchase at this time.";
					//exit;
				}
				//var_dump($this->view->arrAddonsFound); exit;
			}
			else
			{
				$this->view->message = "Sorry, it seems that your institution did not purchase any add-ons";
				//exit;
			}
		}


	}
}
?>