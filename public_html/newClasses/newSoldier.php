<?
class NewSoldier {
	
	private $first;
	private $last;
	private $dob;
	private $gender;
	private $school;
	private $grade;
	private $parent;
	private $user_id;
	private $fnameh;
	private $lnameh;
	private $photo;
	private $mobile;
	private $lang;
	private $setBD;
	
	public function __construct($parent, $first, $last, $dob, $gender, $school, $grade, $fnameh = '', $lnameh = '', $photo = null, $mobile = false, $setBD = true) {
		$this->parent = $parent;	
		$this->first = ucwords(strtolower($first));
		$this->last = ucwords(strtolower($last));
		$this->dob = $dob;
		$this->gender = $gender;
		$this->school = $school;
		$this->grade = $grade;
		$this->fnameh = $fnameh;
		$this->lnameh = $lnameh;
		$this->photo = $photo;
		$this->mobile = $mobile;
		$this->lang = 1;
		$this->setBD = $setBD;
	}
	
	public function setLang( $lang ) {
		$this->lang = $lang;
	}
	
	public function create() {
		//return $this->createAccount();
		if ($this->user_id = $this->createAccount()) {
			if ($this->assignToParent() && $this->setupStudent()) {
				return true;
			}
		} 
		return false;
	}
	
	private function createAccount() {
		$barcode = $this->generateCode();
		$serial = $this->generateSerial();
		$email = $this->parent->admin_email;
		switch ($this->gender) {
			case 'm':
			case 'M':
				$school_type_id = 2;
				break;
			case 'f':
			case 'F':
				$school_type_id = 3;
				break;
		}
		$address1 = $this->parent->admin_address1;
		$address2 = $this->parent->admin_address2;
		$city = $this->parent->admin_city;
		$state = $this->parent->admin_state;
		$zip = $this->parent->admin_postal;
		$country = $this->parent->admin_country; 
		
		$sql = "insert into users 
				set user_code = $barcode, 
				email = '$email', 
				first = '$this->first', 
				last = '$this->last', 
				lang = 'en', 
				school_type_id = $school_type_id, 
				school_id = $this->school, 
				class_id = $this->grade, 
				user_serial = $serial, 
				user_address1 = '$address1', 
				user_address2 = '$address2', 
				user_city = '$city', 
				user_state = '$state', 
				user_postal = '$zip', 
				user_country = '$country', 
				gender = '" . strtoupper($this->gender) . "', 
				dob = '$this->dob', 
				first_he = '$this->fnameh', 
				last_he = '$this->lnameh',
				he_name = '$this->fnameh $this->lnameh', 
				lang_id = " . $this->lang;
		if (!is_null($this->photo)) {
			if ($this->mobile) {
				$sql .= ", mobile_pic = '" . $this->photo . "' ";
			} else {
				$sql .= ", user_photo_id = " . $this->photo;
			}
		}
		//echo $sql; exit;
		//return $sql;
		if (@mysql_query($sql)) {
			return mysql_insert_id();
		} else {
			return 0;
		}
	}

	private function assignToParent() {
		$sql = "insert into admin_auths 
				set admin_id = " . $this->parent->admin_id . ", 
				auth = 'user', 
				id = " . $this->user_id . ", 
				role_id = 1";
		if (@mysql_query($sql)) {
			return true;
		} else {
			return false;
		}
	}
	
	private function setupStudent() {
		/*
		$level = null;
		if ($this->grade > 0) {	
			$year = "select class_grade from classes where class_id = $this->grade";
			$year_res = mysql_query($year);
			$row = mysql_fetch_row($year_res);
			$y = $row[0];
			switch ($y) {
				case 'Pre1a':
					$level = 6;
					break;
				case '1':
					$level = 7;
					break;
				case '2':
					$level = 8;
					break;
				case '3':
					$level = 9;
					break;
				case '4':
					$level = 10;
					break;
				case '5':
					$level = 11;
					break;
				case '6':
					$level = 12;
					break;
				case '7':
					$level = 13;
					break;
				case '8':
					$level = 14;
					break;
				default:
					$level = 6;
					break;
			}
		} else {
			//figure out age and set level by age
			$d1 = new DateTime();
			$d2 = new DateTime($this->dob);
			$age = $d2->diff($d1);
			$level = $age->format('%y');
			if ($level < 6) $level = 6;
			if ($level > 14) $level = 14;		
		}
    				
		if (!is_null($level)) {
			//get all subjects
			$sbj = "select * from subjects where subject_type NOT IN ('school_points', 'home_points')";
			$sub_res = mysql_query($sbj);
			$subjects = array();
			while ($subject = mysql_fetch_assoc($sub_res)) {
				$subjects[] = $subject['subject_id'];
			}
			foreach ($subjects as $subject) {
	        	$track_id = 1;
				if ($subject == 1) {
					$track_id = 5;
				}
	            $ins = "insert into user_tracks values ($this->user_id, $subject, $track_id, $level, 1)";
	            if (!@mysql_query($ins)) {
	            	return false;
	            }
	        }			        
		} else {
			return false;
		}
        */
		/*
		//create private rank for soldier 
        $jd = unixtojd();
        $sql = "insert into rank_marks 
        		set rank_ord = 1, 
        		user_id = $this->user_id, 
        		date_promoted = $jd";
        if (!@mysql_query( $sql )) {
        	return false;
        }
		*/
		
		if ($this->setBD) {
			//add birthday mission/task
			chdir('/'); // make sure we are in root dir
			require_once 'class.birthday.php';
			$b = new Birthday( $this->user_id );
			$b->setBirthday();
			require_once 'class.birthdayYi.php';
			$bi = new BirthdayYi( $this->user_id );
			$bi->setBirthday();
			
			//set dob for syncing with wp
			require_once 'class.heDob.php';
			$hdob = new HeDob( $this->user_id );
			$hdob->setHeDob();
		}
        return true;
	}
	
	private function generateCode() {
		if (mysql_result(mq("SELECT GET_LOCK('users', 30)"),0) != 1) 
			trigger_error('could not get lock', E_USER_ERROR);
			
		$count = 0;
		do {
			if ($count++ > 100000) 
				trigger_error('could not get ID', E_USER_ERROR);
			$user_code = mysql_result(mq('SELECT FLOOR(RAND() * 9223372036854775807)'),0);
		} while (mysql_result(mq("SELECT COUNT(*) FROM users WHERE user_code = $user_code"),0) != 0);
		return $user_code;
	}
	
	private function generateSerial() {
		$sql = "select user_serial from users order by user_serial desc limit 1";
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$serial = $row['user_serial'];
		$serial++;
		return $serial;
	}
	
	public function getUserID() {
		return $this->user_id;
	}
}
?>