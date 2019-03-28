<?php

/*
	// Table of contents

*/

class Feeds
{
	private $_db;
	private $_user_session_data;
	private $_tools;

	public function __construct()
	{
		// Start the DB objects
		$this->_db = Zend_Registry::get('db');
		$this->_db->setFetchMode(Zend_Db::FETCH_OBJ);
		
		// Start the session object
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		
		$this->_tools = new ToolsModels();
   	}
	
	// Generic functions
	public function _feeds_select ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		
		// Possible column selections
		$arrColumns = array (
			"activity_feed_id"	 => @$arrParams["activity_feed_id"],			
			"user_id"			 => @$arrParams["user_id"],
			"institution_id"	 => @$arrParams["institution_id"],
			"permission_id"	 	 => @$arrParams["permission_id"],
			"action"			 => @$arrParams["action"],
			"category"			 => @$arrParams["category"],
			"is_active"			 => @$arrParams["is_active"],
			"created"			 => @$arrParams["created"],
			"modified"			 => @$arrParams["modified"],
			"created_by"		 => @$arrParams["created_by"]
		);
		
		$strSql = "
			SELECT
				*
			FROM
				activity_feeds
			WHERE
				1
		";
		
		foreach ($arrColumns as $strColumn => $Value)
		{
			if (
				isset($Value)
				&& (
					$Value === 0
					|| $Value
				)
			) {
				if (!is_int($Value))
				{
					$Value = '"' . $Value . '"';
				}
				$strSql .= "
					AND `" . $strColumn . "` = " . $Value . "
				";
			}
		}
		
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
		
	}
	// Generic functions end	
	
	public function add_feed($institution_id, $action, $category)
	{
		$user_id = $created_by = $this->_user_session_data->user_id;
		$row = $this->get_row('SELECT * FROM permissions WHERE permissions.user_id='.$user_id);
		$permission_id = $row->permission_id;
		$date = date("Y-m-d H:i:s", time());
		$who = $this->_user_session_data->full_name;
		
		switch($category){
			case "Delete":
				$what = " deleted ";
				break;
			case "Status":
				$what = " changed status for ";
				break;
			case "Edit":
				$what = " edited ";
				break;
			case "Create":
				$what = " created ";
				break;
		}
		$action = $who . $what . $action;
		
		$arrInsert = array('user_id'		=> $user_id,
						   'institution_id'	=> $institution_id,
						   'permission_id'	=> $permission_id,
						   'action'			=> $action,
						   'category'		=> $category,
						   'created'		=> $date,
						   'created_by'		=> $created_by
						   );
		try{
			$result = $this->_db->insert('activity_feed', $arrInsert);
		}catch(Zend_Exception $e){
			echo 'There was an error MF-AF-JDHGF6';
			echo $e->getMessage;
			if(DEV_ENV == 'devel') print_r($arrInsert);
		}
		
		return $result;
	}
    
	/**
	 *retrieves user feeds from table
	 *
	 *@param int $user_id
	 *@param int $institution_id=null
	 *@param str $category=null
	 *@param int $from
	 *@param int $to
	 *@param int $limit
	 *
	 *@return arrResult
	 */
	public function show_feeds($user_id=null, $institution_id, $category=null,$from='',$to='',$limit=100, $query)
	{
		//find out whome you want the resultset be
		switch($query){
			case 'super':
				$strSql = '
				SELECT
					activity_feed.action AS action,
					activity_feed.created AS date
				FROM activity_feed
				ORDER BY activity_feed.created DESC';
				break;
			case 'host':
				$strSql = '
				SELECT
					b.institution_id AS inst_id,
					activity_feed.action AS action,
					activity_feed.created AS date
				FROM institutions as a JOIN institutions as b
				ON a.institution_id = b.host_id
				JOIN activity_feed
				ON b.institution_id = activity_feed.institution_id 
				WHERE a.institution_id = ' .$institution_id.'
				ORDER BY activity_feed.created DESC';
				break;
			case 'network':
				$strSql = '
				SELECT
					b.institution_id AS inst_id,
					activity_feed.action AS action,
					activity_feed.created AS date
				FROM institutions as a
				JOIN institutions as b
				ON a.institution_id = b.network_id
				JOIN activity_feed
				ON b.institution_id = activity_feed.institution_id 
				WHERE a.institution_id  = ' .$institution_id.'
				ORDER BY activity_feed.created DESC';
				break;
			case 'institution':
				$strSql = '
				SELECT
					institutions.institution_id AS inst_id,
					activity_feed.action AS action,
					activity_feed.created AS date
				FROM institutions INNER JOIN activity_feed
				ON institutions.institution_id = activity_feed.institution_id
				WHERE institutions.institution_id = '.$institution_id.'
				ORDER BY activity_feed.created DESC';
				break;
			case 'class':
				$strSql = '
				SELECT
					activity_feed.action AS action,
					activity_feed.created AS date
				FROM classes INNER JOIN institutions
				ON classes.institution_id = institutions.institution_id
				INNER JOIN activity_feed
				ON institutions.institution_id = activity_feed.institution_id
				WHERE classes.class_id = '.$institution_id.'
				ORDER BY activity_feed.created DESC';
				break;
			default:
				return $arrResult;
				
		}
		//echo $strSql; exit;
		try{
			$arrResult = $this->_db->fetchAll($strSql);
		}catch(Zend_Exception $e){
			echo 'There was an error: MF-SF-AHG65D';
			if(DEV_ENV=='devel') echo $strSql;
			$arrResult = array();
		}
		
		return $arrResult;
	}
	
	public function get_row($sql)
	{
		$row = $this->_db->fetchRow($sql);
		return $row;
	}
}
?>