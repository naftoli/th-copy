<?
class Purchase {
	private $id;
	private $name;
	private $email;
	private $authorization;
	private $code;
	private $items;
	private $paid;
	
	public function __construct( $name, $email, $paid, array $items, $authorization ) {
		$this->name = mysql_real_escape_string( $name );
		$this->email = mysql_real_escape_string( $email );
		$this->paid = mysql_real_escape_string( $paid );
		$this->authorization = mysql_real_escape_string( $authorization );
		$this->items = $items;
		$this->code = rand(10000, 99999);
		$this->id = null;
	}
	
	public function createPurchase() {
		mysql_query("set autocommit=0");
		mysql_query("begin");
		$success = true;
		
		$sql = "insert into purchases set name = '" . $this->name . "', email = '" . 
				$this->email . "', code = " . $this->code . ", authorization =\"" . 
				$this->authorization . "\", paid = " . $this->paid;
		//echo $sql;
		
		if ( mysql_query( $sql ) ) {
			$this->id = mysql_insert_id();
			foreach ( $this->items as $item ) {
				if ( $item == 100 ) {
					for ( $i = 1; $i < 12; $i++ ) {
						$sql = "insert into purchase_details set purchase_id = " . $this->id . ", cd_id = " . $i; 
						if ( !mysql_query( $sql ) ) {
							$success = false;
							$error = "Error creating purchase details.";
							break;	
						}
					}
				} else {
					$sql = "insert into purchase_details set purchase_id = " . $this->id . ", cd_id = " . $item; 
					if ( !mysql_query( $sql ) ) {
						$success = false;
						$error = "Error creating purchase details.";
						break;	
					}
				}
			}
		} else {
			$success = false;
			$error = "Error creating purchase.";
		}
		
		if ( $success ) {
			mysql_query("commit");
			mysql_query("set autocommit=1");
			
			//check for duplicate charge and notify shimmy / me
			//$this->checkForDuplication( $this->name, $this->email );
			
			return $this->emailClient();
		} else {
			mysql_query("rollback");
			mysql_query("set autocommit=1");
			throw new Exception( $error );
		}		
	}
	
	private function emailClient() {
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= "From: Leah Perl <motherofperl@sbcglobal.net>" . "\r\n";
		$headers .= "Reply-to: Leah Perl <motherofperl@sbcglobal.net>" . "\r\n";
		
		$msg = "Thank you for your purchase of " . count( $this->items ) . " cds.<br />";
		$msg .= "Your confirmation code is: " . $this->code . "<br />"; 
		$msg .= "Please make sure to enter this code when clicking on the download link.<br /><br />";
		$msg .= "Your download link is: http://mystoryteller.club/purchases.php?id=" . $this->id;
		return mail( $this->email, "Your Purchase", $msg, $headers );
	}
	
	private function checkForDuplication( $name, $email ) {
		$sql = "select * from purchases where name = '" . mysql_real_escape_string( $name ) . 
				"' and email = '" . mysql_real_escape_string( $email ) . "' and date > date_sub(now(), interval 6 hour)";
		$result = mysql_query( $sql );
		if ( mysql_num_rows > 1 ) {
			$purchases = array();
			while ( $row = mysql_fetch_assoc( $result ) ) {
				$purchases[$row['paid']][] = $row['date'];
			}
			
			foreach ( $purchases as $paid => $dates ) {
				$num = count( $dates );
				if ( $num > 1 ) {
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
					$headers .= "From: Leah Perl <motherofperl@sbcglobal.net>";
					
					$to = 'naftolir@gmail.com, shimmy@jcm.museum';
					
					$msg = "$name has made $num duplicate purchases in the last few hours.";
					@mail( $to, "Duplicate Purchase", $msg, $headers );
				}
			}
		}
	}
}
?>