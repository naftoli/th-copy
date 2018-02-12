<?php
class ToolsModels
{
	public function array_hash($varList, $strKey)
	{
		$arrResult = array();
		foreach ($varList as $varItem)
		{
			$arrItem = (array) $varItem;
			if (isset($arrItem[$strKey]) && !isset($arrResult[$arrItem[$strKey]]))
				$arrResult[$arrItem[$strKey]] = $varItem;
		}
		return $arrResult;
	}

	public function rsqlclean($arrParams)
	{
		foreach ($arrParams as $intKey => $Value) {
			if (is_array($Value) || is_object($Value))
			{
				if (is_array($arrParams))
					$arrParams[$intKey] = $this->rsqlclean($Value); // Recursion
				else
					$arrParams->$intKey = $this->rsqlclean($Value);
			}
			else
			{
				if (is_string($Value))
					$Value = trim(mysql_real_escape_string($Value));
				if (is_array($arrParams))
					$arrParams[$intKey] = $Value;
				else {
					$arrParams->$intKey = $Value;;
				}
			}
		}
		return $arrParams;
	}

	public function cleanSlashes($arrParams, $intItr=0)
	{
		$intItr++;
		/*if ($intItr < 6)
		{
			print "$intItr <br>\n";
			var_dump($arrParams);

		} else exit;*/
		if (!$arrParams)
			return $arrParams;
		if (is_string($arrParams))
			return preg_replace("/\\\\+(.)/", "$1", $arrParams);
		if (!count($arrParams))
			return $arrParams;
		if (is_object($arrParams))
			$arrNewParams = (object) array();
		else
			$arrNewParams = array();
		foreach ($arrParams as $intKey => $Value) {
			if (is_array($Value) || is_object($Value))
			{
				if (is_array($arrParams))
					$arrNewParams[$intKey] = $this->cleanSlashes($Value, $intItr); // Recursion
				else if (is_object($arrParams))
				{
					$arrNewParams->$intKey = $this->cleanSlashes($Value, $intItr);
				}
				else
				{
					print "Sorry, there was an error: MTM-CS101-SDF87D";
					exit;//gettype($Value);exit;
				}
			}
			else
			{
				if (is_string($Value))
					$Value = preg_replace("/\\\\+(.)/", "$1", $Value);
				if (is_array($arrParams))
					$arrNewParams[$intKey] = $Value;
				else if (is_object($arrParams))
					$arrNewParams->$intKey = $Value;
				else
				{
					print "Sorry, there was an error: MTM-CS102-S9D9S0";
					exit;//gettype($Value);exit;
				}
			}
		}
		return $arrNewParams;
	}

	// See if there is a value greater than your needle
	public function array_greater_than($intQuery, $arrSource)
	{
		rsort($arrSource, SORT_NUMERIC);
		if (
			is_array($arrSource)
			&& count($arrSource)
		) {
			if ($arrSource[0] < $intQuery)
			{
				return 1;
			}
		}
		return 0;
	}

	// See if there is a value lesser than your needle
	public function array_less_than($intQuery, $arrSource)
	{
		sort($arrSource, SORT_NUMERIC);
		if (
			is_array($arrSource)
			&& count($arrSource)
		) {
			if ($arrSource[0] > $intQuery)
			{
				return 1;
			}
		}
		return 0;
	}

	public function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

}
?>