<?php
class AchievementController extends Zend_Controller_Action
{
	function preDispatch()
	{

	}

	function qrcardAction()
	{
		$query = new QueryGen();
		$objConfig = new Config();
		$arrGet = $this->_request->getParams();
		if (!isset($arrGet['dataparams']))
		{
			print 'Sorry, there was an error: CC-ACPO101-g3g4dd';
			exit;
		}
		$arrDataParams = json_decode($arrGet['dataparams']);
		if (!$arrDataParams || gettype($arrDataParams) != "object")
		{
			print 'Sorry, there was an error: CC-ACPO102-g9dd9d';
			exit;
		}
		$arrDataParams = (array) $arrDataParams;
		if (!isset($arrDataParams['intTask']))
		{
			print 'Sorry, there was an error: CC-ACPO103-f9d0gf';
			exit;
		}
		$this->view->objTask = $objTask = first($query->tasks__select(array(
			'task_id' => $arrDataParams['intTask'],
			'institution_id' => 601
		)));
		$this->view->arrAchievementCardConfig = $objConfig->load(array(
			"set" => array("achievementcards"),
			"institution_id" => 601
		));
		if (!$objTask)
		{
			print 'Sorry, there was an error: CC-ACPO103-f9d0gf';
			exit;
		}
		$this->view->objCampaign = $objCampaign = first($query->campaigns__select(array(
			'campaign_id' => $objTask->campaign_id
		)));
		if (!$objCampaign)
		{
			print 'Sorry, there was an error: CC-ACPO103-f9d0gf';
			exit;
		}
		$intPoints = $arrDataParams['intPoints'];
		if ($objTask->is_locked == 1)
		{
			$intPoints = $objTask->points;
		}
		$this->view->intPoints = $intPoints;
		$arrConfigOptions = $this->view->arrConfigOptions = $objConfig->load(array(
			"set" => array("system"),
			"institution_id" => 601
		));
		$this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => 601
		)));
		$intPages = intval($arrDataParams['intPageCount']);
		$intPages = $intPages < 1 ? 1 : $intPages;
		$this->view->intPages = $intPages;
		$intCardsPerPage = 10;
		$intClass = 0;
		$arrBarcodes = array();
		for ($intItr=0; $intItr<$intPages*$intCardsPerPage; $intItr++)
		{
			$intBarcode = $arrBarcodes[$intItr] = "4" . rand_num_string(19);
			// check if the database to see if barcode already exists
			// this code needs optimization because it will make a lot of queries
			// checking that database for each bar code is a bad method in the first place
			// a better solution is to get a proper random function that doesnt bug out like this one
			do {
				$objAchievementCard = first($query->achievement_cards__select(array(
					"card_serial" => $intBarcode
				)));
			}
			while ($objAchievementCard);

			$arrInsert = array(
				"institution_id"	 => 601,
				"campaign_id"	 	 => $objCampaign->campaign_id,
				"task_id" 			 => $objTask->task_id,
				"class_id"			 => $intClass,
				"card_serial"		 => $intBarcode,
				"card_type"	 		 => $this->_user_session_data->permission,
				"card_points"		 => $intPoints,
				"status"			 => 'not scanned',
				"created_by"         => $this->_user_session_data->user_id
			);
			$query->achievement_cards__insert($arrInsert);
		}
		$this->view->arrBarcodes = $arrBarcodes;
	}

}
?>