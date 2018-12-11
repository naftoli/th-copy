<?
class Auction {
	private $auction_id;
	private $grand_prizes;
	private $prizes;
	private $grand_prizes_schools;
	private $prizes_schools;
	private $prize_school_rel;
	private $users;
	private $barcodes;
    private $newPoints;
	private $schools;
	private $prize_names;
	private $winners;
	private $users1200Points;
	private $usersAnyPoints;
	private $usersWon;
	
	public function __construct( $id ) {
		$this->auction_id = $id;
		$this->grand_prizes = array();
		$this->prizes = array();
		$this->grand_prizes_schools = array();
		$this->prizes_schools = array();
		$this->prize_school_rel = array();
		$this->users = array();
		$this->barcodes = array();
        $this->newPoints = array();
		$this->schools = array();
		$this->prize_names = array();
		$this->winners = array();
		$this->users1200Points = array();
		$this->usersAnyPoints = array();
		$this->usersWon = array();
		$this->deleteWinners();
	}
	
	private function setSchools() {
		$sql = "select school_id, school_name from schools";
		$result = mysql_query( $sql );
		while ( $row = mysql_fetch_assoc( $result ) ) {
			$this->schools[$row['school_id']] = $row['school_name'];
		}
	}
	
	public function getSchools() {
		return $this->schools;
	}
	
	public function setGrandPrizes() {
		$sql = "select pa.prize_id  
				from prizes_auction pa 
				join auction_prizes ap using (prize_id) 
				where auction_id = $this->auction_id 
				and pa.prize_points >= 72 
				and ap.available = 1";
		//echo $sql;
		$result = mysql_query( $sql );
		while ( $row = mysql_fetch_assoc( $result ) ) {
			$this->grand_prizes[] = $row['prize_id'];
		}
	}
	
	public function getGrandPrizes() {
		return $this->grand_prizes;
	}
	
	public function setPrizes() {
		$sql = "select pa.prize_id, pa.prize_name, ap.available  
				from prizes_auction pa 
				join auction_prizes ap using (prize_id) 
				where auction_id = $this->auction_id 
				and pa.prize_points < 72 
				and ap.available > 1";
		//echo $sql;
		$result = mysql_query( $sql );
		while ( $row = mysql_fetch_assoc( $result ) ) {
			$this->prizes[$row['prize_id']] = $row['available'];
			$this->prize_names[$row['prize_id']] = $row['prize_name'];
		}
	}
	
	public function getPrizes() {
		return $this->prizes;
	}
	
	public function setGrandPrizesSchools() {
		$this->grand_prizes_schools = array( 
			176, 162, 45, 30, 54, 2, 7, 112, 179, 105, 63, 81, 49, 89, 55, 106, 
			5, 50, 21, 37, 4, 60, 194, 3, 39, 19, 42, 9, 61, 48, 84, 33, 58, 110, 185, 164, 80 );
	}
	
	public function getGrandPrizesSchools() {
		return $this->grand_prizes_schools;
	}
	
	public function setPrizesSchools() {
		$this->prizes_schools = array(
			176	=>	12,
			162	=>	29, 
			45	=>	23, 
			30	=>	16,
			54	=>	87,
			2	=>	30, 
			7	=>	48, 
			52	=>	2, 
			112	=>	15, 
			179	=>	8, 
			66	=>	10, 
			105	=>	13, 
			63	=>	8, 
			81	=>	4, 
			49	=>	11, 
			89	=>	8, 
			55	=>	7, 
			106	=>	23, 
			5	=>	9, 
			50	=>	14, 
			21	=>	11, 
			37	=>	9, 
			4	=>	55, 
			60	=>	4, 
			194	=>	4, 
			3	=>	6, 
			39	=>	7, 
			19	=>	49, 
			42	=>	47, 
			9	=>	91, 
			61	=>	164, 
			48	=>	11, 
			84	=>	8, 
			33	=>	2, 
			58	=>	32, 
			110	=>	53, 
			185	=>	25, 
			164	=>	4, 
			80	=>	6
		);
	}
	
	public function getPrizesSchools() {
		return $this->prizes_schools;
	}
	
	public function setPrizeSchoolRel() {
		$this->prize_school_rel = array(
			180	=>	array(
					176	=>	1, 
					162	=>	2, 
					45	=>	2, 
					30	=>	1, 
					2	=>	2, 
					7	=>	3, 
					112	=>	1, 
					66	=>	1, 
					105	=>	1, 
					63	=>	1, 
					106	=>	1, 
					5	=>	1, 
					50	=>	1, 
					37	=>	1, 
					4	=>	3, 
					60	=>	1, 
					3	=>	1, 
					39	=>	1, 
					19	=>	3, 
					42	=>	3, 
					9	=>	6, 
					61	=>	3, 
					48	=>	3, 
					84	=>	1, 
					58	=>	3, 
					110	=>	2, 
					185	=>	1
				),
				
			70	=>	array(
					176	=>	1, 
					162	=>	3, 
					45	=>	1, 
					30	=>	1, 
					54	=>	5, 
					2	=>	2, 
					7	=>	3, 
					112	=>	1, 
					179	=>	1, 
					66	=>	1, 
					55	=>	1, 
					106	=>	1, 
					50	=>	1, 
					4	=>	2, 
					194	=>	1, 
					39	=>	1, 
					19	=>	1, 
					42	=>	1, 
					9	=>	3, 
					61	=>	6, 
					58	=>	5, 
					110	=>	2, 
					185	=>	2, 
					164	=>	2, 
					80	=>	2
				), 
			
			179	=>	array(
					176	=>	1, 
					162	=>	2, 
					45	=>	1, 
					30	=>	1, 
					54	=>	3, 
					2	=>	2, 
					7	=>	3, 
					112	=>	1, 
					66	=>	1, 
					49	=> 	1, 
					106	=>	1, 
					50	=>	1, 
					4	=>	3, 
					60	=>	1, 
					19	=>	1, 
					42	=>	3, 
					9	=>	10, 
					61	=>	11, 
					58	=>	1, 
					110	=>	1, 
					185	=>	1
				), 
				
			181	=>	array(
					176	=>	1, 
					162	=>	2, 
					45	=>	2, 
					30	=> 	1, 
					54	=>	3, 
					2	=>	2, 
					7	=>	3, 
					112	=>	1, 
					66	=>	1, 
					81	=>	1, 
					89	=>	1, 
					106	=>	2, 
					5	=>	1, 
					50	=>	1, 
					21	=>	1, 
					37	=>	1, 
					4	=>	3, 
					19	=>	3, 
					42	=>	3, 
					9	=>	8, 
					61	=>	4, 
					48	=>	1, 
					84	=>	1, 
					58	=>	1,
					110	=>	1,  
					185	=>	1
				), 	
				
			176	=>	array(
					162	=>	1, 
					45	=>	1, 
					30	=>	1, 
					54	=>	10, 
					2	=>	1, 
					7	=>	1, 
					50	=>	1, 
					194	=>	1, 
					19	=>	1, 
					42	=>	1, 
					61	=>	17, 
					33	=>	1,
					110	=>	9,  
					185	=>	2, 
					164	=>	1, 
					80	=>	1				
				),
				
			178	=>	array(
					176	=>	1, 
					162	=>	2, 
					45	=>	1, 
					30	=>	1, 
					54	=>	3, 
					2	=>	2, 
					7	=>	3, 
					52	=>	1, 
					112	=>	1, 
					179	=>	1, 
					66	=>	1, 
					105	=>	1, 
					49	=>	1, 
					89	=>	1, 
					55	=>	1, 
					106	=>	1, 
					5	=>	1, 
					50	=>	1, 
					21	=>	1, 
					37	=>	1, 
					4	=>	2, 
					194	=>	1, 
					3	=>	1, 
					39	=>	1, 
					19	=>	3, 
					42	=>	3, 
					9	=>	1, 
					61	=>	7, 
					48	=>	1, 
					84	=>	1, 
					58	=>	1, 
					110	=>	1, 
					185	=>	1
				), 
				
			177	=>	array( 
					162	=>	2, 
					45	=>	1, 
					30	=>	1, 
					54	=>	3, 
					2	=>	2, 
					7	=>	3, 
					52	=>	1, 
					106	=>	1, 
					5	=>	1, 
					50	=>	1, 
					21	=>	1, 
					37	=>	1, 
					4	=>	1, 
					194	=>	1, 
					39	=>	1, 
					19	=>	1, 
					42	=>	3, 
					9	=>	6, 
					61	=>	15, 
					58	=>	1, 
					110	=>	1, 
					185	=>	2
				), 
				
			174	=>	array( 
					176	=>	1, 
					162	=>	2, 
					45	=>	2, 
					54	=>	3, 
					2	=>	2, 
					7	=>	3, 
					112	=>	1, 
					179	=>	1, 
					66	=>	1, 
					105	=>	1, 
					81	=>	1, 
					55	=>	1, 
					106	=>	1, 
					50	=>	1, 
					21	=>	1, 
					37	=>	1, 
					4	=>	3, 
					3	=>	1, 
					19	=>	3, 
					42	=>	2, 
					9	=>	6, 
					61	=>	4, 
					48	=>	1, 
					84	=>	1, 
					58	=>	3, 
					110	=>	2, 
					185	=>	1
				), 
				
			175	=>	array( 
					176	=>	1, 
					162	=>	1, 
					45	=>	1, 
					54	=>	2, 
					2	=>	3, 
					7	=>	2, 
					112	=>	1, 
					66	=>	1, 
					81	=>	1, 
					106	=>	1, 
					50	=>	1, 
					37	=>	1, 
					4	=>	2, 
					60	=>	1, 
					19	=>	2, 
					42	=>	1, 
					9	=>	6, 
					61	=>	6, 
					58	=>	1, 
					110	=>	5, 
					185	=>	6, 
					164	=>	1, 
					80	=>	3
				), 
				
			23	=>	array( 
					112	=>	2, 
					179	=>	1, 
					105	=>	1, 
					63	=>	1, 
					55	=>	1, 
					106	=>	2, 
					4	=>	8, 
					39	=>	1, 
					19	=>	5, 
					9	=>	5, 
					61	=>	28, 
					48	=>	1,
					58	=>	1, 
					110	=>	5, 
					185	=>	1
				), 
				
			183	=>	array( 
					162	=>	4, 
					30	=>	4, 
					54	=>	4, 
					2	=>	4, 
					7	=>	2, 
					105	=>	4, 
					49	=>	4, 
					4	=>	4, 
					19	=>	4, 
					42	=>	4, 
					9	=>	8, 
					61	=>	4
				), 
				
			121	=>	array( 
					176	=>	1, 
					162	=>	2, 
					45	=>	2, 
					30	=>	1, 
					54	=>	3, 
					2	=>	2, 
					7	=>	3, 
					112	=>	1, 
					89	=>	1, 
					55	=>	1, 
					106	=>	2, 
					50	=>	1, 
					21	=>	1, 
					37	=>	1, 
					4	=>	3, 
					19	=>	3, 
					42	=>	3, 
					9	=>	5, 
					61	=>	8, 
					48	=>	1, 
					84	=>	1, 
					58	=>	2, 
					110	=>	1, 
					185	=>	1
				), 
				
			128	=>	array( 
					176	=>	1, 
					162	=>	2, 
					45	=>	2, 
					30	=>	1, 
					54	=>	3, 
					2	=>	2, 
					7	=>	3, 
					112	=>	1, 
					66	=>	1, 
					105	=>	1, 
					63	=>	1, 
					106	=>	1, 
					50	=>	1, 
					21	=>	1, 
					4	=>	3, 
					60	=>	1, 
					19	=>	3, 
					42	=>	4, 
					9	=>	7, 
					61	=>	6, 
					58	=>	3, 
					110	=>	1, 
					185	=>	1
				), 
				
			32	=>	array( 
					176	=>	1, 
					162	=>	1, 
					45	=>	2, 
					30	=>	1, 
					54	=>	10,
					2	=>	1, 
					7	=>	3, 
					112	=>	1, 
					179	=>	1, 
					66	=>	1, 
					105	=>	1,
					63	=>	1, 
					81	=>	1, 
					49	=>	1, 
					89	=>	1, 
					55	=>	1, 
					106	=>	2, 
					5	=>	1, 
					50	=>	1, 
					21	=>	1, 
					37	=>	1, 
					4	=>	4, 
					39	=>	1, 
					19	=>	3, 
					42	=>	3, 
					9	=>	5, 
					61	=>	4, 
					48	=>	1, 
					58	=>	2, 
					110	=>	5, 
					185	=>	1
				), 
				
			20	=>	array( 
					112	=>	1, 
					179	=>	1, 
					105	=>	1, 
					63	=>	2, 
					49	=>	2, 
					89	=>	2, 
					106	=>	3, 
					5	=>	2, 
					21	=>	2, 
					4	=>	6, 
					3	=>	2, 
					19	=>	6, 
					9	=>	5, 
					61	=>	16, 
					48	=>	1, 
					84	=>	1, 
					58	=>	4, 
					110	=>	5, 
					185	=>	1
				), 
				
			10	=>	array( 
					176	=>	1, 
					162	=>	1, 
					45	=>	2, 
					30	=>	1, 
					54	=>	10, 
					2	=>	1, 
					7	=>	3, 
					112	=>	1, 
					179	=>	1, 
					66	=>	1, 
					105	=>	1, 
					63	=>	1, 
					49	=>	1, 
					89	=>	1, 
					55	=>	1, 
					106	=>	2, 
					5	=>	1, 
					50	=>	1, 
					21	=>	1, 
					37	=>	1, 
					4	=>	4, 
					3	=>	1, 
					39	=>	1, 
					19	=>	3, 
					42	=>	3, 
					9	=>	5, 
					61	=>	3, 
					48	=>	1, 
					84	=>	1, 
					58	=>	2, 
					110	=>	5, 
					185	=>	1 
				), 
			
			25	=>	array( 
					176	=>	1, 
					162	=>	1, 
					45	=>	2, 
					30	=>	1, 
					54	=>	6, 
					2	=>	1, 
					7	=>	5, 
					112	=>	1, 
					179	=>	1, 
					105	=>	1, 
					63	=>	1, 
					49	=>	1, 
					89	=>	1, 
					106	=>	2, 
					5	=>	1, 
					50	=>	1, 
					21	=>	1, 
					4	=>	4, 
					19	=>	4, 
					42	=>	5, 
					9	=>	5, 
					61	=>	7, 
					84	=>	1, 
					33	=>	1, 
					58	=>	2, 
					110	=>	5, 
					185	=>	1
				), 
				
			82	=>	array( 
					162	=>	1, 
					45	=>	1, 
					54	=>	19, 
					2	=>	1, 
					7	=>	5, 
					42	=>	5, 
					61	=>	15, 
					110	=>	2, 
					185	=>	1
				)		
		);
	}
	
	public function getPrizeSchoolRel() {
		return $this->prize_school_rel;
	}
	
	public function setUsers() {
		foreach( $this->prizes_schools as $school => $total ) {
			$sql = "select distinct user_id 
					from users u 
					join auction_user_prizes aup using (user_id) 
					where u.user_registered > 0 
					and u.school_id = $school 
					and aup.auction_id = 37 
					and u.user_id not in (
						select user_id from auction_winners
						where auction_id >= 33
					)";
			//echo $sql;
			$result = mysql_query( $sql );
			while ( $row = mysql_fetch_assoc( $result ) ) {
				$this->users[$school][] = $row['user_id'];
			}
		}	
	}
	
	public function getUsers() {
		return $this->users;
	}
	
	public function setBarcodes() {
        $sql = "
            SELECT user_code 
            FROM users u
            JOIN schools s
            USING ( school_id )
            WHERE u.user_registered >0
            AND s.school_era IS NULL";
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $this->barcodes["user_code"][] = $row['user_code'];
        } 
    }
    
    public function setNewPoints() {
        $this->newPoints = header_icorpa_points_multi( $this->barcodes );
    }
	
	private function getPoints( $user_id, $user_code ) {
        $cur_points = floatval(mysql_result(mq(totalMarks("WHERE user_id = $user_id AND mark_date >= " . dateThisYear(13, 18))), 0));   
        //echo "User: " . $user_id . "<br />";
        //echo "Old points: " . $cur_points . "<br />";
        if ( count( $this->barcodes ) > 0 ) {
	        $code = '3' . $user_code;
	        if ( array_key_exists( $code, $this->newPoints ) ) {
	            $new_points = $this->newPoints[$code]["hebrew_points"];
                if ($new_points > 0) {
    	            $cur_points += $new_points;
                    //echo "New points: " . $this->newPoints[$code]["hebrew_points"] . "<br />";
                }
            }
	    }
        return $cur_points;
    }
	
	public function setUsers1200Points() {
		foreach( $this->prizes_schools as $school_id => $total ) {
			$this->users1200Points[$school_id] = array(); 
			$sql = "select user_id, user_code 
					from users u 
					where u.user_registered > 0 
					and u.school_id = $school_id 
					and u.user_id not in (
						select user_id from auction_winners
						where auction_id >= 33
					) 
					and u.user_id not in (
						select user_id from auction_user_prizes aup 
						join users u using (user_id) 
	 					where aup.auction_id = 37 
						and u.school_id = $school_id
					)";
			//echo $sql;
			$result = mysql_query( $sql );
			while ( $row = mysql_fetch_array( $result ) ) {
				$user_id = $row['user_id'];
				$code = $row['user_code'];
				if ( $this->getPoints($user_id, $code) >= 1200 ) {
					$this->users1200Points[$school_id][] = $user_id;
				}
			}
		}
	}
	
	public function getUsers1200Points() {
		return $this->users1200Points;
	}
	
	public function setUsersAnyPoints() {
		foreach( $this->prizes_schools as $school_id => $total ) {
			$this->usersAnyPoints[$school_id] = array(); 
			$sql = "select user_id, user_code 
					from users u 
					where u.user_registered > 0 
					and u.school_id = $school_id 
					and u.user_id not in (
						select user_id from auction_winners
						where auction_id >= 33
					) 
					and u.user_id not in (
						select user_id from auction_user_prizes aup 
						join users u using (user_id) 
	 					where aup.auction_id = 37 
						and u.school_id = $school_id
					)";
			//echo $sql;
			$result = mysql_query( $sql );
			while ( $row = mysql_fetch_array( $result ) ) {
				$user_id = $row['user_id'];
				$code = $row['user_code'];
				$this->usersAnyPoints[$school_id][] = $user_id;
			}
		}
	}
	
	public function getUsersAnyPoints() {
		return $this->usersAnyPoints;
	}
	
	public function setWinners() {		
		$this->setGrandPrizeWinners();
		
		foreach( $this->prize_school_rel as $prize => $info ) {
			$i = 1; //counter to see how many prizes given out 
			foreach( $info as $school => $amount ) {
				//each time we have a winner we remove that winner from users array
				//once users array is empty for that school search in users1200Points array
				//once users1200 array is empty search in usersAnyPoints array 
				while ( $amount > 0 ) {
					echo $i++;
					if ( !empty( $this->users[$school] ) ) {
						$this->calculate( $school, $prize, $amount, 'users' ); 
					} else if ( !empty( $this->users1200Points[$school] ) ) {
						$this->calculate( $school, $prize, $amount, 'users1200Points' );
					} else if ( !empty( $this->usersAnyPoints[$school] ) ) {
						$this->calculate( $school, $prize, $amount, 'usersAnyPoints' );
					} else {
						echo "No users found in school " . $school . ".<br />";
					}
					$amount--;
				}
			}
		}
		
		$this->updateWinnersTable();
	}
	
	private function setGrandPrizeWinners() {
		$schoolsWon = array();	
		//give one grand prize to each of the schools
		foreach( $this->grand_prizes as $grand_prize ) {
			//get school 
			do {
				$schoolRand = rand( 0, (count( $this->grand_prizes_schools ) - 1) );
				$school = $this->grand_prizes_schools[$schoolRand];
			} while ( in_array( $school, $schoolsWon ) );
			do {
				$userRand = rand(0, (count( $this->users[$school] ) - 1) );
				$winner = $this->users[$school][$userRand];
			} while ( in_array( $winner, $this->usersWon ) );
			$schoolsWon[] = $school;
			$this->usersWon[] = $winner;	
			$this->winners[$grand_prize][] = $winner;
		}
		
		//remove all winners from users array
		foreach( $this->users as $school => $info ) {
			foreach( $info as $key => $user ) {
				if ( in_array($user, $this->usersWon) ) {
					//find key
					unset( $this->users[$school][$key] );
		 			$this->users[$school] = array_values( $this->users[$school] );
				}
			}
		}
	}

	private function calculate( $school, $prize, $amount, $name ) {
		$totalUsers = count( $this->{$name}[$school] ); 
		if ( $totalUsers > 1 ) {
			do {
				$rand = rand( 0, ($totalUsers - 1) ); 
				$winner = $this->{$name}[$school][$rand];
			} while ( in_array( $winner, $this->usersWon ) );
			$this->usersWon[] = $winner;
			echo ". Prize: " . $prize . " School: " . $school . " Winner: " . $winner . "<br />";
			$this->winners[$prize][] = $winner;
			unset( $this->{$name}[$school][$rand] );
			$this->{$name}[$school] = array_values( $this->{$name}[$school] );
		} else if ( $totalUsers == 1 ) {
			$winner = $this->{$name}[$school][0];
			$this->usersWon[] = $winner;
			$this->winners[$prize][] = $winner;
			unset( $this->{$name}[$school][0] );
			$this->{$name}[$school] = array_values( $this->{$name}[$school] );
		} else {
			echo "No users - " . $name . "<br />";
		}
	} 	
	
	public function getWinners() {
		return $this->winners;
	}
	
	public function deleteWinners() {
		$sql = "delete from auction_winners where auction_id = " . $this->auction_id;
		mysql_query( $sql );
	}
	
	public function updateWinnersTable() {
		foreach( $this->winners as $prize => $info ) {
			foreach( $info as $user_id ) {
				$sql = "insert into auction_winners values( $this->auction_id, $user_id, $prize, 1 )";
        		mysql_query( $sql ) or die( $sql . mysql_error() );
			}
		} 
	}
	
	public function run() {
		$this->setGrandPrizes();
		$this->setPrizes();
		$this->setGrandPrizesSchools();
		$this->setPrizesSchools();
		$this->setPrizeSchoolRel();
		$this->setSchools();
		$this->setUsers();
		$this->setBarcodes();
		$this->setNewPoints();
		$this->setUsers1200Points();
		$this->setUsersAnyPoints();
		$this->setWinners();
	}
}
?>
