<?php 
require_once 'api/header/db.php';
require_once 'class.globalSettings.php';

class MyShliachHachayol {
	private $year;
	private $admins;
	private $children;
	private $sortedAdmins;
	private $checkForShipping;
	
	public function __construct( $forPickup = false, $id = 61 ) {
		$this->year = GlobalSettings::getRegistrationYear();
		$this->setInfo( $forPickup, $id );
		$this->checkForShipping = true;
	}

	public function setCheckForShipping($checkForShipping) {
		$this->checkForShipping = $checkForShipping;
	}

	public function getSql($gender = '') {
		$sql = "
			SELECT 
				a.*, a.first AS afirst, a.last AS alast, u.*
			FROM
				admins a
					JOIN
				admin_auths aa USING (admin_id)
					JOIN
				users u ON aa.id = u.user_id
					JOIN
				user_registration ur USING (user_id)
					JOIN
				hachayols_to_give htg ON htg.user_id = u.user_id
					AND htg.year = ur.year
			WHERE
				aa.auth = 'user' AND u.school_id = :school  
					AND u.user_registered > 0 
					AND ur.year = :year";
		if ($gender == 'M' || $gender == 'F') {
			$sql .= " AND u.gender = :gender";
		}
		return $sql;
	}
	
	private function setInfo( $forPickup, $id ) {
		global $MASHPIA_DB;
		
		$sql = $this->getSql();
		$result = $MASHPIA_DB->prepare($sql);
		$result->execute([
			'school' => $id,
			'year' => $this->year
		]);
		// $result->debugDumpParams();
 		$rows = $result->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows as $row) {
			if ($this->checkForShipping) {
				$shippingPaid = $this->paidForShipping($row['admin_id'], $id);
				if (
					($forPickup && $shippingPaid) || 
					(!$forPickup && !$shippingPaid)
				) continue;
			}
			$this->admins[$row['admin_id']] = $row;
			$this->children[$row['admin_id']][] = $row['first'] . ' ' . $row['last'];
		}
	}

	private function paidForShipping($admin_id, $school_id) {
		global $MASHPIA_DB;

		if ($school_id == 61) {
			$type = 'THMS%';
		} else if ($school_id == 269) {
			$type = 'THAK%';
		} else {
			return false;
		}
		$sql = "SELECT * FROM registration_charges WHERE type like :type and user_id in (
			SELECT id FROM admin_auths WHERE admin_id = :admin_id) and year = :year";
		$result = $MASHPIA_DB->prepare($sql);
		$result->execute(['admin_id' => $admin_id, 'year' => $this->year, 'type' => $type]);
		$rows = $result->fetchAll(PDO::FETCH_ASSOC);
		return count($rows) > 0;
	}
	
	public function getAdmins() {
		return $this->admins;
	}
	
	public function getChildren() {
		return $this->children;
	}
	
	public function sortByAddress() {
		foreach ($this->admins as $admin) {
			switch (strtolower(trim($admin['admin_country']))) {
				case '':
				case 'usa':
					$this->sortedAdmins[2][$admin['admin_id']] = $admin;
					break;
				case 'canada':
					$this->sortedAdmins[1][$admin['admin_id']] = $admin;
					break;
				default:
					$this->sortedAdmins[0][$admin['admin_id']] = $admin;
			}
		}		
	}
	
	public function getSortedAdmins() {
		return $this->sortedAdmins;
	}
}
?>