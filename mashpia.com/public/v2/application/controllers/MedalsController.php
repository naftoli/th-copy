<?php
class MedalsController extends Zend_Controller_Action
{
    private $_user_session_data;

    function preDispatch()
    {
		// Get the session object
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		/*
		if ($this->_user_session_data)
		{
			if (
				empty($this->_user_session_data->user_id)
				|| empty($this->_user_session_data->institution_id)
				|| empty($this->_user_session_data->permission)
				|| !$this->_user_session_data->is_user_active
			) {
				// Not allowed in
				$this->_redirect('logout');
			}
		}
		else
		{
			// Not allowed in
			$this->_redirect('logout');
		}
		*/
    }

    public function medaleditorAction()
    {
		$objMedals = new Medals();
		$objMissions = new Missions();

		$this->view->campaign_id = $intCampaign = $this->_request->getParam("campaign_id");
		if (
			!isset($intCampaign)
			|| !$intCampaign
		) {
			print text("Sorry, there was an error") . ": CM-CM101-8DF67G";
			exit;
		}

		// Get a count of the missions under this campaign
		$this->view->intMissionsCount = $intMissionsCount = current(current($objMissions->_missions_select(array(
			"campaign_id" => $intCampaign,
			"count" => "true"
		))));
		$intInstitution = $this->_user_session_data->institution_id;
		$arrMedals = $objMedals->_medals_select(array(
			"campaign_id" => $intCampaign,
			"institution_id" => $intInstitution
		));
		$boolAjax = $this->_request->getParam("ajax");
		$intMedal = intval($this->_request->getParam("medal_id"));
		if ($boolAjax) // Display medals
		{
			print json_encode($arrMedals);
			exit;
		}
		elseif ($intMedal) // Move up, down, or delete
		{
			if ($this->_request->getParam("up"))
			{
				$boolResult = $objMedals->move_hierarchy(array(
					"move" => "up",
					"medal_id" => $intMedal
				));
				print $boolResult;
				exit;
			}
			else if ($this->_request->getParam("down"))
			{
				$boolResult = $objMedals->move_hierarchy(array(
					"move" => "down",
					"medal_id" => $intMedal
				));
				print $boolResult;
				exit;
			}
			else if ($this->_request->getParam("delete"))
			{
				$boolResult = $objMedals->_medals_delete(array(
					"medal_id" => $intMedal
				));
				print $boolResult;
				exit;
			}
		}
		if($this->_request->isPost()) // Save / update medals
		{
			if ($this->_request->getParam("update")) // Update multipule medals
			{
				/*
				// First calculate the total values to ensure they are not over the maximum
				$intItr=-1;
				$intValueSum = 0;
				while ($this->_request->getPost((++$intItr) . "_value"))
				{
					$intValueSum += intval($this->_request->getPost($intItr . "_value"));
				}
				if ($intValueSum > $intMissionsCount)
				{
					print "The sum of all medals cannot excead the total number of missions under this campaign.";
					exit;
				}
				*/

				// Loop through the feilds and complete the updates
				$intUpdatedCount = 0;
				$intItr=-1;
				while ($this->_request->getPost((++$intItr) . "_value"))
				{
					$intValue = intval($this->_request->getPost($intItr . "_value"));
					$intMedalId = intval($this->_request->getPost($intItr . "_medal_id"));
					$strName = preg_replace("/[^a-z0-9 ]/i", "", $this->_request->getPost($intItr . "_name"));
					if (!$intMedalId)
					{
						print text("Sorry, there was an error") . ": CM-ME101-SD089D";
						exit;
					}
					if (!strlen($strName))
					{
						print "You must include a name for the medal you are attempting to insert.";
						exit;
					}
					if (!$intValue)
					{
						print "The medal you are attempting to insert must have a value greater than 0.";
						exit;
					}
					$intUpdatedCount += $objMedals->_medals_update(array(
						"where" => array(
							"medal_id"	    	 => $intMedalId
						),
						"values" => array(
							"medal_name"	     => $strName,
							"medal_value"	     => $intValue
							// Images not yet implamented
							//"medal_image_id"	 => @$arrParams["medal_image_id"],
							//"medal_image_id_2"	 => @$arrParams["medal_image_id_2"]
						)
					));
				}
				print $intUpdatedCount;
				exit;
			}
			else // Save new
			{
				// A bit of validation
				$strName = preg_replace("/[^a-z0-9 ]/i", "", $this->_request->getPost("medal_name"));
				$intValue = intval($this->_request->getPost("medal_value"));
				if (!strlen($strName))
				{
					print "You must include a name for the medal you are attempting to insert.";
					exit;
				}
				if (!$intValue)
				{
					print "The medal you are attempting to insert must have a value greater than 0.";
					exit;
				}

				/*
				// Loop through the existing medals to come up with a sum of allocated medal values
				$intSum = 0;
				foreach ($arrMedals as $objMedal)
				{
					$intSum += $objMedal->medal_value;
				}

				// Check if the current sum + the value of the new medal doesnt surpass the total number of missions under this campaign
				if ($intValue + $intSum > $intMissionsCount)
				{
					print "The sum of all medals cannot excead the total number of mission under this campaign.";
					exit;
				}
				*/

				// Do the insert
				$intAI = $objMedals->_medals_insert(array(
					"institution_id"	 => $intInstitution,
					"campaign_id"	     => $intCampaign,
					"medal_hierarchy"	 => count($arrMedals),
					"medal_name"	     => $strName,
					"medal_value"	     => $intValue
					// Images not yet implamented
					//"medal_image_id"	 => @$arrParams["medal_image_id"],
					//"medal_image_id_2"	 => @$arrParams["medal_image_id_2"]
				));

				print $intAI;
				exit;
			}
		}
	}
}
?>