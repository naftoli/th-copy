<?php
require_once 'class.report.php';
require_once 'class.globalSettings.php';

class MyShliachShipLabels extends Report {
	private $admins;
	private $parents;
	protected $users;
	private $userInfo;
	private $medals;
	private $ranks;
	private $start;
	private $end;
	private $school;
    private $year;
	
	public function __construct($id = 61) {
        parent::__construct();
		$this->start = $this->reportDates['start'];
        $this->end = $this->reportDates['end'];
		$this->admins = array();
		$this->parents = array();
		$this->users = array();
		$this->userInfo = array();
		$this->medals = array();
		$this->ranks = array();
        $this->school = $id;
        $this->year = GlobalSettings::getCurrentYear();
	}

    public function setInfo() {
        $this->start = $this->reportDates['start']; // need to redo it b/c it may have been overriden after construction of the object
        $this->end = $this->reportDates['end']; // need to redo it b/c it may have been overriden after construction of the object
        $this->admins = [];
        $this->parents = [];
        $this->users = [];
        $this->userInfo = [];
        $this->medals = [];
        $this->ranks = [];
        $this->setMedals();
        $this->setRanks();
        $this->setParents();
    }
	
	private function setMedals() {
		$sql = "SELECT s.subject_name, m.medal_name, u.user_id, u.last, u.first, mm.* 
				FROM medal_marks mm 
				JOIN medals m USING ( medal_ord ) 
				JOIN users u USING ( user_id ) 
				JOIN subjects s USING ( subject_id ) 
				JOIN user_registration ur using ( user_id ) 
				WHERE u.user_registered > 0 
				and mm.date_awarded >= " . $this->start . "  
				AND mm.date_awarded <= " . $this->end . "  
				and u.school_id = " . $this->school . " 
				and u.medals_ranks = 1 
				and ur.year = " .$this->year . " 
				ORDER BY s.subject_id, mm.medal_ord";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$this->medals[$row['user_id']][] = $row;
			if (array_search($row['user_id'], $this->users) === false) {
				$this->users[] = $row['user_id'];
				$this->userInfo[$row['user_id']] = $row['first'] . ' ' . $row['last'];
			}
		}
	}
	
	private function setRanks() {
        // always get the highest rank and then see which books / ranks have not yet been shipped
//        $sql = "SELECT u.user_id, u.user_serial, u.first, u.last, MAX(rm.rank_ord) AS rank_ord
//                FROM rank_marks rm
//                JOIN users u USING ( user_id )
//                JOIN user_registration ur using ( user_id )
//                WHERE u.user_registered > 0
//                and u.school_id = " . $this->school . "
//                and ur.year = " . $this->year . "
//                GROUP BY u.user_id";
        // asked to change to this by Tzivi 1/16/2025
        $sql = "SELECT u.user_id, u.user_serial, u.first, u.last, MAX(rm.rank_ord) AS rank_ord 
                FROM rank_marks rm
                JOIN ranks r USING ( rank_ord )
                JOIN users u USING ( user_id )
                JOIN user_registration ur using ( user_id )
                WHERE u.user_registered > 0
                and rm.date_promoted >= " . $this->start . "
                AND rm.date_promoted <= " . $this->end . "
                and u.school_id = " . $this->school . "
                and ur.year = " . $this->year . "
                GROUP BY u.user_id";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$this->ranks[$row['user_id']][] = $row;
			if (array_search($row['user_id'], $this->users) === false) {
				$this->users[] = $row['user_id'];
				$this->userInfo[$row['user_id']] = $row['first'] . ' ' . $row['last'];
			}
		}
	}
	
	private function setParents() {
		$sql = "select * from admins a 
				join admin_auths aa using (admin_id) 
				where aa.id in (" . implode(',', $this->users) . ")  
				and aa.auth = 'user' 
				order by aa.admin_id";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$this->admins[$row['admin_id']] = $row;
			$this->parents[$row['admin_id']][] = $row['id'];
		}
	}

    public function getRankBooksShipped() {
        $shipped = [];
        $sql = "
            SELECT 
                *
            FROM
                rank_books_shipped rbs
                    JOIN
                users u USING (user_id)
            WHERE
                u.school_id = " . $this->school . " 
        ";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $shipped[$row['user_id']][] = $row['book_ord'];
        }
        return $shipped;
    }

    public function getRankMedalsShipped() {
        $shipped = [];
        $sql = "
            SELECT 
                *
            FROM
                rank_medals_shipped rms
                    JOIN
                users u USING (user_id)
            WHERE
                u.school_id = " . $this->school . " 
        ";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $shipped[$row['user_id']][] = $row['rank_ord'];
        }
        return $shipped;
    }
	
	public function getParents() {
		return $this->parents;
	}
	
	public function getMedals() {
		return $this->medals;
	}
	
	public function getRanks() {
		return $this->ranks;
	}
	
	public function getAdmins() {
		return $this->admins;
	}
	
	public function getUsers() {
		return $this->users;
	}
	
	public function getUserInfo() {
		return $this->userInfo;
	}
}
?>