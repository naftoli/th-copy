<?php
/*
	// Table of contents
*/

class Import
{
	private $_db;
	private $_user_session_data;
	
	public function __construct()
	{
	   // Start the DB objects
	   $this->_db = Zend_Registry::get('db');
	   $this->_db->setFetchMode(Zend_Db::FETCH_OBJ);
	
	   // Start the session object
	   $this->_user_session_data = new Zend_Session_Namespace('user_session_data');
	   
	}
	
/**************************************************************************************************
	This functions are used ONLY during the import process where we migrate data from the old system
	into the new one.
**************************************************************************************************/
	
	/** Function insert classes from an imported classes_sql file.
	 * This file contains classes from the old database but for the current school year.
	 *
	 */
	public function inject_classes()
	{
		$file = fopen($_SERVER["DOCUMENT_ROOT"]."/SQL/old_classes.csv", 'r');		
		$this->_db->query("SET NAMES `UTF8`");
		$arrParams = array();
		$arrParams2 = array();
		while(($data = fgetcsv($file, 1000, ",")) !== false)
		{
			$class_id = $data[0];
			$class_id = preg_replace("/'/", "", $class_id);
			$arrParams["old_class_id"] = $class_id;
		
			$school_id = $data[1];
			$school_id = preg_replace("/'/", "", $school_id);
			$arrParams["institution_id"] = $school_id;
			
			$class_grade = $data[2];
			$class_grade = preg_replace("/'/", "", $class_grade);
			$arrParams["grade"] = $class_grade;
			
			$class_sub = $data[3];
			$class_sub = preg_replace("/'/", "", $class_sub);
			$arrParams["sub"] = $class_sub;
			
			$class_teacher = $data[4];
			$class_teacher = preg_replace("/'/", "", $class_teacher);			
			$arrParams["class_name"] = $class_teacher;
			
			$default_level = $data[5];
			$default_level = preg_replace("/'/", "", $default_level);
			
			$gender_view = $data[6];
			$gender_view = preg_replace("/'/", "", $gender_view);
			$arrParams["gender"] = "mixed";
			
			$arrParams["is_active"] = 1;
			$arrParams["created"] = date ("Y-m-d H:i:S");;
			$arrParams["modified"] = '';
			$arrParams["created_by"] = '';
			
			if($class_teacher !="class_teacher")
			{
				
				$intAI = $this->_db->insert("classes", $arrParams);
				if($intAI)
				{
					$new_class_id = $this->_db->lastInsertId();
				}
				if($arrParams["class_name"] != "")
				{
					$sqlStr = "
						SELECT
							users.user_id
						FROM
							users
						WHERE
							first_name='$class_teacher'";
				
					$arrResult = $this->_db->fetchRow($sqlStr);
						
					$arrParams2["class_id"] = $new_class_id;
					$arrParams2["user_id"] = $arrResult->user_id;
					$arrParams2["class_role"] = 'Teacher';
					$arrParams2["created"] = date ("Y-m-d H:i:S");;
					$arrParams2["modified"] = '';
					$arrParams2["created_by"] = '';
					
					if($arrParams2["user_id"]!="")
					{					
						$this->_db->insert("user_classes", $arrParams2);
					}
				}
			}
		}
	}
	
	
	public function importInstitutions()
	{
		$file = fopen($_SERVER["DOCUMENT_ROOT"]."/SQL/legacy_data/institutions.csv", 'r');		
		$this->_db->query("SET NAMES `UTF8`");
		
		$raw_data = 0;
		$inserted_data = 0;
			
		if(DEV_ENV == "staging")
		{
			//$this->_db->query("TRUNCATE TABLE institutions;");
			//$this->_db->query("TRUNCATE TABLE legacy_lookup;");
			$this->_db->query("INSERT INTO institutions (institution_type,host_id,network_id,name,is_active,address,city,state,country,website,created,created_by) values ('Host',0,0,'IMS Host',1,'5111 De Courtrai','Montreal','Quebec','Canada','http://www.mashpia.com',now(),1);");
			$host_id = $this->_db->lastInsertId();
			$this->_db->query("INSERT INTO institutions (institution_type,host_id,network_id,name,is_active,address,city,state,country,website,created,created_by) values ('Network',1,0,'IMS Network',1,'5111 De Courtrai','Montreal','Quebec','Canada','http://www.mashpia.com',now(),1);");
			$network_id = $this->_db->lastInsertId();
			while(($buffer = fgets($file)) !== false)
			{
				$data = explode("|", $buffer);
				$old_school_id = $data[0];
				$old_school_id = preg_replace("/'/", "", $old_school_id);
				//$arrParams["old_school_id"] = $old_school_id;
				
				$raw_data++;
			
				$name = $data[1];
				$name = preg_replace("/'/", "", $name);
				$arrParams["name"] = $name;
				
				$hebrew_name = $data[2];
				$hebrew_name = preg_replace('/"/', "", $hebrew_name);
				$arrParams["hebrew_name"] = $hebrew_name;
				
				$address = $data[13];
				$address = preg_replace("/'/", "", $address);
				$arrParams["address"] = $address;
				
				$city = $data[15];
				$city = preg_replace("/'/", "", $city);			
				$arrParams["city"] = $city;
				
				$state = $data[16];
				$state = preg_replace("/'/", "", $state);
				$arrParams["state"] = $state;
				
				$country = $data[17];
				$country = preg_replace("/'/", "", $country);
				$arrParams["country"] = $country;
				
				$postal = $data[18];
				$postal = preg_replace("/'/", "", $postal);
				$arrParams["postal"] = $postal;
				
				$phone = $data[19];
				$phone = preg_replace("/'/", "", $phone);
				$arrParams["phone"] = $phone;
				
				$arrParams["is_active"] = 1;
				
				$created = $data[44];
				$created = preg_replace("/'/", "", $created);
				$arrParams["created"] = $created;
				$arrParams["host_id"] = $host_id;
				$arrParams["network_id"] = $network_id;
				$arrParams["institution_type"] = "School";
				$arrParams["modified"] = date("Y-m-d H:i:S");
				$arrParams["created_by"] = "";
				if($old_school_id !='school_id')
				{
					$intAI = $this->_db->insert("institutions", $arrParams);
					if($intAI)
					{
						$new_school_id = $this->_db->lastInsertId();
						$inserted_data;
						
					}
					$arrParam2 = array();
					$arrParam2["legacy_id"] 	= $old_school_id;
					$arrParam2["ims_id"] 		= $new_school_id;
					$arrParam2["legacy_table"] 	= "schools";
					$arrParam2["ims_table"] 	= "institutions";
					
					$this->_db->insert("legacy_lookup", $arrParam2);
					$inserted_data++;
				}
				unset($arrParam2);
				unset($arrParams);
			}
		}
		else
		{
			print "Sorry, you cannot run this script.";
		}
		
		echo "INSTITUTIONS: created ".$inserted_data.' out of '.$raw_data.' raw records';
	}
	
	/**
	* Imports students and sets up permissions. Institution is hold in $data[]
	*
	*/
	public function importStudents()
	{
		$file = fopen($_SERVER["DOCUMENT_ROOT"]."/SQL/legacy_data/users_students.csv", 'r');  
		$this->_db->query("SET NAMES `UTF8`");
		
		$raw_data = 0;
		$inserted_data = 0;
		
		//clear all previous data	
		
		if(DEV_ENV == "staging"){
			//$this->_db->query("TRUNCATE TABLE users;");
			//$this->_db->query("TRUNCATE TABLE permissions;");
			while(($buffer = fgets($file)) !== false){
			
				
				$data = explode("|", $buffer);
				$raw_data++;
				
				if($data[0] == "user_id") continue;
				
				$legacy_user_id = $data[0];
				$legacy_institution_id = $data[11];
				$created = date("Y-m-d H:i:s", time());
				
				//skip students who don't have an institution_id assigned
				if($legacy_institution_id == '') continue;
				
				$state = $data[19];
				$country =  $data[21];
				$postal = $data[20];
				$phone = $data[22];
				//print_r($data);
				//echo $state. $country. $postal . $phone; exit;
				
				
				$email = (!empty($data[3])) ? $data[3] : "n/a";
				
				//print_r($data);
				$arrStudentInsert = array(	"email" 			=> 'n/a-'.$legacy_user_id,
											"password" 			=> 'n/a',
											"bar_code" 			=> "3" . $data[1],
											"first_name" 		=> $data[5],
											"last_name" 		=> $data[6],
											"hebrew_first_name"	=> $data[7],
											"hebrew_last_name"	=> $data[8],
											"is_active" 		=> 1,
											"address" 			=> $data[16] . ' ' . $data[17],
											"city" 				=> $data[18],
											"state" 			=> $state,
											"country" 			=> $country,
											"postal" 			=> $postal,
											"phone" 			=> $phone,
											"created"	 		=> $data[25]
											);
				
				//print_r($arrStudentInsert);
			
			
				$this->_db->beginTransaction();
				try{
					$this->_db->insert("users", $arrStudentInsert);
					$ims_user_id = $this->_db->lastInsertId();
					
					//create permissions
					$arrPermissionsInsert = array("user_id"				=> $ims_user_id,
												  "institution_id" 		=> $this->lookupId($legacy_institution_id, "institutions", "schools"),
												  "permission" 			=> "Student",
												  "default_permission" 	=> 1,
												  "created" 			=> $created
												  );
					
					//update lookup table
					$arrLookupInsert = array("legacy_id" 	=> $legacy_user_id,
											 "ims_id"		=> $ims_user_id,
											 "legacy_table"	=> "users",
											 "ims_table"	=> "users");
					
					$this->_db->insert("permissions", $arrPermissionsInsert);
					$this->_db->insert("legacy_lookup", $arrLookupInsert);
					$this->_db->commit();
					echo "inserted OK...<br />";
					$inserted_data++;
					
					
				} catch (Zend_Exception $e) {
					$this->_db->rollBack();
					echo $e->getMessage();
					echo " Legacy id: " . $legacy_user_id;
				}
			}
			
			
		} else {
			print "Sorry, you cannot run this script.";
		}
		
		echo "STUDENTS: Inserted ".$inserted_data." records from ".$raw_data." original records <br />";
	}
	
	/**
	* Imports admins and sets up permissions. Institution is hold in $data[]
	*
	*/
	public function importAdmins()
	{
		$file = fopen($_SERVER["DOCUMENT_ROOT"]."/SQL/legacy_data/old_admins_table.csv", 'r');  
		$this->_db->query("SET NAMES `UTF8`");
		
		//clear all previous data	
		
		if(DEV_ENV == "staging"){
			
			//$this->_db->query("TRUNCATE TABLE users");
			//$this->_db->query("TRUNCATE TABLE legacy_lookup");
			
			$raw_data		= 0;
			$inserted_data	= 0;
			while(($buffer = fgets($file)) !== false){
				
				$data = explode("|", $buffer);
				
				if($data[0] == "admin_id") continue;
				$raw_data++;
				
				$legacy_user_id = $data[0];				
				$created = date("Y-m-d H:i:s", time());
				
				//print_r($data);	
				
				$email = (!empty($data[17])) ? $data[17] : "n/a-".$legacy_user_id ;
				if($this->_emailIsDuplicate($email)) $email .= "_" . $legacy_user_id;
				
				$arrAdminsInsert = array(	"email" 			=> $email,
											"password" 			=> md5($data[3]),
											"bar_code" 			=> "",
											"first_name" 		=> $data[5],
											"last_name" 		=> $data[6],
											"hebrew_first_name"	=> "",
											"hebrew_last_name"	=> "",
											"is_active" 		=> 1,
											"address" 			=> $data[8] . ' ' . $data[9],
											"city" 				=> $data[10],
											"state" 			=> $data[11],
											"country" 			=> $data[13],
											"postal" 			=> $data[12],
											"phone" 			=> $data[15],
											"created"	 		=> $created
											);
				
				//print_r($arrAdminsInsert);// exit;
			
				
				$this->_db->beginTransaction();
				try{
					$this->_db->insert("users", $arrAdminsInsert);
					$ims_user_id = $this->_db->lastInsertId();
					
					//update lookup table
					$arrLookupInsert = array("legacy_id" 	=> $legacy_user_id,
											 "ims_id"		=> $ims_user_id,
											 "legacy_table"	=> "admins",
											 "ims_table"	=> "users");
					
					$this->_db->insert("legacy_lookup", $arrLookupInsert);
					$this->_db->commit();
					echo "inserted OK...<br />";
					$inserted_data++;
					
					
				} catch (Zend_Exception $e) {
					$this->_db->rollBack();
					echo $e->getMessage();
					echo " Legacy id: " . $legacy_user_id;
				}
				
			}
			
			
		} else {
			print "Sorry, you cannot run this script.";
		}
		
		echo "ADMINS: Inserted ".$inserted_data." records from ".$raw_data." original records <br />";
	}
	
	public function importParents()
	{
		$file = fopen($_SERVER["DOCUMENT_ROOT"]."/SQL/legacy_data/users_parents.csv", 'r');  
		$this->_db->query("SET NAMES `UTF8`");
		
		$raw_data = 0;
		$inserted_data = 0;
		
		
		if(DEV_ENV == "staging"){
			
			while(($buffer = fgets($file)) !== false){
				
				$data = explode("|", $buffer);
				
				if($data[0] == "admin_id") continue;
				
				$legacy_user_id = $data[0];				
				$created = date("Y-m-d H:i:s", time());
				$raw_data++;
				
				
				$email = (!empty($data[17])) ? $data[17] : "n/a-".$legacy_user_id ;
				if($this->_emailIsDuplicate($email)) $email .= "_" . $legacy_user_id;		
				
				$arrStudentInsert = array(	"email" 			=> $email,
											"password" 			=> md5($data[3]),
											"bar_code" 			=> "",
											"first_name" 		=> $data[5],
											"last_name" 		=> $data[6],
											"hebrew_first_name"	=> "",
											"hebrew_last_name"	=> "",
											"is_active" 		=> 1,
											"address" 			=> $data[8] . ' ' . $data[9],
											"city" 				=> $data[10],
											"state" 			=> $data[11],
											"country" 			=> $data[13],
											"postal" 			=> $data[12],
											"phone" 			=> $data[15],
											"created"	 		=> $created
											);
				
				//print_r($arrStudentInsert);// exit;
				
				$this->_db->beginTransaction();
				try{
					$this->_db->insert("users", $arrStudentInsert);
					$ims_user_id = $this->_db->lastInsertId();
					
					//update lookup table
					$arrLookupInsert = array("legacy_id" 	=> $legacy_user_id,
											 "ims_id"		=> $ims_user_id,
											 "legacy_table"	=> "admins",
											 "ims_table"	=> "users");
					
					$this->_db->insert("permissions", $arrPermissionsInsert);
					$this->_db->insert("legacy_lookup", $arrLookupInsert);
					$this->_db->commit();
					echo "inserted OK...<br />";
					$inserted_data++;
					
					
				} catch (Zend_Exception $e) {
					$this->_db->rollBack();
					echo $e->getMessage();
					echo " Legacy id: " . $legacy_user_id;
				}
				
			}
			
			
		} else {
			print "Sorry, you cannot run this script.";
		}
		
		echo "PARENTS: Inserted ".$inserted_data." records from ".$raw_data." original records <br />";
	}
	
	/**
	 * Insert into relationship table parent child relationship
	 */
	public function importParentsChildrenRelationship()
	{
		$file = fopen($_SERVER["DOCUMENT_ROOT"]."/SQL/old_parents.csv", 'r');  
		$this->_db->query("SET NAMES `UTF8`");
		$raw_data = 0;
		$inserted_data = 0;
		if(DEV_ENV == "staging")
		{
			while(($buffer = fgets($file)) !== false)
			{
				$data = explode(",", $buffer);
				
				if($data[0] == "admin_id") continue;
				
				$legacy_parent_id = $data[0];
				$legacy_child_id = $data[2];
				$parent_id 			= $this->lookupId($legacy_parent_id, "users", "admins");
				$child_id 	= $this->lookupId($legacy_child_id, "users", "users");
				//print "parent_id: " . $parent_id. " child_id: " . $child_id ."<br />";
				
				$created = date("Y-m-d H:i:s", time());
				$raw_data++;
				$this->_db->beginTransaction();
				try
				{
					//create an array with data for insertion into realtionships table
					$arrRelationshipsInsert = array(
												"user_id" 			=> $parent_id,
												"relation_id"		=> $child_id,
												"relationship"		=> "parent",
												"created"			=> $created
											);
					//var_dump($arrRelationshipsInsert); exit;
					$this->_db->insert("relationships", $arrRelationshipsInsert);
					$this->_db->commit();
					echo "inserted OK...<br />";
					$inserted_data++;
				}
				catch (Zend_Exception $e)
				{
					$this->_db->rollBack();
					echo $e->getMessage();
					echo " Legacy id: " . $legacy_user_id;
				}
				
			}
		}
		else
		{
			print "Sorry, you cannot run this script.";
		}
		echo "PARENTS: Inserted ".$inserted_data." records from ".$raw_data." original records <br />";
	}
	
	/**
	 * Inserts permissions for principals
	 */
	public function importPermissions()
	{
		$file = fopen($_SERVER["DOCUMENT_ROOT"]."/SQL/legacy_data/permissions_principals.csv", 'r');  
		$this->_db->query("SET NAMES `UTF8`");
		
		
		if(DEV_ENV == "staging"){
			//clear all previous data
			//$this->_db->query("TRUNCATE TABLE users;");
			//$this->_db->query("TRUNCATE TABLE permissions;");
			
			
			$raw_data = 0;
			$inserted_data = 0;
			
			while(($buffer = fgets($file)) !== false){
				$raw_data++;
				$data = explode("|", $buffer);
				
				if($data[0] == "user_id") continue;
				
				$user_id 			= $this->lookupId($data[0], "users", "admins");
				$institution_id 	= $this->lookupId($data[1], "institutions", "schools");
				
				
				
				
				$created = date("Y-m-d H:i:s", time());
				
				if($user_id == '' || $institution_id == '') continue;
				//create permissions
				$arrPermissionsInsert = array("user_id"				=> $user_id,
											  "institution_id" 		=> $institution_id,
											  "permission" 			=> "Institution Administrator",
											  "default_permission" 	=> 1,
											  "created" 			=> $created
											  );
				
				$this->_db->beginTransaction();
				try{
					$this->_db->insert("permissions", $arrPermissionsInsert);
					$this->_db->commit();
					echo "inserted OK...<br />";
					$inserted_data++;
					
				} catch (Zend_Exception $e) {
					$this->_db->rollBack();
					echo $e->getMessage();
					echo " Legacy id: " . $legacy_user_id;
				}
				
			}
			
			
		} else {
			print "Sorry, you cannot run this script.";
		}
		
		echo "PERMISSIONS: Inserted ".$inserted_data,' permission records out of '.$raw_data.' raw records <br />';
	}
	
	/**
	 * Creates classes and associated teachers. If teachers don't exist, they will be
	 * created and defualt permissions will be assigned. Classes and associated teachers
	 * have to be created at the same time so we can preserve the association
	 */
	public function importClasses()
	{
		$file = fopen($_SERVER["DOCUMENT_ROOT"]."/SQL/legacy_data/classes_with_teachers.csv", 'r');  
		$this->_db->query("SET NAMES `UTF8`");
		
		$raw_data = 0;
		$inserted_data = 0;
		
		
		if(DEV_ENV == "staging"){
			//clear all previous data
			//$this->_db->query("TRUNCATE TABLE users;");
			//$this->_db->query("TRUNCATE TABLE permissions;");
			while(($buffer = fgets($file)) !== false){
				
				$data = explode("|", $buffer);
				
				if($data[0] == "class_id") continue;
							
				$created = date("Y-m-d H:i:s", time());
				$raw_data++;
				
				//insert user
				$this->_db->beginTransaction();
				try{
					//	check if duplicate exist
					$sql = 'SELECT * FROM users WHERE last_name = "'.$data[4].'"';
					$result = $this->_db->fetchRow($sql);
					
					//lookup institution id
					$institution_id = $this->lookupId($data[1], "institutions", "schools");
						
					if($result){
						//		[YES] get user_id
						$teacher_id = $result->user_id;
					}else{
						// 		[NO] insert => get last insert id
						$arrInsertTeacher = array("last_name"	=> $data[4],
												  "created"		=> $created);
						$this->_db->insert("users", $arrInsertTeacher);
						$teacher_id = $this->_db->lastInsertId();
						
						
						
						//create permission
						$arrPermission = array(	"user_id"				=> $teacher_id,
												"permission"			=> "Teacher",
												"institution_id"		=>	$institution_id,
												"default_permission"	=> 1,
												"created"				=> $created);
						
						$this->_db->insert("permissions", $arrPermission);
					}
					
					// insert class
					$arrInsertClass = array("institution_id"	=> $institution_id,
											"grade"				=> $data[2],
											"sub"				=> $data[3],
											"gender"			=> "mixed",
											"is_active"			=> 1,
											"created"			=> $created);
					
					$this->_db->insert("classes", $arrInsertClass);
					$class_id = $this->_db->lastInsertId();
					
					//insert class into legacy_lookup table
					$arrInsertLegacyData = array("legacy_id"	=> $data[0],
												 "ims_id"		=> $class_id,
												 "legacy_table"	=> "classes",
												 "ims_table"	=> "classes");
					
					$this->_db->insert("legacy_lookup", $arrInsertLegacyData);
					
					if($data[4] != "No Teacher" || !empty($data[4])){
						//insert user_classes with user_id & class_id
						$arrInsertUserClasses = array("class_id"	=> $class_id,
												  "user_id"			=> $teacher_id,
												  "class_role"		=> "Teacher",
												  "created"			=> $created);
						
						$this->_db->insert("user_classes", $arrInsertUserClasses);
					}
					
					
					$this->_db->commit();
					echo "inserted OK...<br />";
					$inserted_data++;
					
					
				} catch (Zend_Exception $e) {
					$this->_db->rollBack();
					echo $e->getMessage();
					echo "Error inserting classes";
				}
				
			}
			
		} else {
			print "Sorry, you cannot run this script.";
		}
		
		echo "CLASSES: Inserted ".$inserted_data." records from ".$raw_data." original records <br />";
	}
	
	public function importStudentClasses()
	{
		$file = fopen($_SERVER["DOCUMENT_ROOT"]."/SQL/legacy_data/users_students.csv", 'r');  
		$this->_db->query("SET NAMES `UTF8`");
		
		$raw_data = 0;
		$inserted_data = 0;
		
		if(DEV_ENV == "staging"){
			while(($buffer = fgets($file)) !== false){				
				$data = explode("|", $buffer);
				
				if($data[0] == "user_id") continue;
				//print_r($data); continue;
				
				
				$created = date("Y-m-d H:i:s", time());
				$raw_data++;
				
				$this->_db->beginTransaction();
				try{
					
						$user_id = $this->lookupId($data[0], "users", "users");
						$institution_id = $this->lookupId($data[11], "institutions", "schools");
						$class_id = $this->lookupId($data[12], "classes", "classes");
						//echo 'legacy data: user_id: ' . $user_id .' school_id: '. $institution_id .' class_id: ' .$class_id . '<br />';// exit;
						
						
						$arrInsertUserClasses = array(  "class_id"		=> $class_id,
														"user_id"		=> $user_id,
														"class_role"	=> "Student",
														"created"		=> $created);
						//var_dump($arrInsertUserClasses);// exit;
						$this->_db->insert("user_classes", $arrInsertUserClasses);
						
						//insert user_classes into legacy_lookup table
						$arrInsertLegacyData = array("legacy_id"	=> $data[11],
													 "ims_id"		=> $class_id,
													 "legacy_table"	=> "users",
													 "ims_table"	=> "user_classes");
						
						//var_dump($arrInsertLegacyData); exit;
						$this->_db->insert("legacy_lookup", $arrInsertLegacyData);
						$this->_db->commit();
						echo "inserted OK...<br />";
						$inserted_data++;
					
				} catch (Zend_Exception $e) {
					$this->_db->rollBack();
					echo $e->getMessage();
					echo "Error inserting user-classes";
				}				
			}			
		} else {
			print "Sorry, you cannot run this script.";
		}
		echo "USER_CLASSES: Inserted ".$inserted_data." records from ".$raw_data." original records <br />";
	}
	public function importPartialStudentClasses()
	{
		$file = fopen($_SERVER["DOCUMENT_ROOT"]."/SQL/legacy_data/old_students_Shaloh_Boston_School.csv", 'r');  
		$this->_db->query("SET NAMES `UTF8`");
		
		$raw_data = 0;
		$inserted_data = 0;
		
		if(DEV_ENV == "staging"){
			while(($buffer = fgets($file)) !== false){				
				$data = explode("|", $buffer);
				
				if($data[0] == "class_id") continue;
				//print_r($data); continue;
				
				
				$created = date("Y-m-d H:i:s", time());
				$raw_data++;
				
				$this->_db->beginTransaction();
				try{
					
						$user_id = $this->lookupId($data[1], "users", "users");
						$class_id = $this->lookupId($data[0], "classes", "classes");
						//echo 'legacy data: user_id: ' . $user_id .' school_id: '. $institution_id .' class_id: ' .$class_id . '<br />'; exit;
						
						
						$arrInsertUserClasses = array(  "class_id"		=> $class_id,
														"user_id"		=> $user_id,
														"class_role"	=> "Student",
														"created"		=> $created);
						//var_dump($arrInsertUserClasses);// exit;
						$this->_db->insert("user_classes", $arrInsertUserClasses);						
						$this->_db->commit();
						echo "inserted OK...<br />";
						$inserted_data++;
					
				} catch (Zend_Exception $e) {
					$this->_db->rollBack();
					echo $e->getMessage();
					echo "Error inserting user-classes";
				}				
			}			
		} else {
			print "Sorry, you cannot run this script.";
		}
		echo "USER_CLASSES: Inserted ".$inserted_data." records from ".$raw_data." original records <br />";
	}
	
	/*
	  This function looks up information in our lookup table and returns the
	  corresponding id
	*/
	function lookupId($legacy_id, $ims_table, $legacy_table)
	{
		$sql = '
		SELECT * FROM legacy_lookup
		WHERE legacy_id = '.$legacy_id.'
		AND ims_table = "'.$ims_table.'"
		AND legacy_table = "'.$legacy_table.'"';
		//echo $sql; exit;
		$result = $this->_db->fetchRow($sql);
		if($result){
			return $result->ims_id;
		}
		//echo "result is: : " . $result->ims_id . " .$sql <hr />"; 
		return 0;
	}
	
	private function _emailIsDuplicate($email)
	{
		$sql = 'SELECT * FROM users WHERE users.email = "'.$email.'"';
		$result = $this->_db->fetchAll($sql);
		
		return ($result) ? true : false;
	}
	
	public function truncate($table)
	{
		if(DEV_ENV == 'staging'){
			$this->_db->query('TRUNCATE TABLE ' . $table);
		}else{
			echo 'You dont have permission to truncate this table';
		}		
	}
	
	/**
	 * Imports images forma remote server
	 *
	 * @return
	 */
	public function import_image($legacy_user_id, $image_category_id)
	{
		set_time_limit(86400); // 24 hours
		$filename = "http://mashpia.com/file_view_new.php?user_id=".$legacy_user_id;
		$image_type = getimagesize($filename);
		print "user_id=".$legacy_user_id ."<br/ >";
		$handle = fopen($filename, "rb");
		if (strpos($http_response_header[0], '404') !== false){
			$result['success'] = false;
			$result['image_id'] = NULL;
			$result['message'] = "Error: Document not found. <br />";
			return $result;
		}
		
		$image = stream_get_contents($handle);
		fclose($handle);
		
		$date = date("Y-m-d H:i:s", time());
		$created_by = $this->_user_session_data->user_id;
		
		$arrFields = array (
			"photo"					=> $image,
			"photo_type"			=> $image_type['mime'],
			"image_name"			=> "import_".$legacy_user_id.".jpg",
			"image_category_id"		=> $image_category_id,
			"created"				=> $date,
			"created_by"			=> $created_by
		);
		
		try{
			$this->_db->insert('images', $arrFields);
			$result['success'] = true;
			$result['image_id'] =$this->_db->lastInsertId();
			$result['message'] = "SUCCESS: Photo inserted. <br />";
		}catch(Zend_Exception $e ){
			$result['success'] = false;
			$result['image_id'] = NULL;
			$result['message'] = $e->getMessage() . "<br />";
		}
		
		return $result;
	}
	
	/**
	 * Gets student who have no picture in the system and returns array
	 */
	public function getStudents()
	{
		set_time_limit(86400); // 24 hours
		//truncate tables images and image_categories
		$sql = '
		SELECT * FROM users INNER JOIN permissions
		ON users.user_id = permissions.user_id
		LEFT JOIN images
		ON users.image_id = images.image_id
		WHERE users.image_id IS NULL';
		
		$arrResult = $this->_db->fetchAll($sql);
		//loop students
		foreach($arrResult as $objResult)
		{
			//get student id from the legacy_lookup
			$strSqlLegacyLookup = "
				SELECT
					legacy_id
				FROM
					legacy_lookup
				WHERE
					ims_id=".$objResult->user_id." 
					AND legacy_table='users'
					AND ims_table='users'";
			
			$arrResult3 = $this->_db->fetchRow($strSqlLegacyLookup);
			$legacy_user_id = $arrResult3->legacy_id;
			$image_category_id = $this->_create_image_category($objResult->institution_id);
			
			$result = $this->import_image($legacy_user_id, $image_category_id);
			if($result['success']){
				$arrUpdateStudent = array("image_id"	=> $result['image_id']);
				$this->_db->update("users", $arrUpdateStudent, "user_id = ".$objResult->user_id);
				echo $result['message'];
			}else{
				echo $result['message'];
			}
		}
	}
	
	public function getInstitutions()
	{
		set_time_limit(86400); // 24 hours
		$sqlInstitutions = "Select institution_id from institutions where institution_type!='Host' and institution_type!='Network'";
		$arrResult = $this->_db->fetchAll($sqlInstitutions);
		//loop students
		foreach($arrResult as $objResult)
		{
			//get old institution id from the legacy_lookup
			$strSqlLegacyLookup = "
				SELECT
					legacy_id
				FROM
					legacy_lookup
				WHERE
					ims_id=".$objResult->institution_id." 
					AND legacy_table='schools'
					AND ims_table='institutions'";
			$arrResult2 = $this->_db->fetchRow($strSqlLegacyLookup);
			$legacy_institution_id = $arrResult2->legacy_id;
			$image_category_id = $this->_create_image_category($objResult->institution_id);
			
			$result = $this->import_institution_image($legacy_institution_id, $image_category_id);
			if($result['success']){
				$arrUpdateStudent = array("image_id"	=> $result['image_id']);
				$this->_db->update("institutions", $arrUpdateStudent, "institution_id = ".$objResult->institution_id);
				echo $result['message'];
			}else{
				echo $result['message'];
			}
		}
	}
	public function import_institution_image($legacy_institution_id, $image_category_id)
	{
		set_time_limit(86400); // 24 hours
		$filename = "http://mashpia.com/file_view_schools.php?school_id=".$legacy_institution_id;
		$image_type = getimagesize($filename);
		print "institution_id=".$legacy_institution_id ."<br/ >";
		$handle = fopen($filename, "rb");
		if (strpos($http_response_header[0], '404') !== false){
			$result['success'] = false;
			$result['image_id'] = NULL;
			$result['message'] = "Error: Document not found. <br />";
			return $result;
		}
		
		$image = stream_get_contents($handle);
		fclose($handle);
		
		$date = date("Y-m-d H:i:s", time());
		$created_by = $this->_user_session_data->user_id;
		
		$arrFields = array (
			"photo"					=> $image,
			"photo_type"			=> $image_type['mime'],
			"image_name"			=> "import_institution_".$legacy_institution_id.".jpg",
			"image_category_id"		=> $image_category_id,
			"created"				=> $date,
			"created_by"			=> $created_by
		);
		
		try{
			$this->_db->insert('images', $arrFields);
			$result['success'] = true;
			$result['image_id'] =$this->_db->lastInsertId();
			$result['message'] = "SUCCESS: Photo inserted. <br />";
		}catch(Zend_Exception $e ){
			$result['success'] = false;
			$result['image_id'] = NULL;
			$result['message'] = $e->getMessage() . "<br />";
		}		
		return $result;
	}
	
	/**
	 * This function is called from within the class and handles image category
	 * creation for student pictures. It checks if it exists for the given institution
	 * , returns category_id, else it creates the category and returns last insert id
	 *
	 * @param int $institution_id
	 * @return int $image_category_id
	 */
	private function _create_image_category($institution_id)
	{
		$strSqlImageCategory = "
				SELECT * FROM image_categories
				WHERE institution_id=". $institution_id." AND name = 'Institution Pictures'";
			
		$arrResult = $this->_db->fetchRow($strSqlImageCategory);
		
		if($arrResult){
			$image_category_id = $arrResult->image_category_id;
		}else{
			$date = date("Y-m-d H:i:s", time());
			$arrInsertImageCategory = array(
							"institution_id"	=> $institution_id,
							"name"				=> "Institution Pictures",
							"created"			=> $date,
							"modified"			=> "",
							"created_by"		=> $this->_user_session_data->user_id
			);
			$this->_db->insert("image_categories", $arrInsertImageCategory);
			$image_category_id = $this->_db->lastInsertId();
		}
		
		return $image_category_id;
	
	}
	
	public function importUserAddons()
	{
		$strSelect = "
			SELECT
				*
			FROM
				user_extended_info
			INNER JOIN permissions ON
				user_extended_info.user_id=permissions.user_id";
		$arrResult = $this->_db->query($strSelect);
		//var_dump($arrResult);
		foreach($arrResult as $objResult)
		{
			if($objResult->add_on_one==1)
			{
				$intPackageItemId=3;
				$strPackageItemName='Store';
				$intPrice = 14;
				$strInsert = "
				INSERT INTO student_purchases values('',$objResult->institution_id, $objResult->user_id,$intPackageItemId,'$strPackageItemName',$intPrice,'$objResult->user_registered','','')";
				//print $strInsert. "<br />";// exit;
				$boolInsert = $this->_db->query($strInsert);
			}
			if($objResult->add_on_two==1)
			{
				$intPackageItemId=6;
				$strPackageItemName='Album';
				$intPrice = 10;
				$strInsert = "
				INSERT INTO student_purchases values('',$objResult->institution_id, $objResult->user_id,$intPackageItemId,'$strPackageItemName',$intPrice,'$objResult->user_registered','','')";
				//print $strInsert. "<br />"; //exit;
				$boolInsert = $this->_db->query($strInsert);
			}
			if($objResult->user_registration_fee > 0)
			{
				$intPackageItemId=5;
				$strPackageItemName='Registration';
				$intPrice = 36;
				$strInsert = "
				INSERT INTO student_purchases values('',$objResult->institution_id, $objResult->user_id,$intPackageItemId,'$strPackageItemName',$intPrice,'$objResult->user_registered','','')";
				//print $strInsert. "<br />"; //exit;
				$boolInsert = $this->_db->query($strInsert);
			}
			print "INSERT OK.<br/>";
		}
	}
	public function importUserRanks()
	{
		$objUserRanks = new Utilities();
		if(DEV_ENV == 'staging' || DEV_ENV == 'production')
		{
			return $objUserRanks->move_user_ranks();
		}
		else
		{
			echo 'You dont have permission to run this script';
		}	
	}
	public function import_parents()
	{
		if(DEV_ENV == 'staging' || DEV_ENV == 'production')
		{
			//get Parents from the relationship table and crate a permission 'Parent' for them in the permissions table
			$strSql = "Select * from relationships where relationship='Parent'";
			$result = $this->_db->fetchAll($strSql);
			$date = date("Y-m-d H:i:s", time());
			$counter =0;
			foreach($result as $objParent)
			{
				//get school_id for a each child that a parent is accosiated with, and associate a parent with that school_id
				$sqlInstitutionSearch = "Select institution_id from permissions where user_id=".$objParent->relation_id;
				$arrResultInstitution = $this->_db->fetchRow($sqlInstitutionSearch);			
				// prepare an array for insertion into permissions table
				$arrInsertParentPermission = array(
							"user_id" 				=> $objParent->user_id,
							"permission"			=> $objParent->relationship,
							"institution_id" 		=> $arrResultInstitution->institution_id,
							"default_permission"	=> 1,
							"created"				=> $date,
							"created_by"			=> $this->_user_session_data->user_id				
						);
				//check first if a Parent permission for a user_id exists, if not then insert
				$sqlCheckDuplicatePermission = "Select * from permissions where permission='Parent' and user_id=".$objParent->user_id;
				$arrResultDuplicate = $this->_db->fetchRow($sqlCheckDuplicatePermission);
				if(empty($arrResultDuplicate))
				{
					// insert a record into permissions table
					//var_dump($arrInsertParentPermission);
					$this->_db->insert("permissions", $arrInsertParentPermission);
					$permission_id = $this->_db->lastInsertId();
					print "permission Parent with permission id: " .$permission_id . " was inserted<br/>";
					$counter++;
				}
				else{
					echo "Sorry this permission already exists</br>";
				}
			}
			echo "inserted: ". $counter. " records as Parent";
		}
		else
		{
			echo 'You dont have permission to run this script';
		}	
	}
}
?>
