<?php
    class Ranks
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
        
		public function _ranks_select($arrParams)
		{
			$arrParams = $this->_tools->rsqlclean($arrParams);
			// Possible column selections
			$arrColumns = array (
				"user_id"	=> @$arrParams["user_id"],
				"rank_id"	=> @$arrParams["rank_id"],
				"date_promoted"	=>	@$arrParams["date_promoted"],
				"date_printed"	=>	@$arrParams["date_printed"],
				"date_book_received"	=>	@$arrParams["date_book_received"],
				"date_card_received"	=>	@$arrParams["date_card_received"],
				"created"	=>	@$arrParams["created"],
				"modified"	=>	@$arrParams["modified"],
				"created_by"	=>	@$arrParams["created_by"]	
			);
			
			$strSql = "
				SELECT
					*
				FROM
					user_ranks
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
        public function ranks_select_by_institution_id($institution_id)
        {
           if(!isset($institution_id) || $institution_id==0){
			$sql = 'SELECT * FROM ranks';
			}else{
				$utility = new Utilities();
				$childIds = $utility->institution_reverse_lookup($institution_id);
				$sql = 'SELECT * FROM ranks WHERE institution_id IN ('.$childIds.')';
			}
	
			try{
				$result = $this->_db->fetchAll($sql);
			} catch (Zend_Exception $e){
				echo "There was an error: MC-CSBII-JHSGDT";
				if(DEV_ENV == 'devel'){
					echo $sql;
					echo $e->getMessage();
				}
			}
			//echo $sql; exit;
			return $result;         
        }
        public function rank_insert($arrQuery)
        {
            $intCurrentDate = date("Y-m-d H:i:S");

			// Filter everything for the query
			foreach ($arrQuery as $intKey => $strValue) {
				$strValue = mysql_real_escape_string($strValue);
				$arrQuery[$intKey] = trim($strValue);
			}
	
			// Build the insert
			$arrFeilds = array (
				"institution_id"  => $arrQuery["institution_id"],
				"rank_title"      => $arrQuery["rank_title"],
				"rank_medals"     => $arrQuery["rank_medals"],
				"rank_color"      => $arrQuery["rank_color"],
				"created"         => $intCurrentDate,
				"created_by"      => $this->_user_session_data->user_id
			);
			
			// Execute
			$intResult = $this->_db->insert("ranks", $arrFeilds);
			return $intResult;
        }
        public function ranks_update($arrQuery, $rank_id)
        {
            $intCurrentDate = date("Y-m-d H:i:S");
			// Filter everything for the query
			foreach ($arrQuery as $intKey => $strValue) {
				$strValue = mysql_real_escape_string($strValue);
				$arrQuery[$intKey] = trim($strValue);
			}
		
			// Build the update
			$arrFeilds = array ();
			if (isset($arrQuery["rank_title"]))
				$arrFeilds["rank_title"] = $arrQuery["rank_title"];
			if (isset($arrQuery["rank_medals"]))
				$arrFeilds["rank_medals"] = $arrQuery["rank_medals"];
			if (isset($arrQuery["rank_color"]))
				$arrFeilds["rank_color"] = $arrQuery["rank_color"];
			
			$strWhere = "rank_id=" . $rank_id;			
			// Execute
			$intResult = $this->_db->update("ranks", $arrFeilds, $strWhere);
			return $intResult;
        }
        
        public function rank_select_name($strRankName, $intInstitution)
        {
            $strSql = "Select * from ranks where rank_title=\"" . $strRankName . "\"
            and institution_id=".$intInstitution;
            $arrResult = $this->_db->fetchRow($strSql);
			if($arrResult)
			{
				return $arrResult;
			}
			return 0;
        }
		
		public function rank_select_by_rank_id($rank_id)
		{
			$strSql = "Select * from ranks where rank_id=" . $rank_id;
			$arrResult = $this->_db->fetchRow($strSql);
			if($arrResult)
			{
				return $arrResult;
			}
			return 0;
		}
		public function rank_select_id($rank_id)
		{
			$strSql = "Select * from ranks where rank_id=". $rank_id;
			$arrResult = $this->_db->fetchRow($strSql);
			if($arrResult)
			{
				return $arrResult;
			}
			return 0;			
		}
		public function ranks_select_class_by_institution_id($institution_id)
		{
			$sql = "
				SELECT *
				FROM classes
				where institution_id=". $institution_id;
			try{
				$result = $this->_db->fetchAll($sql);
			} catch (Zend_Exception $e){
				echo "There was an error: MC-CSBII-JHSGDT";
				if(DEV_ENV == 'devel'){
					echo $sql;
					echo $e->getMessage();
				}
			}
			//echo $sql; exit;
			return $result;
		}
		
		public function ranks_select_by_institution($institution_id)
		{
			
			$sql="
				SELECT
					*
				FROM
					classes
				INNER JOIN
					user_classes ON classes.class_id=user_classes.class_id
				INNER JOIN
					user_ranks ON user_classes.user_id=user_ranks.user_id
				INNER JOIN
					ranks ON user_ranks.rank_id=ranks.rank_id
				WHERE
					classes.institution_id=".$institution_id."
				GROUP by ranks.rank_id ASC ";
			try{
					$result = $this->_db->fetchAll($sql);
				} catch (Zend_Exception $e){
					echo "There was an error: MR-RSBI101-ASDR6T";
					if(DEV_ENV == 'devel'){
						echo $sql;
						echo $e->getMessage();
					}
				}				
			return $result;
		}
		
		public function rank_cards_select($arrParams)
		{
			$arrParams = $this->_tools->rsqlclean($arrParams);
			if (!isset($arrParams["institution_id"]))
			{
				print "Sorry, there was an error: MR-RCS101-SD9F8D";
				exit;
			}
			$strSql = "
				SELECT
					*, ranks.rank_id, ranks.rank_title
				FROM
					classes,
					user_classes,
					user_ranks,
					users,
					institutions,
					ranks,
					permissions
				WHERE
					institutions.institution_id = ".$arrParams["institution_id"]."
					AND permissions.institution_id = institutions.institution_id
					AND permissions.permission = 'Student'
					AND users.user_id = permissions.user_id
					AND user_classes.user_id = users.user_id
					AND classes.class_id = user_classes.class_id
					AND classes.institution_id = institutions.institution_id
					AND user_ranks.user_id = users.user_id
					AND user_ranks.institution_id = institutions.institution_id
					AND user_ranks.rank_id=ranks.rank_id
					AND user_classes.class_role='Student'";
			if (isset($arrParams["class_id"]) && $arrParams["class_id"])
				$strSql .= "
					AND classes.class_id = " . $arrParams["class_id"];
				
			$strSql .= "
				GROUP BY
					user_classes.class_id, user_ranks.user_rank_id ASC";
			//print $strSql;exit;
			$arrResult = $this->_db->fetchAll($strSql);
			return $arrResult;
		}
		/** Function select all students in the class
		 *  inner joins are made in order to get the rest of the information
		 *  about a user, such as username, rank name,
		 *  @param: array arrParams
		 *  @return: arr $result
		 */
		public function ranks_select($arrParams)
		{
			if(isset($arrParams["rank_id"]) ){
				$strWhere=" AND user_ranks.rank_id=".$arrParams["rank_id"];
			} else { //if there ever be a case when institution would want to print all rank cards disregarding a particular rank.
				$strWhere=" AND	user_ranks.rank_id IN (
								Select
									MAX(rank_id) 
								FROM
									user_ranks
								WHERE
									user_ranks.user_id=user_classes.user_id
								)";
			}
			
			// to re-print a rank card for a given user
			if(isset($arrParams["user_id"]) && isset($arrParams["rank_id"]))
			{
				$sql = "SELECT
							*
						FROM
							user_ranks
						LEFT JOIN users on user_ranks.user_id=users.user_id
						LEFT JOIN institutions on user_ranks.institution_id=institutions.institution_id
						LEFT JOIN ranks on user_ranks.rank_id=ranks.rank_id
						WHERE
							user_ranks.user_id=".$arrParams["user_id"]."
							AND user_ranks.rank_id=".$arrParams["rank_id"];
				//print $sql; exit;
				try{
					$result = $this->_db->fetchAll($sql);
					} catch (Zend_Exception $e){
						echo "There was an error: MR-RS101-FGF6R6T";
						if(DEV_ENV == 'devel'){
							echo $sql;
							echo $e->getMessage();
						}
					}				
				return $result;
				exit;
			} else if (isset($arrParams["class_id"]) && isset($arrParams["rank_id"])) // if a particular and rank is selected
			{
				$sql= "
					SELECT
						*, users.image_id as 'user_image_id', MAX(user_ranks.rank_id)
					FROM
						user_classes,
						user_ranks,
						users,
						ranks,
						classes,
						institutions
					WHERE
						institutions.institution_id = " . $arrParams["institution_id"] . "
						AND user_ranks.institution_id = " . $arrParams["institution_id"] . "
						AND user_classes.user_id = user_ranks.user_id
						AND user_classes.user_id = users.user_id
						AND user_ranks.rank_id = ranks.rank_id
						AND user_classes.class_id=classes.class_id
						AND classes.institution_id = " . $arrParams["institution_id"] . "
						AND user_classes.class_id = ". $arrParams["class_id"] ."
						AND user_classes.class_role = 'Student'";
					
					$sql.= $strWhere;
					$sql.= " GROUP BY user_ranks.user_rank_id, users.user_id ASC";
					try{
						$result = $this->_db->fetchAll($sql);
					} catch (Zend_Exception $e){
						echo "There was an error: MR-RS101-FGF6R6T";
						if(DEV_ENV == 'devel'){
							echo $sql;
							echo $e->getMessage();
						}
					}				
					return $result;
					exit;
			}
			// selection for an entire institution
			if(isset($arrParams["institution_id"]))
			{
				$sql = "
					SELECT
						*, ranks.rank_id, ranks.rank_title
					FROM
						classes,
						user_classes,
						user_ranks,
						users,
						institutions,
						ranks,
						permissions
					WHERE
						institutions.institution_id = ".$arrParams["institution_id"]."
						AND permissions.institution_id = institutions.institution_id
						AND permissions.permission = 'Student'
						AND users.user_id = permissions.user_id
						AND user_classes.user_id = users.user_id
						AND classes.class_id = user_classes.class_id
						AND classes.institution_id = institutions.institution_id
						AND user_ranks.user_id = users.user_id
						AND user_ranks.institution_id = institutions.institution_id
						AND user_ranks.rank_id=ranks.rank_id
						AND user_classes.class_role='Student'
						";
				
				$sql.= $strWhere;
				$sql.= "
					GROUP BY
						user_classes.class_id, user_ranks.user_rank_id ASC";
				//print $sql; exit;
				try{
					$result = $this->_db->fetchAll($sql);
					} catch (Zend_Exception $e){
						echo "There was an error: MR-RS101-FGF6R6T";
						if(DEV_ENV == 'devel'){
							echo $sql;
							echo $e->getMessage();
						}
					}
				return $result;
				exit;
			}
		}
		public function ranks_select_by_class($class_id)
		{
			$sql="
				Select
					*
				FROM
					user_classes
				INNER JOIN
					user_ranks ON user_classes.user_id=user_ranks.user_id
				INNER JOIN
					ranks ON user_ranks.rank_id=ranks.rank_id
				WHERE
					user_classes.class_id=".$class_id ."
					AND user_classes.class_role='Student'
				GROUP BY ranks.rank_id ASC";
			//print $sql; exit;
			try{
					$result = $this->_db->fetchAll($sql);
				} catch (Zend_Exception $e){
					echo "There was an error: MR-RSBC101-DSD564";
					if(DEV_ENV == 'devel'){
						echo $sql;
						echo $e->getMessage();
					}
				}				
				return $result;
		}
		public function batch_print($arrUserRanksIds)
		{
			$date = date('Y-m-d H:i:s', time());
			$user_rank_ids = join(",", $arrUserRanksIds);
			$strSql = "
				SELECT
					institutions.name,
					institutions.address,
					institutions.city,
					institutions.state,
					institutions.image_id,
					institutions.institution_id,
					users.image_id as 'user_image_id',
					users.first_name,
					users.last_name,
					users.hebrew_first_name,
					users.hebrew_last_name,
					users.bar_code,
					users.user_serial,
					users.dob,
					users.dob_he_offset,
					users.dob_he,
					users.user_start_date,
					ranks.rank_title,
					ranks.rank_color,
					user_ranks.user_rank_id
				FROM
					user_ranks
				INNER JOIN institutions
					ON user_ranks.institution_id = institutions.institution_id
				INNER JOIN users
					ON user_ranks.user_id = users.user_id
				INNER JOIN ranks
					ON ranks.rank_id = user_ranks.rank_id
				WHERE user_rank_id IN (".$user_rank_ids.")";
			//print $strSql; exit;
			try{
				$arrRanks = $this->_db->fetchAll($strSql);
			} catch (Zend_Exception $e){
				echo "There was an error: MK-BP-KJ876S";
				echo $e->getMessage();
			}
			foreach($arrRanks as $r)
			{
				$sql = '
					UPDATE user_ranks				
					SET rank_status = "printed",
					date_printed="'.$date .'",
					created_by='.$this->_user_session_data->user_id.'
					WHERE user_rank_id = '.$r->user_rank_id;
				//print $sql ."<br />";
				$result = $this->_db->query($sql);
			}
			return $arrRanks;
		}
		public function batch_print_th($arrUserRanksIds)
		{
			$date = date('Y-m-d H:i:s', time());
			$user_rank_ids = join(",", $arrUserRanksIds);
			$strSql = "
				SELECT
					institutions.name,
					institutions.address,
					institutions.city,
					institutions.state,
					institutions.image_id,
					institutions.institution_id,
					users.image_id as 'user_image_id',
					users.first_name,
					users.last_name,
					users.hebrew_first_name,
					users.hebrew_last_name,
					users.bar_code,
					users.user_serial,
					users.dob,
					user_extended_info.dob_he_offset,
					user_extended_info.dob_he,
					user_extended_info.user_start_date,
					ranks.rank_title,
					ranks.rank_color,
					user_ranks.user_rank_id
				FROM
					user_ranks
				INNER JOIN institutions
					ON user_ranks.institution_id = institutions.institution_id
				INNER JOIN users
					ON user_ranks.user_id = users.user_id
				INNER JOIN user_extended_info
					ON user_ranks.user_id = user_extended_info.user_id
				INNER JOIN ranks
					ON ranks.rank_id = user_ranks.rank_id
				WHERE user_rank_id IN (".$user_rank_ids.")";
			//print $strSql; exit;
			try{
				$arrRanks = $this->_db->fetchAll($strSql);
			} catch (Zend_Exception $e){
				echo "There was an error: MK-BP-KJ876S";
				echo $e->getMessage();
			}
			foreach($arrRanks as $r)
			{
				$sql = '
					UPDATE user_ranks				
					SET rank_status = "printed",
					printed_by = "institution",
					date_printed="'.$date .'",
					created_by='.$this->_user_session_data->user_id.'
					WHERE user_rank_id = '.$r->user_rank_id;
				//print $sql ."<br />";
				$result = $this->_db->query($sql);
			}
			return $arrRanks;
		}
		public function user_rank_update($arrUserRanksIds)
		{
			$modified = date('Y-m-d H:i:s', time());
			$user_rank_ids = join(",", $arrUserRanksIds);
			$strSqlUpdate = "
				UPDATE user_ranks
				SET rank_status= 'redeemed',
				modified='".$modified."'
				WHERE user_rank_id IN (". $user_rank_ids .")";
			$result = $this->_db->query($strSqlUpdate);
			return $result;		
		}
		public function user_rank_send_to_th($arrUserRanksIds)
		{
			$modified = date('Y-m-d H:i:s', time());
			$user_rank_ids = join(",", $arrUserRanksIds);
			$strSqlUpdate = "
				UPDATE user_ranks
				SET printed_by= 'host',
				modified='".$modified."'
				WHERE user_rank_id IN (". $user_rank_ids .")";
			$result = $this->_db->query($strSqlUpdate);
			return $result;
		}
		public function insert_user_ranks($arrQuery)
		{
			// Filter everything for the query
			foreach ($arrQuery as $intKey => $strValue) {
				$strValue = mysql_real_escape_string($strValue);
				$arrQuery[$intKey] = trim($strValue);
			}
	
			// Build the insert
			$arrFeilds = array (
				"user_id"  			=> $arrQuery["user_id"],
				"rank_id"     		=> $arrQuery["rank_id"],
				"institution_id"    => $arrQuery["institution_id"],
				"date_promoted"     => $arrQuery["date_promoted"],
				"rank_status"       => $arrQuery["rank_status"],
				"printed_by"      	=> $arrQuery["printed_by"],
				"created"      		=> $arrQuery["created"],
				"created_by"      	=> $arrQuery["created_by"]
			);
			//var_dump($arrFeilds); exit;
			// Execute
			$lastInsertId = $this->_db->insert("user_ranks", $arrFeilds);
			return $lastInsertId;
		}
    }
?>