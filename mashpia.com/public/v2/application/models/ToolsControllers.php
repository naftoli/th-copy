<?php
class ToolsControllers
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
	
	public function array_var_extract($varList, $strKey)
	{
		if (!is_array($varList))
		{
			$varList = array($varList);
		}
		array_walk($varList, array($this, 'var_extract_walker'), $strKey);
		return $varList;
	}
	
	private function var_extract_walker(&$varItem, $intItr, $strKey)
	{
		$arrItem = (array) $varItem;
		if (isset($arrItem[$strKey]))
			$varItem = $arrItem[$strKey];
	}
	
}
?>