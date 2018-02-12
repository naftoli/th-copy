<?php
class Incrementals /* aka The Time Machine */
{
	public function __construct()
	{
	   // Start the DB objects
	   $this->_db = Zend_Registry::get('db');
	   $this->_db->setFetchMode(Zend_Db::FETCH_OBJ);
	   
	   // Start the session object
	   $this->_user_session_data = new Zend_Session_Namespace('user_session_data');
	   
	   // Model tools
	   $this->_tools = new ToolsModels();
	}
	
	/*
	 * Required: user_id, institution_id
	 */
	public function load_incrementals($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MI-LI101-SDF8DF";
			exit;
		}
		if (!isset($arrParams["institution_id"]))
		{
			print "Sorry, there was an error: MI-LI102-SD09F8";
			exit;
		}
		
		$strSql = "
			SELECT
				campaigns.*
			FROM
				missions,
				campaigns
			WHERE
				missions.mission_type = 'Incremental'
				AND campaigns.installed_campaign_id = missions.campaign_id
				AND campaigns.institution_id = " . $arrParams["institution_id"];
		$arrResults = $this->_db->fetchAll($strSql);
		
		return $arrResults;
	}
}
?>