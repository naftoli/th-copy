<?php
	// original purpose transaction manage in new system
	ini_set('zlib.output_compression', '1');
	ini_set('default_charset', 'UTF-8');
	require_once('db.php');
	$intUser = @$_GET['user_id'];
	if (empty($intUser))
	{
		print json_encode(array(
			"error" => 'Sorry, there was an error: MAH-GPT101-89as7d'
		));
		exit;
	}
	$strSql = "
		SELECT
			*
		FROM
			date_tasks_marks
		WHERE
			user_id = " . $intUser . "
			AND mark_inactive = 0
		ORDER BY
			mark_date+0 ASC
	";
	$arrDateTaskMarks = rdgq($strSql);
	$strSql = "
		SELECT
			*
		FROM
			points
		WHERE
			user_id = " . $intUser . "
		ORDER BY
			award_date+0 ASC
	";
	$arrPoints = rdgq($strSql);
	// aggregate items in order of date
	$arrDates = array_stack('award_date', $arrPoints);
	$arrDates = array_merge_real_recursive($arrDates,array_stack('mark_date', $arrDateTaskMarks));
	$arrDateTaskMarksBubble = array_bubble_hash('mark_date', $arrDateTaskMarks);
	$arrPointsBubble = array_bubble_hash('award_date', $arrPoints);
	$arrResults = array();
	natsort($arrDates);
	$arrDates = array_reverse($arrDates, TRUE);
	foreach ($arrDates as $intDate)
	{
		if (isset($arrDateTaskMarksBubble[$intDate]))
		{
			foreach ($arrDateTaskMarksBubble[$intDate] as $objData)
			{
				$arrResults[] = array(
					'epoch' => jdtounix($objData->mark_date),
					'description' => $objData->mark_description 
						. ' done_qty: ' . $objData->done_qty
						. ' mark_quantity: ' . $objData->mark_quantity
						. ' ref: dtm-' . $objData->date_task_id,
					'points' => $objData->mark_points
				);
			}
		}
		if (isset($arrPointsBubble[$intDate]))
		{
			foreach ($arrPointsBubble[$intDate] as $objData)
			{
				$arrResults[] = array(
					'epoch' => jdtounix($objData->award_date),
					'description' => $objData->award_description 
						. ' ref: p-' . $objData->subject_id,
					'points' => $objData->award_points
				);
			}
		}

	}
	print serialize($arrResults);
	exit;
	dumper($arrResults,1,1);
?>