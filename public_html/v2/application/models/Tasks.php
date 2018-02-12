<?php
class Tasks
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
    
     private function getMashpiaSettings()
	{
		// get settings
		$strSql = "select lang_id, school_type from mashpiadb.schools where school_id = " . $this->_user_session_data->institution_id;
		$objResult = first($this->_db->fetchAll($strSql));
		$lang = $objResult->lang_id;
		$type = $objResult->school_type ? $objResult->school_type : 2;
		return array('lang' => $lang, 'type' => $type);
	}
    
     public function getAchievementCardTasks($arrCampaigns)
     {
		$campaigns = array();
		foreach ($arrCampaigns as $campaign => $info) {
			$campaigns[] = $campaign;
		}
		
		$strSql = "select s.subject_name, t.*
				from mashpiadb.subjects s
				join mashpiadb.achievement_tasks t using (subject_id)
				where s.subject_id in (" . implode(',', $campaigns) . ")
				and (t.base = 1 or t.base = " . $this->_user_session_data->institution_id . ")";
		if (isset($this->_user_session_data->class_id)) {
			$strSql .= " and (t.platoon = 1 or t.platoon =" . $this->_user_session_data->class_id . ")";
		}
		//echo $strSql; exit;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
     }
	 
	 // calculations for sm work somewhat differently in v2 than in mashpia - hence different code
	private	function calculateSM( $year ) {
			$sm = array(); 
			$day = 29; // last day of month before Rosh Chodesh
			//first get last sm for previous year
			$date = jewishtojd( 13, $day, ($year-1) );
			//$date += 1; //fix issue with jdtounix showing a day off
			$time = jdtounix( $date + 1 );
			$dayOfWeek = date( "w", $time );
			if ($dayOfWeek < 6) $date -= ++$dayOfWeek;
			$shabbosMevorchim = $date; 
			$sm[0] = $shabbosMevorchim;
			for ( $i = 1; $i < 13; $i++ ) {
				$date = jewishtojd( $i, $day, $year );
				//$date += 1; //fix issue with jdtounix showing a day off
				$time = jdtounix( $date + 1 );
				$dayOfWeek = date( "w", $time );
				if ($dayOfWeek < 6) $date -= ++$dayOfWeek;
				$shabbosMevorchim = $date; 
				$sm[$i] = $shabbosMevorchim; //note: if value of index #6 == index #7 then that means that it is NOT a leap year
			}
			return $sm;
		}
   
     public function getMashpiaTasks($type, $arrCampaigns, $start, $end, $grid = false, $ladders = array())
     {
		$campaigns = array();
		
		switch ($type) {
			case 'daily':
			case 'weekly':
				foreach ($arrCampaigns as $campaign => $info) {
					if (!in_array($campaign, array(1,40,94))) 
						$campaigns[] = $campaign;
				}
				break;
			case 'special':
				$campaigns = array(40,94);
				break;
			case 'tehillim':
				$campaigns = array(1);
				// change start and end dates so that it finds latest shabbos mevorchim
				//$start = 2457747;
				//$end = 2457747;
				// find out which year we are working with
				$db = Zend_Registry::get('db');
				$db->setFetchMode(Zend_Db::FETCH_OBJ);
				$sql = "select `val` from mashpiadb.global_settings where `key` = 'current_year'";
				$stmt = $db->query($sql);
				$row = $stmt->fetch();
				$year = $row->val;
				$sm = $this->calculateSM( $year );
				$current = 0;
				$today = unixtojd();
				$found = false;
				foreach ($sm as $month => $date) {
					if (!$found && $today < $date) {
						$current = ($month-1);
						$found = true;
					}
				}
				$start = $end = $sm[$current];
				break;
		}
				
		$settings = $this->getMashpiaSettings();			
		$strSql = "select dt.cat, dt.cat_ord_new, ceil(dt.points) as points, dt.grid_id, dt.quantity, dtm.subject_id from mashpiadb.date_tasks dt 
				join mashpiadb.date_tasks_missions dtm using (date_tasks_mission_id)
				join mashpiadb.labels l using (label_id)
				join mashpiadb.frequencies f using (frequency_id) 
				where dtm.subject_id in (" . implode(',', $campaigns) . ") 
				and dtm.start_date >= " . $start . "
				and dtm.end_date <= " . $end . " 
				and (dtm.created_by_school is null or dtm.created_by_school = " . $this->_user_session_data->institution_id . ")  
				and dtm.personal = 0                        
				and dtm.lang_id = " . $settings['lang'] . " 
				and dtm.school_type_id = " . $settings['type'] . " 
				and dt.cat != '' 
				and dt.grid_id is not null ";
		if ($grid) $strSql .= " and dt.grid_marking = 1";
		if ($type == 'daily') $strSql .= " and dt.daily_task = 1";
		else $strSql .= " and dt.daily_task = 0";
		if (!empty($ladders)) $strSql .= " and dtm.level in (" . implode(',', $ladders) . ")";
		$strSql .= " group by dtm.subject_id, dt.grid_id";
		if (in_array(1, $campaigns)) {
			//echo $strSql; exit;
		}
		$arrResult = $this->_db->fetchAll($strSql);
		foreach ($arrResult as $key => $row) {
			//$arrResult[$key]->cat_ord = intval(($row->subject_id . '' . $row->new_cat_ord) * 100);
			$arrResult[$key]->cat_ord = $row->cat_ord_new;
		}
		//dumper($arrResult,1,1);
		return $arrResult;
     }
    
     public function getMashpiaMarks($tasks, $users, $start, $markDate)
     {
		$arrMarks = array();
		foreach ($users as $user) {
			// get school type and language
			$sql = "select school_type_id, lang_id from mashpiadb.users where user_id = " . $user;
			//echo $sql; exit;
			$res = first($this->_db->fetchAll($sql));
			$school_type_id = $res->school_type_id;
			$lang_id = $res->lang_id;
			
			foreach ($tasks as $subject => $info) {
				// get ladders and years for user
				$strSql = "select * from mashpiadb.user_tracks where user_id = " . $user . " and subject_id = " . $subject;
				$arrResult = array_hash("subject_id", $this->_db->fetchAll($strSql));
				
				$level = isset($arrResult[$subject]->level) && $arrResult[$subject]->level > 0 ? $arrResult[$subject]->level : 6;
				$track_id = isset($arrResult[$subject]->track_id) && $arrResult[$subject]->track_id > 0 ? $arrResult[$subject]->track_id : 1;
				
				foreach ($info as $grid_id => $objTask) {
					$end = $start + 6;
					$strSql = "select dt.date_task_id, dt.daily_task from mashpiadb.date_tasks dt 
								join mashpiadb.date_tasks_missions dtm using (date_tasks_mission_id) 
								where dtm.start_date >= " . $start . " 
								and dtm.end_date <= " . $end . " 
								and dtm.level = " . $level . " 
								and dtm.track_id = " . $track_id . " 
								and dtm.school_type_id = " . $school_type_id . " 
								and dtm.lang_id = " . $lang_id . " 
								and dt.grid_id = " . intval($grid_id);
					$arrResult = first($this->_db->fetchAll($strSql));
					//if ($user == 8273) dumper($arrResult,1,1);
					if (!empty($arrResult)) {
						//$sql = "select * from mashpiadb.date_tasks_marks
						//		where date_task_id = " . $arrResult->date_task_id . " 
						//		and user_id = " . $user . "
						//		and done_qty > 0";
						$sql = "select * from mashpiadb.date_tasks_marks dtm 
								join mashpiadb.date_tasks dt using (date_task_id)
								join mashpiadb.date_tasks_missions dtmm using (date_tasks_mission_id) 
								where dt.grid_id = " . $grid_id . " 
								and dtm.user_id = " . $user . "
								and dtmm.start_date >= " . $start . "
								and dtmm.end_date <= " . $end . " 
								and dtm.done_qty > 0";
						if ($arrResult->daily_task == '1') $sql .= " and mark_date = " . $markDate;
						//if ($user == 8273) echo $sql; exit;
						$result = $this->_db->fetchAll($sql);
						$object = new stdClass();
						$object->user_id = $user;
						$object->grid_id = $grid_id;
						$num = count($result);
						if ($num) {
							$object->marked = $num;
							$object->mark = $result[0]->done_qty;
						} else {
							$object->marked = 0;
							$object->mark = 0;
						}
						$arrMarks[] = $object;
					} else {
						$object = new stdClass();
						$object->user_id = $user;
						$object->grid_id = $grid_id;
						$object->marked = 0;
						$arrMarks[] = $object;
					}
				}
			}
		}
		//if ($user == 8273) dumper($arrMarks,1,1);
		//dumper($arrMarks,1,1);
		return $arrMarks;	
     }
   
     public function markMashpiaTasks($arrInfo)
	{
		require_once "/home/mashpia/public_html/classes/missions_updater.php";
		$m = new mission_updater( true );
		$inserts = array();
		foreach ($arrInfo as $user => $info) {
			// get school type and language
			$sql = "select school_type_id, lang_id from mashpiadb.users where user_id = " . $user;
			$res = first($this->_db->fetchAll($sql));
			$school_type_id = $res->school_type_id;
			$lang_id = $res->lang_id;
			
			// keep track of date tasks mission id's for updating
			$missions = array();
			
			foreach ($info as $subject => $other) {
				// get ladders and years for user
				$strSql = "select * from mashpiadb.user_tracks where user_id = " . $user . " and subject_id = " . $subject;
				$arrResult = array_hash("subject_id", $this->_db->fetchAll($strSql));
			
				$level = $arrResult[$subject]->level > 0 ? $arrResult[$subject]->level : 6;
				$track_id = $arrResult[$subject]->track_id > 0 ? $arrResult[$subject]->track_id : 1;
				
				foreach ($other as $row) {
					if (empty($row['value'])) $done = 0;
					else $done = intval($row['value']);
					$start = $row['start'];
					$end = $start + 6;
					$strSql = "select dtm.date_tasks_mission_id, dt.date_task_id, dt.points, dt.name, dt.daily_task from mashpiadb.date_tasks dt 
								join mashpiadb.date_tasks_missions dtm using (date_tasks_mission_id)
								where dtm.start_date >= " . $start . "
								and dtm.end_date <= " . $end . "
								and dtm.level = " . $level . "
								and dtm.track_id = " . $track_id . "
								and dtm.school_type_id = " . $school_type_id . "
								and dtm.lang_id = " . $lang_id . " 
								and dt.grid_id = " . intval($row['grid_id']);
					if ($subject == 1) {
						//echo $strSql; exit;
					}
					//if ($user == 51357 && $row['grid_id'] == 13006) {
					//	echo $strSql;
					//	exit;
					//}
					$arrResult = first($this->_db->fetchAll($strSql));
					//if ($user == 20302) dumper($arrResult,1,1);
					
					//if ($user == 51357 && $row['grid_id'] == 13006) dumper($arrResult,1,1);
					if (!empty($arrResult)) {
						$missions[] = $arrResult->date_tasks_mission_id;
						//$sql = "select * from mashpiadb.date_tasks_marks
						//		where date_task_id = " . $arrResult->date_task_id . " 
						//		and user_id = " . $user;
						$sql = "select * from mashpiadb.date_tasks_marks dtm 
								join mashpiadb.date_tasks dt using (date_task_id) 
								where dt.grid_id = " . intval($row['grid_id']) . " 
								and dtm.user_id = " . $user;
						if ($subject == 1 || $arrResult->daily_task) $sql .= " and mark_date = " . $row['date'];
						else $sql .= " and mark_date >= " . $start . " and mark_date <= " . $end;
						$result = $this->_db->fetchAll($sql);
						//if ($user == 20302) dumper($result,1,1);
						if (count($result) == 0 && $done > 0) {
							$strSql = "insert into mashpiadb.date_tasks_marks
										set date_task_id = " . $arrResult->date_task_id . ", 
										user_id = " . $user . ",
										mark_date = " . $row['date'] . ",
										done_qty = " . $done . ",
										mark_description = \"" . $arrResult->name . "\", 
										mark_points = " . $arrResult->points;
							//if ($subject == 1 && $user == 8273) echo $strSql; exit;
							$inserts[$user][] = $strSql;
						} else if (count($result) == 1 && $done > 0) {
							$strSql = "update mashpiadb.date_tasks_marks dtm 
										set done_qty = " . $done . " 
										where user_id = " . $user . "
										and date_task_id = " . $arrResult->date_task_id;
							if ($subject == 1 || $arrResult->daily_task) $strSql .= " and mark_date = " . $row['date'];
							else $strSql .= " and mark_date >= " . $start . " and mark_date <= " . $end;
							$inserts[$user][] = $strSql;			
						} else if (count($result) == 1 && $done == 0) {
							$strSql = "delete from mashpiadb.date_tasks_marks  
										where user_id = " . $user . "
										and date_task_id = " . $arrResult->date_task_id;
							if ($subject == 1 || $arrResult->daily_task) $strSql .= " and mark_date = " . $row['date'];
							else $strSql .= " and mark_date >= " . $start . " and mark_date <= " . $end;
							$inserts[$user][] = $strSql;
						}
						//if ($user == 23370) echo $strSql; exit;
					}
				}
			}
			foreach ($missions as $mission) {
				$m->mission_update( $user, $mission );
			}
		}
		
		//dumper($inserts,1,1);
		$this->_db->beginTransaction();
		try {
			foreach ($inserts as $user => $info) {
				foreach ($info as $insert) {
					$this->_db->query($insert);
				}
			}
			$this->_db->commit();			
			return true;
		} catch (Exception $e) {
			$this->_db->rollBack();
			echo $insert; exit;
			return false;
		}
		//return true;
		exit;
     }

	// Generic functions
	public function _tasks_select ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"task_id"				=> @$arrParams["task_id"],
			"installed_task_id"		=> @$arrParams["installed_task_id"],
			"school_type"			=> @$arrParams["school_type"],
			"task_name"				=> @$arrParams["task_name"],
			"mission_id"			=> @$arrParams["mission_id"],
			"campaign_id"			=> @$arrParams["campaign_id"],
			"class_id"				=> @$arrParams["class_id"],
			"points"				=> @$arrParams["points"],
			"frequency"				=> @$arrParams["frequency"],
			"start_date"			=> @$arrParams["start_date"],
			"end_date"				=> @$arrParams["end_date"],
			"sequence"				=> @$arrParams["sequence"],
			"duration"				=> @$arrParams["duration"],
			"is_active"				=> @$arrParams["is_active"],
			"is_required"			=> @$arrParams["is_required"],
			"velocity"				=> @$arrParams["velocity"],
			"created"				=> @$arrParams["created"],
			"modified"				=> @$arrParams["modified"],
			"created_by"			=> @$arrParams["created_by"]
		);

		$strSql = "
			SELECT
				" . (isset($arrParams["_COUNT"]) ? "count(*)" : "*") . "
			FROM
				tasks
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
				if (is_array($Value) && !count($Value))
					return array();
				if (is_array($Value))
				{
					$arrValues = array();
					foreach ($Value as $Key1 => $Value1)
					{
						if (!is_int($Value1))
						{
							$Value1 = '"' . $Value1 . '"';
						}
						$arrValues[] = $Value1;

					}
					$strSql .= "
						AND `" . $strColumn . "` IN (" . join(",", $arrValues) . ")
					";
				}
				else
				{
					if (!is_int($Value))
					{
						$Value = '"' . $Value . '"';
					}
					$strSql .= "
						AND `" . $strColumn . "` = " . $Value . "
					";
				}
			}
		}

		if (isset($arrParams["hierarchy"])) // join select institution hierarchy
		{
			$strSql2 = "";
			if (
				isset($arrParams["hierarchy"]["host_id"])
				&& (
					$arrParams["hierarchy"]["host_id"] === 0
					|| $arrParams["hierarchy"]["host_id"]
				)
			) {
				$strSql2 .= "
					OR (
						host_id = " . $arrParams["hierarchy"]["host_id"] . "
						AND institution_type = 'School'
					)";
			}
			if (
				isset($arrParams["hierarchy"]["network_id"])
				&& (
					$arrParams["hierarchy"]["network_id"] === 0
					|| $arrParams["hierarchy"]["network_id"]
				)
			) {
				$strSql2 .= "
					OR (
						network_id = " . $arrParams["hierarchy"]["network_id"] . "
						AND institution_type = 'School'
					)";
			}
			if (
				isset($arrParams["institution_id"])
				&& (
					$arrParams["institution_id"] === 0
					|| $arrParams["institution_id"]
				)
			) {
				$strSql2 .= "
					OR (
						institution_id = {$arrParams["institution_id"]}
						AND (institution_type = 'School' OR institution_type='Camp')
					)";
			}

			if ($strSql2 != "")
			{
				$strSql2 = "
					SELECT
						institution_id
					FROM
						institutions
					WHERE
						0
				" . $strSql2;
				$strSql .= "
					AND institution_id in (" . $strSql2 . ")";
			}
		}
		else
		{	// query institution_id if its not hierarchile
			if (isset($arrParams["institution_id"]))
			$strSql .= "
				AND institution_id = " . $arrParams["institution_id"];
		}

		if (!isset($arrParams["_COUNT"]))
		{
			if (isset($arrParams["_ORDER"]))
			{
				$strSql .= "
					ORDER BY
						" . $arrParams["_ORDER"];
			}
			else
			{
				$strSql .= "
					ORDER BY
						sequence + 0 ASC
				";
			}
		}

		if (isset($arrParams["_LIMIT"]))
		{
			$strSql .= "
				LIMIT " . $arrParams["_LIMIT"];
		}

		if (isset($arrParams["schedule_date_min"]))
			$strSql .= "
				AND schedule_date >= " . intval($arrParams["schedule_date_min"]);
		if (isset($arrParams["schedule_date_max"]))
			$strSql .= "
				AND schedule_date <= " . intval($arrParams["schedule_date_max"]);

		//print $strSql;exit;
		$arrResult = $this->_tools->cleanSlashes($this->_db->fetchAll($strSql));
		return $arrResult;
	}

	public function _tasks_update($arrParams)
	{
		$arrValuesParams = array("task_name","school_type","sequence","campaign_id","mission_id","class_id","institution_id","points","frequency","start_date","end_date","duration","is_active","is_required","velocity","created","created_by");
		$arrWhereParams = array("task_id","installed_task_id","school_type", "task_name","sequence","campaign_id","mission_id","class_id","institution_id","points","frequency","start_date","end_date","duration","is_active","is_required","velocity","created","modified","created_by");

		$arrParams = $this->_tools->rsqlclean($arrParams);

		$arrValues = array();

		// Values

		foreach ($arrValuesParams as $strKey)
		{
			if (isset($arrParams["values"][$strKey]))
				$arrValues[$strKey] = $arrParams["values"][$strKey];
		}
		$arrValues["modified"] = date("Y-m-d H:i:S");

		// Where
		$arrWhere = array();
		foreach ($arrWhereParams as $strKey)
		{
			if (isset($arrParams["where"][$strKey]))
				$arrWhere[] = $this->_db->quoteInto($strKey . ' = ?', $arrParams["where"][$strKey]);
		}

		if (!count($arrWhere))
		{
			print "Sorry, there was an error: MB-BU101-TRTHTT";
			exit;
		}

		// Execute
		$boolResult = $this->_db->update("tasks", $arrValues, $arrWhere);
		return $boolResult;
	}

	public function _tasks_delete($arrParams)
	{
		$arrWhereParams = array("task_id","installed_task_id","school_type","task_name","sequence","campaign_id","mission_id","class_id","institution_id","points","frequency","start_date","end_date","duration","is_active","is_required","velocity","created","modified","created_by");
		$arrParams = $this->_tools->rsqlclean($arrParams);
		$arrFeilds = array();
		foreach ($arrWhereParams as $strKey)
		{
			if (isset($arrParams[$strKey]))
				$arrFeilds[] = $this->_db->quoteInto($strKey . ' = ?', $arrParams[$strKey]);
		}
		if (!count($arrFeilds))
		{
			print "Sorry, there was an error: MB-BLD101-SD7SD7";
			exit;
		}
		$boolResult = $this->_db->delete("tasks", $arrFeilds);
		return $boolResult;
	}

	public function _tasks_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}

		$arrFeilds = array (
			'task_id'				=> @$arrParams["task_id"],
			'installed_task_id'		=> @$arrParams["installed_task_id"],
			"school_type"			=> @$arrParams["school_type"],
			'task_name'				=> @$arrParams["task_name"],
			'sequence'				=> @$arrParams["sequence"],
			'campaign_id'			=> @$arrParams["campaign_id"],
			'mission_id'			=> @$arrParams["mission_id"],
			'class_id'				=> @$arrParams["class_id"],
			'institution_id'		=> @$arrParams["institution_id"],
			'points'				=> @$arrParams["points"],
			'frequency'				=> @$arrParams["frequency"],
			'start_date'			=> @$arrParams["start_date"],
			'end_time'				=> @$arrParams["end_time"],
			'duration'				=> @$arrParams["duration"],
			'is_active'				=> @$arrParams["is_active"],
			'is_required'			=> @$arrParams["is_required"],
			'velocity'				=> @$arrParams["velocity"],
			'created'				=> @$arrParams["created"],
			'modified'				=> @$arrParams["modified"],
			'created_by'			=> @$arrParams["created_by"]
		);

		// Execute
		$boolResult = $this->_db->insert("tasks", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

	public function _tasks_scale_select ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"tasks_scale_id"		=> @$arrParams["tasks_scale_id"],
			"task_id"				=> @$arrParams["task_id"],
			"grade"					=> @$arrParams["grade"],
			"ladder"				=> @$arrParams["ladder"],
			"mission_id"			=> @$arrParams["mission_id"],
			"campaign_id"			=> @$arrParams["campaign_id"],
			"class_id"				=> @$arrParams["class_id"],
			"institution_id"		=> @$arrParams["institution_id"],
			"is_required"			=> @$arrParams["is_required"],
			"velocity"				=> @$arrParams["velocity"],
			"comment"				=> @$arrParams["comment"],
			"created"				=> @$arrParams["created"],
			"modified"				=> @$arrParams["modified"],
			"created_by"			=> @$arrParams["created_by"]
		);

		$strSql = "
			SELECT
				*
			FROM
				tasks_scale
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

	public function task_filter_date_ranges($arrTasks)
	{
		$arrTasksBuffer = array();
		foreach ($arrTasks as $objTask)
		{
			// start
			if (preg_match('/^([0-9]{4})\-([0-9]{2})\-([0-9]{2})$/', $objTask->start_date, $arrMatched))
			{
				if ($arrMatched[1] > 0)
				{
					$intTime = mktime(0,0,0,$arrMatched[2],$arrMatched[3],$arrMatched[1]);
					if ($intTime > time())
						continue;
				}
			}
			if (preg_match('/^([0-9]{4})\-([0-9]{2})\-([0-9]{2})$/', $objTask->end_date, $arrMatched))
			{
				if ($arrMatched[1] > 0)
				{
					$intTime = mktime(0,0,0,$arrMatched[2],$arrMatched[3],$arrMatched[1]);
					if ($intTime < time())
						continue;
				}
			}
			$arrTasksBuffer[] = $objTask;
		}
		return $arrTasksBuffer;
	}
   ///// [ TASKS_TABLE ] /////////////////////////////////////////////////////////////////////////////

	public function task_select_campaigns($intHost=0, $intNetwork=0, $intInstitution=0)
	{
		$strSql = "
			SELECT
				*
			FROM
				campaigns
			WHERE
				1";
		if (isset($intInstitution) && $intInstitution)
		{
			$strSql .= "
				AND institution_id=" . $intInstitution;
		}
		else if (isset($intNetwork) && $intNetwork)
		{
			$strSql .= "
				AND institution_id=" . $intNetwork;
		}
		else if (isset($intHost) && $intHost)
		{
			$strSql .= "
				AND institution_id=" . $intHost;
		}
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}
	public function task_select_campaign_id($intCampaignParam)
	{
		$strSql = "
			SELECT
				*
			FROM
				campaigns
			WHERE
				campaign_id='$intCampaignParam'
				AND	is_active = 1";
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult;
	}

	public function task_select_mission_id($intCampaign,$intMission,$intInstitution)
	{
		$strSql = "
			SELECT
				*
			FROM
				tasks
			WHERE " .(
				$intCampaignParam
				? "campaign_id=$intCampaignParam"
				: "campaign_id!=0"
				). "
				AND " .(
				$intMissionParam
				? "mission_id=$intMissionParam"
				: "mission_id=!0"
				). "
				AND " .(
				$intInstitutionParam
				? "institution_id=$intInstitutionParam"
				: "institution_id!=0"
				). "
				AND	is_active = 1";
		print 'strSql: ' . $strSql;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}
	public function task_select_institution_id($intInstitutionParam)
	{
		$strSql = "
			SELECT
				*
			FROM
				institutions
			WHERE
				institution_id='$intInstitutionParam'";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	/**
	 * Function selects all campaigns based on institution id.
	 *
	 * @param int $institution_id
	 *
	 * @return arr $result
	 *
	 */
	public function tasks_select_by_institution_id($institution_id)
	{

		if(!isset($institution_id) || $institution_id==0){
			$sql = 'SELECT * FROM tasks';
		}else{
			$utility = new Utilities();
			$childIds = $utility->getChildInstitutions($institution_id);
			$sql = 'SELECT * FROM tasks WHERE institution_id IN ('.$childIds.')';
		}

		try{
			$result = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MT-TSBII-KJH67E";
			if(DEV_ENV == 'devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}
		//echo $sql; exit;
		return $result;
	}

	public function task_select_missions($intInstitution=0, $intCampaign=0)
	{
		$strSql = "
			SELECT
				*
			FROM
				missions
			WHERE
				" .(
					$intInstitution
					? "institution_id=$intInstitution"
					: "institution_id!=0"
				) . "
				AND "
				  .(
				  	$intCampaign
					? "campaign_id=$intCampaign"
					: "campaign_id!=0"
				). "
				AND is_active = 1";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}
	public function task_insert ($arrQuery) {
		$intCurrentDate = date("Y-m-d H:i:S");

		// Filter everything for the query
		foreach ($arrQuery as $intKey => $strValue) {
			$strValue = mysql_real_escape_string($strValue);
			$arrQuery[$intKey] = trim($strValue);
		}

		// Build the insert
		$arrFeilds = array (
			"task_name" => @$arrQuery["task_name"],
			"mission_id" => @$arrQuery["mission_id"],
			"campaign_id" => @$arrQuery["campaign_id"],
			"institution_id" => @$arrQuery["institution_id"],
			"points" => @$arrQuery["points"],
			"frequency" => @$arrQuery["frequency"],
			"start_date" => @$arrQuery["start_date"],
			"end_date" => @$arrQuery["end_date"],
			"sequence" => @$arrQuery["sequence"],
			"velocity" => @$arrQuery["velocity"],
			"created" => $intCurrentDate,
			"created_by" => $this->_user_session_data->user_id
		);

		// Execute
		$intResult = $this->_db->insert("tasks", $arrFeilds);
		return $intResult;
	}


	public function task_update ($arrQuery, $intId) {

		$intCurrentDate = date("Y-m-d H:i:S");

		// Filter everything for the query
		foreach ($arrQuery as $intKey => $strValue) {
			$strValue = mysql_real_escape_string($strValue);
			$arrQuery[$intKey] = trim($strValue);
		}

		// Build the update
		$arrFeilds = array ();
		if (isset($arrQuery["task_name"]))
			$arrFeilds["task_name"] = $arrQuery["task_name"];
		if (isset($arrQuery["mission_id"]))
			$arrFeilds["mission_id"] = $arrQuery["mission_id"];
		if (isset($arrQuery["campaign_id"]))
			$arrFeilds["campaign_id"] = $arrQuery["campaign_id"];
		if (isset($arrQuery["institution_id"]))
			$arrFeilds["institution_id"] = $arrQuery["institution_id"];
		if (isset($arrQuery["points"]))
			$arrFeilds["points"] = $arrQuery["points"];
		if (isset($arrQuery["frequency"]))
			$arrFeilds["frequency"] = $arrQuery["frequency"];
		if (isset($arrQuery["start_date"]))
			$arrFeilds["start_date"] = $arrQuery["start_date"];
		if (isset($arrQuery["end_date"]))
			$arrFeilds["end_date"] = $arrQuery["end_date"];
		if (isset($arrQuery["sequence"]))
			$arrFeilds["sequence"] = $arrQuery["sequence"];
		// Execute
		$intResult = $this->_db->update("tasks", $arrFeilds, "task_id=" . $intId);
		return $intResult;
	}

	public function tasks_select($boolStatus=1, $arrExtra=0)
	{
		$strSql = "
			SELECT
				*
			FROM
				tasks
			WHERE 1 ";
		/*
			Using arrExtra, an institution can be selected by id or using a host id
			and/or network id.
		*/

		$arrSql = array(); // All exceptions within this array will be AND joined
		if(isset($arrExtra["campaign_id"]))
		{
			$arrSql[] = "campaign_id = " . $arrExtra["campaign_id"];
		}
		if(isset($arrExtra["mission_id"]))
		{
			$arrSql[] = "mission_id = " . $arrExtra["mission_id"];
		}
		if(isset($arrExtra["task_id"]))
		{
			$arrSql[] = "task_id = " . $arrExtra["task_id"];
		}

		if(count($arrSql)){
			$strSql .= " AND (" . join(" AND ", $arrSql) . ")";
		}

		$arrSql = array(); // All exceptions within this array will be OR joined
		if (
			isset($arrExtra["host_id"])
			|| isset($arrExtra["network_id"])
		) {
			$strSubSql = "
				SELECT
					institution_id
				FROM
					institutions
				WHERE
					";
			$arrSubSql = array();
			if ( // Select host
				isset($arrExtra["host_id"])
			) {
				$arrSubSql[] = "(
					institution_id={$arrExtra["host_id"]}
					AND host_id = 0
					AND network_id = 0
				)";
			}
			if ( // Select network
				isset($arrExtra["network_id"])
			) {
				$arrSubSql[] = "(
					institution_id={$arrExtra["network_id"]}
					AND network_id = 0
					AND host_id != 0
				)";
			}
			if ( // Select institution
				isset($arrExtra["institution_id"])
			) {
				$arrSubSql[] = "(
					institution_id={$arrExtra["institution_id"]}
					AND network_id != 0
					AND host_id != 0
				)";
			}
			$arrSql[] = "institution_id IN (" . $strSubSql . "(" . join(" OR ", $arrSubSql) . "))";
		}
		if (count($arrSql)) {
			$strSql .= " AND (" . join(" OR ", $arrSql) . ")";
		}
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function task_select_id ($intId) {
		if (!$intId) {
			return 0;
		}
		$strSql = "
			SELECT
				*
			FROM
				tasks
			WHERE
				task_id=" . $intId;
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult;
	}

	public function task_select_name ($strName, $intInstitution, $intCampaign, $intMission) {
		$strSql = "
			SELECT
				*
			FROM
				tasks
			WHERE
				task_name=\"" . $strName . "\"
				AND institution_id=" . $intInstitution . "
				AND campaign_id=" . $intCampaign . "
				AND mission_id=" . $intMission;
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult;
	}

	public function get_host_and_network_id_by_task_id($intTaskId)
	{
		$strSql = "SELECT n.host_id, n.network_id ";
		$strSql = $strSql . "FROM tasks AS t ";
		$strSql = $strSql . "JOIN institutions AS n ON (t.institution_id=n.institution_id) ";
		$strSql = $strSql . "WHERE t.task_id=" . $intTaskId;
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult;
	}
	public function task_point_by_task_id($intTask)
	{
		$strSql = "
			SELECT
				points
			FROM
				tasks
			WHERE
				task_id=".$intTask;
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult;
	}
	public function task_name_select_by_task_id($intTask)
	{
		$strSql = "
			SELECT
				task_name
			FROM
				tasks
			WHERE
				task_id=".$intTask;
		$arrResult = $this->_db->fetchRow($strSql);
		return $arrResult;
	}

}
?>