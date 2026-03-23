<?php 
require_once 'api/header/db.php';
require_once 'class.globalSettings.php';

class MyShliachHachayol {
	private $year;
	private $admins;
	private $children;
	private $numChildren;
	private $sortedAdmins;
	private $checkPaidForShipping;
	private $checkIfAlreadyShipped;
	
	public function __construct( $forPickup = false, $id = 61, $checkIfAlreadyShipped = false, $checkPaidForShipping = true ) {
		// we only check if already shipped for shipment 1 and ms/ak catchup
		$this->checkIfAlreadyShipped = $checkIfAlreadyShipped;
		$this->checkPaidForShipping = $checkPaidForShipping;
		$this->year = GlobalSettings::getCurrentYear();
		$this->setInfo( $forPickup, $id ); 
	}

	public function setCheckPaidForShipping(bool $value) {
		$this->checkPaidForShipping = $value;
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

		$stmt = $MASHPIA_DB->prepare("
			SELECT 
				COUNT(*) AS total
			FROM
				admin_auths aa
					JOIN
				users u ON u.user_id = aa.id
					JOIN
				user_registration ur USING (user_id)
			WHERE
				aa.auth = 'user'
					AND aa.admin_id = :admin_id
					AND ur.year = :year
		");
		
		$removed = 0;
		foreach ($rows as $row) {
			if ($this->checkPaidForShipping) {
				$shippingPaid = $this->paidForShipping($row['admin_id'], $id);
				if (
					($forPickup && $shippingPaid) || 
					(!$forPickup && !$shippingPaid)
				) {
					$removed++;
					continue;
				}
			}
			if ($this->checkIfAlreadyShipped) {
				$alreadyShipped = $this->checkShipmentOne($row['admin_id']);
				if ($alreadyShipped) continue;
			}
			$this->admins[$row['admin_id']] = $row;
			$this->children[$row['admin_id']][$row['user_id']] = $row['first'] . ' ' . $row['last'];

			$res = $stmt->execute(['admin_id' => $row['admin_id'], 'year' => $this->year]);
			$rowChildren = $stmt->fetch(PDO::FETCH_ASSOC);
			$this->numChildren[$row['admin_id']] = $rowChildren['total'] ?? 0;
		}
		// echo $removed . " families removed<br />";
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

	public function getNumChildren() {
		return $this->numChildren;
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

	private function checkShipmentOne($admin_id) {
        $family_ids = [
            172247, 195746, 71578, 168952, 175042, 189749, 194010, 197148, 198983, 199754,
            200132, 197476, 193120, 150479, 5794, 182987, 193229, 686, 1145, 3574,
            71046, 71487, 119027, 140768, 150580, 168402, 172411, 194120, 196727, 197335,
            198225, 199255, 199491, 199533, 200381, 200847, 200973, 200998, 118, 436,
            1264, 6205, 6356, 6459, 6561, 6646, 6725, 6804, 6823, 6848,
            7118, 8224, 71227, 71314, 71580, 128799, 129140, 129268, 129283, 129574,
            140230, 140771, 141151, 150317, 150658, 150844, 167609, 167705, 167857, 167963,
            168078, 168294, 169109, 169281, 169520, 170626, 170928, 171631, 172009, 172192,
            172237, 172269, 172307, 172627, 173897, 174022, 174986, 175122, 175218, 175276,
            175309, 175427, 175559, 175665, 175845, 175952, 177864, 178463, 178830, 178946,
            179238, 179460, 179692, 179729, 179730, 179731, 179732, 179749, 179754, 180026,
            180166, 180359, 180419, 180669, 180825, 180949, 181042, 181144, 181265, 181385,
            181398, 181498, 181565, 181579, 181626, 181676, 181716, 181719, 181742, 181808,
            181892, 181974, 182023, 182103, 182189, 182283, 182332, 182360, 182451, 182497,
            182542, 182579, 182633, 182646, 182768, 182769, 182822, 182880, 182998, 183085,
            183096, 183162, 183184, 183250, 183331, 183397, 183420, 183422, 183515, 183613,
            183721, 183829, 183948, 184027, 184167, 184233, 184365, 184403, 184459, 184517,
            184583, 184594, 184677, 184797, 184823, 184877, 184960, 185023, 185121, 185199,
            185320, 185379, 185466, 185584, 185631, 185779, 185831, 185906, 185999, 186099,
            186170, 186288, 186318, 186429, 186467, 186573, 186674, 186761, 186818, 186913,
            187003, 187071, 187154, 187253, 187357, 187401, 187523, 187612, 187752, 187828,
            187936, 188067, 188159, 188294, 188395, 188506, 188601, 188742, 188823, 188935,
            189027, 189138, 189261, 189371, 189489, 189600, 189701, 189810, 189925, 190074,
            190161, 190299, 190400, 190532, 190659, 190726, 190865, 190988, 191115, 191211,
            191344, 191472, 191590, 191725, 191871, 191999, 192115, 192270, 192392, 192523,
            192684, 192819, 192957, 193099, 193286, 193385, 193534, 193689, 193802, 193940,
            194088, 194245, 194389, 194533, 194695, 194834, 194985, 195153, 195302, 195457,
            195627, 195800, 195966, 196139, 196317, 196502, 196679, 196868, 197048, 197238,
            197421, 197618, 197820, 198031, 198234, 198444, 198654, 198863, 199084, 199314,
            199536, 199774, 200003, 200244, 200328, 200330, 200349, 200356, 200362, 200544,
            200570, 200799, 200904, 200914
        ];

        return in_array($admin_id, $family_ids);
    }
}
?>