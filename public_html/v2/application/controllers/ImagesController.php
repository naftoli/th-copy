<?php

/*
	// Table of contents
*/


class ImagesController extends Zend_Controller_Action
{
	private $_user_session_data;
    function init()
	{
		$this->view->host_id = $this->_request->host_id;
		$this->view->network_id = $this->_request->network_id;
		$this->view->institution_id = $this->_request->institution_id;

		$this->view->user_id = $this->_request->user_id;

	}

	function preDispatch()
	{
		
	}

	public function indexAction()
	{
		$this->_redirect('dashboard');
	}

	 public function imageshostsAction()
    {
		$objInstitutions = new Institutions();
		$this->view->objHosts = $objInstitutions->get_all_active_hosts();
    }
	// HOSTS //

	// NETWORKS //
    public function imagesnetworksAction()
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
		/*
		if ($this->view->intHost > 0)
		{
			$this->view->objNetworks = $this->objInstitution->get_networks_by_host_id($this->view->intHost);
		}
		elseif ($this->_user_session_data->permission == "Super Administrator")
		{
			$this->view->objNetworks = $this->objInstitution->get_all_of_the_networks();
		}
		*/
    }
	// NETWORKS //

	// INSTITUTIONS //
    public function imagesinstitutionsAction()
    {
		$this->view->intHost = $this->_request->getParam("host_id");
		$this->view->intNetwork = $this->_request->getParam("network_id");

		$objInstitutions = new Institutions();
		$this->view->objInstitutions =
			$objMission =
				$objInstitutions->_institutions_select(
					array(
						"institution_type" => "School",
						"host_id" => $this->view->intHost,
						"network_id" => $this->view->intNetwork,
						"is_active" => 1
					)
				);
		/*
		$Institutions = new Institutions();

		if ($this->view->intInstitution > 0)
		{
			$this->view->objInstitutions = $Institutions->get_institution($this->view->intInstitution);
		}
		elseif ($this->view->intNetwork > 0)
		{
			$this->view->objInstitutions = $Institutions->get_institutions_by_network_id($this->view->intNetwork);
		}
		elseif ($this->view->intHost > 0)
		{
			$this->view->objInstitutions = $Institutions->get_institutions_by_host_id($this->view->intHost);
		}
		elseif ($this->_user_session_data->permission == "Super Administrator")
		{
			$this->view->objInstitutions = $Institutions->get_all_of_the_institutions();
		}
		*/
    }

	public function imagesaddhostsAction()
    {
		$objRoles = new Roles();
    	$objCampaigns = new Campaigns();
    	$this->view->arrCampaigns = $objCampaigns->campaigns_select_hosts();
    }

    public function imagesaddnetworksAction()
    {
    	$this->view->intHost = $this->_request->getParam("host_id");
		$objCampaigns = new Campaigns();
    	$this->view->arrCampaigns = $objCampaigns->campaigns_select_networks($this->view->intHost);
    }

    public function imagesaddinstitutionsAction()
    {
    	$this->view->intHost = $this->_request->getParam("host_id");
    	$this->view->intNetwork = $this->_request->getParam("network_id");
		$objCampaigns = new Campaigns();
    	$this->view->arrCampaigns = $objCampaigns->campaigns_select_institutions($this->view->intHost, $this->view->intNetwork);
    }

    public function imagesaddAction()
    {
		echo 1;
		exit;
    }

	public function imagecategorieslistAction()
	{
		//sets variables for image picker link
		$this->view->mode = (isset($this->_request->mode)) ? $this->_request->mode : "default";
		$this->view->update = (isset($this->_request->update)) ? $this->_request->update : 'none';
		$this->view->id = (isset($this->_request->id)) ? $this->_request->id : 'none';


		$this->view->host_id = $this->_request->host_id;
		$this->view->network_id = $this->_request->network_id;
		$this->view->institution_id = $this->_request->institution_id;

		$objImage = new Image();

		if(!empty($this->_request->institution_id)){
			$arrCategories = $objImage->get_categories_by_institution($this->_request->institution_id);
		} elseif(!empty($this->_request->network_id)){
			$arrCategories = $objImage->get_categories_by_network($this->_request->network_id);
		} elseif(!empty($this->_request->host_id)){
			$arrCategories = $objImage->get_categories_by_host($this->_request->host_id);
		} else {
			$arrCategories = $objImage->get_all_categories();
		}

		$this->view->arrCategories = $arrCategories;

		if($this->_request->isPost()){
			$objImage = new Image();
			switch($this->_request->myaction){
				case 'add':
					if(!empty($this->_request->institution_id)){
						$institution_id = $this->_request->institution_id;
					}elseif(!empty($this->_request->network_id)){
						$institution_id = $this->_request->network_id;
					}elseif(!empty($this->_request->host_id)){
						$institution_id = $this->_request->getParam('host_id');
					}else{
						echo "Institution ID is not defined.
						Please navigate to the previous screen and click on one
						of the institutions where you want this category to be created.";
						exit;
					}
					$name = $this->_request->category_name;
					//check for duplicate name
					if($objImage->check_duplicate_category_name($name, $institution_id)){
						exit;
						break;
					}

					$arrInsert = array("institution_id"	=> $institution_id,
									   "name"			=> $name);

					$result = $objImage->add_image_category($arrInsert);
					echo $result;
					exit;
					break;

				case 'delete':
					$result = $objImage->delete_image_category($this->_request->category_id);
					echo 1;
					exit;
					break;
			}
		}
	}

	public function imageslistAction()
	{
		//capture target url from session variable
		$URL = new Zend_Session_Namespace('targetUrl');

		$this->view->targetUrl = $URL->url;

		$this->view->mode = (isset($this->_request->mode)) ? $this->_request->mode : "default";
		$this->view->update = (isset($this->_request->update)) ? $this->_request->update : 'none';
		$this->view->id = (isset($this->_request->id)) ? $this->_request->id : 'none';

		$objImage = new Image();
		$objUsers = new Users();
		$objInstitutions = new Institutions();

		$this->view->host_id = $this->_request->getParam("host_id");
		$this->view->network_id = $this->_request->getParam("network_id");
		$this->view->institution_id = $this->_request->getParam("institution_id");
		$this->view->category_id = $this->_request->category_id;

		//get images for the category
		if(isset($this->_request->category_id)){
			$category_id = $this->_request->getParam('category_id');
			$institution_id = $this->_request->getParam('institution_id');
			$this->view->arrImages = $objImage->get_images_by_category(array("category_id" => $category_id,
																			 "institution_id" => $institution_id));
		} else {
			echo "No category id specified.";
			exit;
		}


		//echo "action " . $this->_request->myaction; exit;

		if ($this->_request->isPost()) // Ajax
		{
			switch($this->_request->myaction){
				case 'add':
					$image_name = $this->_request->getParam('image_name');
					$user_id = $this->_request->getParam('user_id');
					$prize_id = $this->_request->getParam('prize_id');
					$institution_id = $this->_request->getParam('institution_id');
					$lastInsertId = $objImage->upload_image($image_name, $category_id);
					//if user_id is set then update users table and set image_id=$lastInsertId
					if(isset($user_id))
					{
						$arrUpdate = array("image_id"	=> $lastInsertId,
										   "user_id"	=> $user_id,
										   "mode"		=> "add");
						$result = $objUsers->update_user_images($arrUpdate);
					}
					if(isset($prize_id))
					{
						$arrUpdate = array("image_id"	=> $lastInsertId,
										   "prize_id"	=> $prize_id,
										   "mode"		=> "add");
						$result = $objImage->update_prize_images($arrUpdate);
					}
					if(isset($institution_id))
					{
						$arrUpdate = array("image_id"		=> $lastInsertId,
										   "institution_id"	=> $institution_id,
										   "mode"			=> "add");
						$result = $objInstitutions->update_institution_images($arrUpdate);
					}
					echo 1;
					exit;
					break;
				case 'delete':
					$objImage->delete_image($this->_request->image_id);
					$user_id = $this->_request->getParam('user_id');
					if(isset($user_id))
					{
						$arrUpdate = array("user_id"	=> $user_id,
										   "image_id"	=> $this->_request->image_id,
										   "mode"		=> "delete");
						//var_dump($arrUpdate); exit;
						$result = $objUsers->update_user_images($arrUpdate);
					}
					echo 1;
					exit;
					break;
				case 'set_image':
					$objImage->update_image_id($this->_request->update, $this->_request->id, $this->_request->image_id);
					echo 1;
					$URL->url = '';
					exit;
					break;
			}
    	}
	}

	public function imageslistprocessAction()
	{
		//echo "action " . $this->_request->myaction; exit;

		if ($this->_request->isPost()) // Ajax
		{
			$objImage = new Image();
			switch($this->_request->myaction){
				case 'add':
					$image_name = $this->_request->getParam('image_name');;
					$result = $objImage->upload_image($image_name, $category_id);
					echo 1;
					exit;
					break;
				case 'delete':
					$objImage->delete_image($this->_request->image_id);
					echo 1;
					exit;
					break;
				case 'set_image':
					$objImage->update_image_id($this->_request->update, $this->_request->id, $this->_request->image_id);
					echo 1;
					$URL->url = '';
					exit;
					break;
			}
    	}
	}

	public function imagepickerAction()
	{

	}

	public function imagecategoriesaddAction()
	{

	}
	public function viewsrcAction()
	{
		$intImage = intval($this->_request->getParam('image_id'));
		if (!$intImage)
			exit;
		$objStore = new Store();
		$objImage = $objStore->show_picture($intImage);
		$objConnection = mysql_connect('mashpia.icorpa.com', 'mashpia_devel', 'q65u7sZVsnGb');
		$objQuery = mysql_query("SELECT photo FROM mashpia_devel.images WHERE image_id = " . $intImage . " LIMIT 1", $objConnection) or die (mysql_error());
		header('Content-type: ' . $objImage->photo_type);
		$intImageExpires = 86400 * 1; // days
		header("Pragma: public");
		header("Cache-Control: maxage=" . $intImageExpires);
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $intImageExpires) . ' GMT');
		print base64_decode(mysql_result($objQuery, 0));
		//var_dump(mysql_result($objQuery, 0));
		exit;
	}

	public function showImageAction()
	{
		$strImageId = $this->_request->getParam('image_id');
		if (preg_match("/^[0-9_]+\.[a-z]+$/", $strImageId))
			return header("Location: " . WEB_ROOT . "imgs/v/src/" . $strImageId);
		$this->_showImage($strImageId);
		exit;
	}

	public function imageviewAction()
	{

	}

	private function _showImage($image_id)
	{

		$store = new Store();
		//if(!$image_id || empty($image_id)) $image_id = 3524;

		$image = $store->show_picture($image_id);
		if($image){
			header('Content-type: ' . $image->photo_type);
			$intImageExpires = 86400 * 1; // days
			header("Content-Disposition: inline;");
			header("Pragma: public");
			header("Cache-Control: maxage=" . $intImageExpires);
			header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $intImageExpires) . ' GMT');
			//echo $image->photo; exit;
			 if (base64_decode($image->photo, true))
				echo base64_decode($image->photo);
			   else
				echo $image->photo;
			exit;
		} else {
			exit;
		}

	}

	public function imageseditAction()
	{
		$this->view->image_id = $image_id = $this->_request->image_id;
		$this->view->image_name = $image_name = $this->_request->image_name;



		if ($this->_request->isPost()) // Ajax
		{
			//handle POST request here
			if(isset($image_id )&& isset($image_name)){
				$image = new Image();
				echo $image->update_image($image_name, $image_id);
				exit;
			} else {
				echo "Image ID or image name is not set";
				exit;
			}
    	}
	}
}
?>