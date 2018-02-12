<?php
class Image
{
    private $_db;
    private $_user_session_data;
	private $_tools;

    public function __construct()
    {
		// Start the DB objects
		$this->_db = Zend_Registry::get('db');
		$this->_db->setFetchMode(Zend_Db::FETCH_OBJ);
		$this->_tools = new ToolsModels();
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
    }

	// Generic functions
	public function _images_select ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		
		// Possible column selections
		$arrColumns = array (
			"image_id"	 		=> @$arrParams["image_id"],
			"created"			=> @$arrParams["created"],
			"modified"			=> @$arrParams["modified"],
			"created_by"		=> @$arrParams["created_by"]
		);
		
		$strSql = "
			SELECT
				*
			FROM
				images
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

	
	public function get_image($intImageId)
	{
		$strSql = "SELECT photo, photo_type FROM images WHERE image_id=" . $intImageId;
		$objPhoto = $this->_db->fetchRow($strSql);
		return $objPhoto;
	}
	
	/**
	 * Gets image categories by host_id
	 *
	 * @param $host_id
	 *
	 * @return $arrresult
	 *
	 */
	public function get_categories_by_host($host_id)
	{
		$sql = '
		SELECT b.institution_id, b.name, b.network_id, b.host_id FROM institutions AS a
		INNER JOIN institutions AS b
		ON a.institution_id = b.host_id
		WHERE a.institution_id = '.$host_id;
		
		try{
			$result = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MI-GCBH-KSI87D";
			if(DEV_ENV == 'devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}
		
		foreach($result as $r){
			$buffer[] = ' institution_id='.$r->institution_id;
		}
		
		if(count($buffer)> 0){
			$sql_or = join(' OR ', $buffer);
		} else {
			$sql_or = " 1=2 ";
		}
		
		$sql = '
		SELECT * FROM image_categories WHERE institution_id = '.$host_id. ' OR '.$sql_or;
		
		try{
			$result = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MI-GCBH-LKJHT4";
			if(DEV_ENV == 'devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}
		
		return $result;
	}
	
	public function get_categories_by_network($network_id)
	{
		$sql = '
		SELECT b.institution_id, b.name, b.network_id, b.host_id FROM institutions AS a
		INNER JOIN institutions AS b
		ON a.institution_id = b.network_id
		WHERE a.institution_id = '.$network_id;
		
		try{
			$result = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MI-GCBN-KLIO87";
			if(DEV_ENV == 'devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}
		
		foreach($result as $r){
			$buffer[] = ' institution_id='.$r->institution_id;
		}
		
		if(count($buffer)> 0){
			$sql_or = join(' OR ', $buffer);
		} else {
			$sql_or = " 1=2 ";
		}
		
		
		$sql = '
		SELECT * FROM image_categories WHERE institution_id = '.$network_id. ' OR '.$sql_or;
		
		
		try{
			$result = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MI-GCBN-NHSGT5";
			if(DEV_ENV == 'devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}
		
		return $result;
	}
	
	public function get_categories_by_institution($institution_id)
	{	
		$sql = '
		SELECT * FROM image_categories WHERE institution_id = '.$institution_id;
		
		try{
			$result = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MI-GCBH-LKJHT4";
			if(DEV_ENV == 'devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}
		
		return $result;
	}
	
	public function get_all_categories()
	{
		$sql = '
		SELECT * FROM image_categories';
		
		try{
			$result = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MI-GAC-ASE48K";
			if(DEV_ENV == 'devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}
		
		return $result;
	}
	
	public function add_image_category($arrInsert)
	{
		try{
			$result = $this->_db->insert("image_categories", $arrInsert);
			$lastInsert = $this->_db->lastInsertId();
		} catch (Zend_Exception $e){
			echo "There was an error: MI-AIC-HDGGFT";
			if(DEV_ENV == 'devel'){
				print_r($arrInsert);
				echo $e->getMessage();
			}
		}
		
		return $lastInsert;
	}
	
	public function check_duplicate_category_name($name, $institution_id)
	{
		$sql = '
		SELECT * FROM image_categories
		WHERE name="'.$name.'"
		AND institution_id='.$institution_id;
		try{
			$result = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MI-CDCN-OK87SG";
			if(DEV_ENV == 'devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}
		
		return isset($result) ? $result : false;
	}
	
	public function upload_image($image_name, $category_id)
	{
		//echo $image_name; exit;
		
		/*
		$filename = IMAGE_UPLOADER_DIRECTORY."/".$image_name;
		$handle = fopen($filename, "rb");
		$image = fread($handle, filesize($filename));
		fclose($handle);
		*/
		$image = chunk_split(base64_encode(file_get_contents(IMAGE_UPLOADER_URL."/".rawurlencode($image_name))));
		
		$image_type = getimagesize(IMAGE_UPLOADER_URL."/".rawurlencode($image_name));
		$date = date("Y-m-d H:i:s", time());
		$created_by = $this->_user_session_data->user_id;
		
		$arrFields = array (
			"photo"					=> $image,
			"photo_type"			=> $image_type['mime'],
			"image_name"			=> $image_name,
			"image_category_id"		=> $category_id,
			"created"				=> $date,
			"created_by"			=> $created_by
		);
		
		//physically delete file from hard disk
		//unlink(IMAGE_UPLOADER_URL."/".$image_name);
		
		$result = $this->_db->insert('images', $arrFields);
		return  $this->_db->lastInsertId();
		//return $result;
	}
	
	public function update_image($image_name, $image_id)
	{
		//echo "image name " . $image_name . " image id: " . $image_id; exit;
		
		/*
		$filename = IMAGE_UPLOADER_DIRECTORY."/".$image_name;
		$handle = fopen($filename, "rb");
		$image = fread($handle, filesize($filename));
		fclose($handle);
		*/
		$image = chunk_split(base64_encode(file_get_contents(IMAGE_UPLOADER_URL."/".rawurlencode($image_name))));
		
		$image_type = getimagesize(IMAGE_UPLOADER_URL."/".rawurlencode($image_name));
		$date = date("Y-m-d H:i:s", time());
		$created_by = $this->_user_session_data->user_id;
		
		$modified = date("Y-m-d H:i:s", time());
		
		$arrFields = array (
			"photo"					=> $image,
			"photo_type"			=> $image_type['mime'],
			"image_name"			=> $image_name,
			"modified"				=> $modified
		);
		
		//physically delete file from hard disk
		//unlink(IMAGE_UPLOADER_URL."/".$image_name);
		
		$result = $this->_db->update('images', $arrFields, "image_id=".$image_id);
		return $result;
	}
	
	public function get_images_by_category($arrParams)
	{
		if(isset($arrParams["institution_id"]))
		{
			$sql = '
			SELECT
				images.image_id
			FROM
				images
			INNER JOIN image_categories on images.image_category_id=image_categories.image_category_id
			WHERE
				image_categories.image_category_id = '.$arrParams["category_id"].'
				AND image_categories.institution_id = '.$arrParams["institution_id"].'
			ORDER BY images.created DESC';
			//print $sql; exit;			
			try{
				$result = $this->_db->fetchAll($sql);
			} catch (Zend_Exception $e){
				echo "There was an error: MI-GIBC-SHGD65";
				if(DEV_ENV == 'devel'){
					echo $sql;
					echo $e->getMessage();
				}
			}
			return $result;
		}
		//	AND image_categories.institution_id= '.$arrParams["institution_id"].'
		elseif(isset($arrParams["category_id"]))
		{
			$sql = '
			SELECT images.image_id FROM images			
			WHERE images.image_category_id = '.$arrParams["category_id"].'
			ORDER BY created DESC';
			try{
				$result = $this->_db->fetchAll($sql);
			} catch (Zend_Exception $e){
				echo "There was an error: MI-GIBC-SHGD65";
				if(DEV_ENV == 'devel'){
					echo $sql;
					echo $e->getMessage();
				}
			}
			return $result;
		}
	}
	
	public function get_images_by_institution_id($institution_id)
	{
		$sql = '
		SELECT * FROM images INNER JOIN image_categories
		ON images.image_category_id = image_categories.category_id
		WHERE image_category_id.institution_id = '.$institution_id.'
		ORDER BY images.image_name ASC';
		
		try{
			$result = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MI-GIBII-SJHD86";
			if(DEV_ENV == 'devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}
		
		return $result;
	}
	
	public function delete_image($image_id)
	{
		$sql = 'DELETE FROM images WHERE image_id='.$image_id;
		
		try{
			$result = $this->_db->query($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MI-DC-JHGD42";
			if(DEV_ENV == 'devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}
		
		
		return $result;
	}
	
	public function delete_image_category($category_id)
	{
		$sql1 = '
		DELETE FROM image_categories WHERE image_category_id = '.$category_id;
		
		$sql2 = '
		DELETE FROM images WHERE image_category_id = '.$category_id;
		
		try{
			$result = $this->_db->query($sql1);
		} catch (Zend_Exception $e){
			echo "There was an error: MI-DIC-GHSGD6";
			if(DEV_ENV == 'devel'){
				echo $sql1;
				echo $e->getMessage();
			}
		}
		
		try{
			$result = $this->_db->query($sql2);
		} catch (Zend_Exception $e){
			echo "There was an error: MI-DIC-LKSHG5";
			if(DEV_ENV == 'devel'){
				echo $sql2;
				echo $e->getMessage();
			}
		}
		
		return $result;
	}
	
	/*
	 * This function was made so stupid it makes me want to jump out my window...
	 */
	public function update_image_id($update_table, $primary_key, $image_id)
	{
		$arrUpdate = array('image_id' => $image_id);
		if(isset($update_table) && isset($image_id))
		{
			switch ($update_table){
				case 'prizes':
					$where = "prize_id=" . $primary_key;
					break;
				case 'users':
					$where = "user_id=" . $primary_key;
					break;
				case 'campaigns1':
					$arrUpdate = array('image_largemed' => $image_id);
					$where = "campaign_id=" . $primary_key;
					break;
				case 'campaigns2':
					$arrUpdate = array('image_smallmed' => $image_id);
					$where = "campaign_id=" . $primary_key;
					break;
				case 'campaigns3':
					$arrUpdate = array('image_achievement' => $image_id);
					$where = "campaign_id=" . $primary_key;
					break;
				case 'missions':
					$where = "mission_id=" . $primary_key;
					break;
				case 'tasks':
					$where = "task_id=" . $primary_key;
					break;
				case 'medals':
					$where = "medal_id=" . $primary_key;
					break;
				case 'institutions':
					$where = "institution_id=" . $primary_key;
					break;
				case 'ranks':
					$arrUpdate = array('rank_image_1' => $image_id,
									   'rank_image_2' => $image_id);
					$where = "rank_id=" . $primary_key;
					break;
				case 'medals1':
					$arrUpdate = array('medal_image_id' => $image_id);
					$where = "medal_id=" . $primary_key;
					break;
				case 'medals2':
					$arrUpdate = array('medal_image_id_2' => $image_id);
					$where = "medal_id=" . $primary_key;
					break;
				}
				//echo "table name: ".$update_table ." where: " . $where;
				//exit;
				$update_table = preg_replace("/[0-9]+$/", "", $update_table);
				$this->_db->update($update_table, $arrUpdate, $where);
		}
	}
	public function update_prize_images($arrUpdate)
	{
		if(is_array($arrUpdate))
		{
			if($arrUpdate["mode"] == "add")
			{
				$sqlUpdate = "UPDATE prizes set image_id=". $arrUpdate["image_id"]." WHERE prize_id=".$arrUpdate["prize_id"];				
				$result = $this->_db->query($sqlUpdate);	
			}
			if($arrUpdate["mode"] == "delete")
			{
				$sqlUpdate = "UPDATE prizes set image_id=null WHERE image_id=".$arrUpdate["image_id"]." AND prize_id=".$arrUpdate["prize_id"];
				//print $sqlUpdate; exit;
				$result = $this->_db->query($sqlUpdate);
			}
		return $result;
		}
	}
	
	
}
?>
