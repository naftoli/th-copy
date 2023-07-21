<?php
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
	private $school_type;
	
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
		$this->school_type = 0;
	}
	
	public function setLang( $lang ) {
		$this->lang = $lang;
	}

	public function setSchoolType( $type ) {
	    $this->school_type = $type;
    }
	
	public function create() {
		//return $this->createAccount();
		if ($this->user_id = $this->createAccount()) {
            $this->setupTracks();
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
		if ($this->gender) {
            switch ($this->gender) {
                case 'm':
                case 'M':
                    $this->school_type = 2;
                    break;
                case 'f':
                case 'F':
                    $this->school_type = 3;
                    break;
            }
        } else {
		    if (!isset($this->school_type)) $this->school_type = 50;
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
				school_type_id = $this->school_type, 
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
		if (@mysql_query($sql)) {
			return mysql_insert_id();
		} else {
			return 0;
		}
	}

    private function setupTracks() {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/class.campaignEnrollment.php';
        try {
            $c = new CampaignEnrollment($this->user_id);
            $c->enroll();
        } catch (EnrollmentException $e) {}
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
		if ($this->setBD) {
			//add birthday mission/task
			require_once( dirname(__FILE__) . '/../class.birthdayEn.php' );
			$b = new BirthdayEn( $this->user_id );
			$b->setBirthday();
			require_once( dirname(__FILE__) . '/../class.birthdayYi.php' );
			$bi = new BirthdayYi( $this->user_id );
            $bi->setBirthday();
            require_once( dirname(__FILE__) . '/../class.birthdayHe.php' );
            $bh = new BirthdayHe( $this->user_id );
            $bh->setBirthday();
			
			//set dob for syncing with wp
			require_once( dirname(__FILE__) . '/../class.heDob.php' );
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