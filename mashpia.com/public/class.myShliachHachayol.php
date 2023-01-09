<?
class MyShliachHachayol {
	private $admins;
	private $children;
	private $sortedAdmins;
	
	public function __construct( $noShip = false, $id = 61 ) {
		$this->setInfo( $noShip, $id );
	}	
	
	private function setInfo( $noShip, $id ) {
		$sql = "SELECT a.*, a.first as afirst, a.last as alast, u.*  
                from admins a 
				join admin_auths aa using (admin_id) 
				join users u on aa.id = u.user_id 
				where aa.auth = 'user' 
				and u.school_id = $id  
				and u.user_registered > 0 ";
		if ($noShip) {
			$sql .= "and no_shipping = 0 ";
		}
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$info[] = $row;
		} 
		
		foreach ($info as $row) {
			$this->admins[$row['admin_id']] = $row;
			$this->children[$row['admin_id']][] = $row['first'] . ' ' . $row['last'];
		}
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