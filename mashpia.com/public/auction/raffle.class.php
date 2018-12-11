<?
/*
 * Raffles will be run 6 times throughout the year.
 * For every 50 points that each child has, a raffle ticket 
 * will be placed in each prize for that raffle.
 * Points include missions as well as achievement cards.
 * Every school needs to win one prize per 100 students.
 * Smaller schools should not win each time but only a few times.
 */
$admin_auth = array('school'); 
require_once('../header.php');
 
class Raffle {
        
    private $id;
    private $prizes;
    private $prize_amounts;
    private $specific_prize;
    private $used_prizes;
    private $winners;
    private $schools;
    private $schools_specific_prize;
    private $children;
    private $barcodes;
    private $newPoints;
	private $start;
	private $end;
	private $ctr;
	private $bad_schools;
        
    public function __construct($id, $prize = 0) {
        $this->id = $id;
        $this->prizes = array();
        $this->winners = array();
        $this->schools = array();
        $this->specific_prize = $prize;
        $this->used_prizes = array();
        $this->prize_amounts = array();
        $this->children = array();
        $this->barcodes = array();
        $this->newPoints = array();
		$this->start = null;
		$this->end = null;
		$this->ctr = 0;
		$this->bad_schools = array();
    }
	
	public function setDates( $start, $end ) {
		$this->start = $start;
		$this->end = $end;
	}
    
    public function setPrizes($prize = 0) {
        $sql = "select * from auction_prizes where auction_id = " . $this->id;
		if ($prize > 0)
			$sql .= " and prize_id = $prize";
        //echo $sql . "<br />";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $prize_id = $row['prize_id'];
            $num = $row['available'];
            
            if ( $prize_id == $this->specific_prize ) 
                continue;
            
            do {
                $this->prizes[] = $prize_id;
            } while ( $num-- > 1 );
        }
        $this->prize_amounts = array_count_values( $this->prizes );        
    }
    
    function getPrizes() {
        return $this->prizes;
    }
    
    public function setSchoolsSpecificPrize( $schools ) {
        $this->schools_specific_prize = $schools;
    }
    
    public function getSchoolsSpecificPrize() {
        return $this->schools_specific_prize;
    }
    
    public function setSchools( $schools = null ) {
        if ( is_null( $schools ) ) {
            $sql = "
                SELECT school_id, count( user_id ) AS total
                FROM users u
                JOIN schools s
                USING ( school_id )
                WHERE user_registered >0
                AND u.school_id >0
                AND s.school_era IS NULL 
                AND s.school_id not in (82, 79) 
                GROUP BY school_id";
            $result = mysql_query($sql);
            while ( $row = mysql_fetch_assoc( $result) ) {
                $school_id = $row['school_id']; 
                $total = $row['total'];
                
                if ( in_array( $school_id, $this->schools_specific_prize ) ) 
                    continue;
                
                do { 
                    $this->schools[] = $row['school_id'];
                    $total -= 100;
                } while ( $total > 75 );
            }
        } else {
            $this->schools = $schools;
        }
    }
    
    public function getSchools() {
        return $this->schools;
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
    
    public function setChildren( $school_id = 0 ) {
    	$users = array();
    	$sql = "select school_id, user_id from users 
    			where user_registered > 0 
    			AND school_id in (";
		if ($school_id) 
			$sql .= $school_id;
		else if (!empty($this->schools))
			$sql .= implode(',', $this->schools);
		else if (!empty($this->schools_specific_prize)) 
			$sql .= implode(',', $this->schools_specific_prize);
		$sql .= ")";
		//echo $sql; exit;
		$result = mysql_query($sql);
		while ( $row = mysql_fetch_assoc( $result ) ) {
			$users[$row['school_id']][] = $row['user_id'];
		}
		echo "<pre>"; //print_r($users);
		//echo time() . "<br />";
		require_once 'class.raffleTicket.php';
		foreach ( $users as $school => $children ) {
			if (!empty($children)) {
				$r = new RaffleTicket( $children, $this->start, $this->end );
				$tickets = $r->calculateTickets();
				if ( !empty( $tickets ) ) {
					$this->children[$school] = $tickets;
				}
			}
		}
		//echo time();
		//print_r( array_keys($this->children) ); exit;
		//print_r($this->children);
		//echo "</pre>"; exit;
    	/*
        if ( $school_id > 0 ) {
            $sql = "
                SELECT school_id, user_id, user_code 
                FROM users u
                JOIN schools s
                USING ( school_id )
                WHERE u.user_registered > 0 
                AND s.school_id = $school_id 
                AND s.school_era IS NULL"; 
        } else {
            $sql = "
                SELECT school_id, user_id, user_code 
                FROM users u
                JOIN schools s
                USING ( school_id )
                WHERE u.user_registered > 0 
                AND s.school_id in (";
                if (!empty($this->schools))
					$sql .= implode(',', $this->schools);
				else if (!empty($this->schools_specific_prize)) 
					$sql .= implode(',', $this->schools_specific_prize);
				$sql .= ") 
                AND s.school_era IS NULL";
        }
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            //find out if user has enough points to be in raffle - needs to have at least 50 points
            $points = $this->getPoints( $row['user_id'], $row['user_code'] );
            if ( ($points > 49) || ($school_id > 0 && $points > 0) )
                $this->children[$row['school_id']][] = $row['user_id'];
        }
		 * 
		 */
    }
    
    public function checkSchoolChildren( $school_id ) {
        if ( !key_exists( $school_id, $this->children ) ) {
            $this->setChildren( $school_id );
        }
    }
    
    public function getChildren() {
        return $this->children;
    }
    
    private function getPoints( $user_id, $user_code ) {
        $cur_points = floatval(mysql_result(mq(totalMarks("WHERE user_id = $user_id")), 0));   
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
    
    public function setWinners($delete = true) {
        //check db for existing winners and delete
        if ($delete)
        	$this->checkForWinners();    
        
        //for schools in school specific prize array find random child from that school and award him prize
        if ( $this->specific_prize > 0 ) {
            foreach ( $this->schools_specific_prize as $school ) {
                if ( array_key_exists( $school, $this->children ) ) {
                    $random = $this->getRandomChild( $school ); 
					if ($random == -1) continue;
                    $this->winners[$school][$this->children[$school][$random]] = $this->specific_prize;
					//echo $this->id . ':' . $this->children[$school][$random] . ':' . $this->specific_prize . "<br />";
                    $this->updateWinnersTable( $this->id, $this->children[$school][$random], $this->specific_prize );
                }
            }
        }
        
        $awarded = 0;
        //for rest of schools find random child then find random prize
        foreach ( $this->schools as $school ) {
            if ( array_key_exists($school, $this->children) ) {
            	//echo $school . "<br />"; continue;
            	//if auction goes into never ending loop, it may be related to trying to find a child 
            	//that didn't win yet this year but there is no such child
                $random = $this->getRandomChild( $school );
                $prize = $this->getPrize();                 
                $this->winners[$school][$this->children[$school][$random]] = $prize;
                $this->updateWinnersTable( $this->id, $this->children[$school][$random], $prize );
                //make sure to only give out the number of prizes available 
                if ( ++$awarded == count( $this->prizes ) ) {
                    echo "Total Prizes: " . count( $this->prizes ) . " Awarded: " . $awarded . "<br />";    
                    break;
                }
            } else {
                echo "School #" . $school . " has no children ";
                //delete winners from table
                $this->deleteWinners();
                exit;
            }
        }
    }

    public function setSpecificSchoolWinner( $school ) { 
        if ( array_key_exists( $school, $this->children ) ) {
            $random = $this->getRandomChild( $school ); 
            $this->winners[$school][$this->children[$school][$random]] = $this->specific_prize;
            $this->updateWinnersTable( $this->id, $this->children[$school][$random], $this->specific_prize );
        }
    }
    
    public function getWinners() {
        return $this->winners;
    }
    
    private function getPrize() {
        $random = rand( 0, ( count($this->prizes) - 1 ) );
        $random_p = $this->prizes[$random];
        //check that this prize wasn't used up to max amount available
        //find number of times this prize is on prize list
        //find number of times this prize is in used prizes list
        //compare the two
        if ( in_array( $random_p, $this->used_prizes ) ) {
            $num = $this->prize_amounts[$random_p];
            $used_num = array_count_values( $this->used_prizes );
            if ($used_num[$random_p] == $num) {
            	//echo $random_p; 
                return $this->getPrize();
            } else {
                $this->used_prizes[] = $random_p;
                return $random_p;
            }
        } else {
            $this->used_prizes[] = $random_p;
            return $random_p;
        }
    }
    
    private function checkForWinners() {
    	$s = "delete from auction_winners where auction_id = " . $this->id;
            mysql_query( $s ) or die( mysql_error() );
    }
    
    private function updateWinnersTable( $auction_id, $user_id, $prize_id, $qty = 1 ) {
        $sql = "insert into auction_winners values( $auction_id, $user_id, $prize_id, $qty, 0 )";
        mysql_query( $sql ) or die( $sql . mysql_error() );
    }
    
    private function deleteWinners() {
        $sql = "delete from auction_winners where auction_id = " . $this->id;
        mysql_query( $sql ) or die( $sql . mysql_error() );
    }
    
    public function getUsedPrizes() {
        return $this->used_prizes;
    }
    
    private function getRandomChild( $school, $random = false ) {
        if ( !$random ) {
        	if (count($this->children[$school]) == 1) {
        		$random = 0;
        	} else {
        		$random = rand( 0, ( count( $this->children[$school] ) - 1 ) );
			}
		} else {
			if (!array_key_exists($random, $this->children[$school])) {
				$this->bad_schools[] = $school;
				return -1;
			}
		}
        if ( $this->userWonThisYear( $this->children[$school][$random] ) || (array_key_exists($school, $this->winners) && array_key_exists($this->children[$school][$random], $this->winners[$school]))) {
        //if ( $this->userWonThisYear( $this->children[$school][$random] )) {	
        	//echo $school . ' : ' . $random . '- already won<br />'; 
        	if ($this->ctr++ > 5) {
        		$this->bad_schools[] = $school;
				return -1;
        	}
            return $this->getRandomChild( $school, $random++ );
        } else {
        	//echo $school . ' : ' . $random . '<br />';
        	$this->ctr = 0;
            return $random;
		}
    } 
    
    private function userWonThisYear( $user ) {
        $sql = "select * from auction_winners 
                where auction_id >= 58 
                and user_id = " . $user;
        $result = mysql_query( $sql );
        $rows = mysql_num_rows($result);
        if ( $rows > 1 ) 
            return true;
        else 
            return false;
    }
    
    public function updateAuction() {
        $sql = "update auctions 
                set auction_ran = 1 
                where auction_id = " . $this->id;
        $result = mysql_query( $sql );
    }
	
	public function getBadSchools() {
		return $this->bad_schools;
	}
}
?>