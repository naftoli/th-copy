<?php
class Kiosk01
{
	/*
	 * Process a scanned card
	 * Required:
	 *		institution_id
	 *		user_id
	 *		bar_code
	 */
	public function achievement_card_process($arrParams)
	{
		if (!$arrParams || !is_array($arrParams))
		{
			return array(
				"error" => "Sorry, there was an error: MK01-AC101-54FDG2"
			);
		}
		if (!isset($arrParams["institution_id"]))
		{
			return array(
				"error" => "Sorry, there was an error: MK01-AC102-54SFG1"
			);
		}
		if (!isset($arrParams["user_id"]))
		{
			return array(
				"error" => "Sorry, there was an error: MK01-AC102-54SFG1"
			);
		}
		if (!isset($arrParams["bar_code"]))
		{
			return array(
				"error" => "Sorry, there was an error: MK01-AC103-5G34G1"
			);
		}

		$query = new QueryGen();

		if (preg_match("/^[0-9]{20}|[0-9]{15}$/", $arrParams["bar_code"]))
		{
			$arrClasses = array_hash("class_id", $query->user_classes__select(array(
				"user_id" => $arrParams["user_id"]
			)));

			// Load the card
			$objCard = first($query->achievement_cards__select(array(
				"card_serial" => (string) $arrParams["bar_code"]
			)));
			if (
				$objCard->institution_id != 1
				&& $objCard->institution_id != $arrParams["institution_id"]
			) {
				return array(
					"error" => "This card was created for a different institution."
				);
			}
			if (!$objCard)
			{
				return array(
					"error" => "The scan code was not found in our system. Maybe the bar code wasn't scanned properly."
				);
			}

			// Load campaign info if possible
			if (isset($objCard->task_id))
			{
				$objTask = $this->view->objTask = first($query->tasks__select(array(
					"task_id" => $objCard->task_id
				)));
				if ($objTask)
					$objCampaign = $this->view->objCampaign = first($query->campaigns__select(array(
						"campaign_id" => $objTask->campaign_id
					)));
			}
			if (
				$objCard->task_id
				&& (
					!$objTask
					|| !$objCampaign
				)
			) {
				return array(
					"error" => "Sorry, your scan card is not longer valid."
				);
			}

			// Verify that the student is in the right class
			if (
				!empty($objCard->class_id)
				&& $objCard->class_id > 0
				&& !isset($arrClasses[$objCard->class_id])
			) {
				return array(
					"error" => "You are not in the correct class to scan this card."
				);
			}

			if ($objCard->status == "scanned")
			{
				return array(
					"error" => "This card was already scanned."
				);
			}
			if ($objCard->campaign_id)
			{
				//deposit points to child's account and mark the achievement card as scanned
				$query->user_campaigns__insert(array(
					"user_id"			=> $arrParams["user_id"],
					"institution_id"	=> $arrParams["institution_id"],
					"campaign_id"		=> $objCard->campaign_id,
					"mission_id"		=> $objCard->mission_id,
					"task_id"			=> $objCard->task_id,
					"class_id"			=> $objCard->class_id,
					"schedule_date"		=> time(),
					"points_given"		=> $objCard->card_points
				));
			}
			$query->user_points__insert(array(
				"achievement_card_id"	=> $objCard->achievement_card_id,
				"user_id"				=> $arrParams["user_id"],
				"institution_id"		=> $arrParams["institution_id"],
				"campaign_id"			=> $objCard->campaign_id,
				"mission_id"			=> $objCard->mission_id,
				"task_id"				=> $objCard->task_id,
				"class_id"				=> $objCard->class_id,
				"points"				=> $objCard->card_points,
				"resource_name"			=> "specific achievement card",
				"prize_id"				=> 0
			));
			$query->achievement_cards__update(array(
				"where" => array(
					"card_serial" => $arrParams["bar_code"]
				),
				"values" => array(
					"status" => "scanned"
				)
			));
			return array(
				'success' => 'true'
			);
		}
		else
		{
			return array(
				"error" => "The scan code was not found in our system. Maybe the bar code wasn't scanned properly."
			);
		}
	}
}
?>
