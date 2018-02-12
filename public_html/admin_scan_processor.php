<?

if (preg_match('/[^0-9]/', $scan_code)) {
	return sprintf(T_('Code %s failed, must contain only numbers.'), $scan_code);
} 
else switch(strlen($scan_code)) {
  
	case 10: // length
	
	case 18: // length, depreciated for withdraw code
    
		switch($scan_code[0]) {
		
			case '1': // withdraw code
				$row = mysql_fetch_assoc(mq("SELECT first, last, user_serial, print_date, scan_date, points FROM user_withdraw JOIN users USING (user_id) WHERE code_id = " . substr($scan_code, 1) . " AND school_id = $school_id"));
				
				if (!$row) {
					return T_('Unable to find voucher with this scan code.');
				} 
				elseif(!is_null($row['scan_date'])) {
					return sprintf(T_('This voucher costing %s for %s %s #%s was already cashed on %s'), $row['points'], $row['first'], $row['last'], $row['user_serial'], $row['scan_date']);
				} 
				else {
					mq('UPDATE user_withdraw SET scan_date = NOW() WHERE code_id = ' . substr($scan_code, 1));
					return es(sprintf(T_('Marked voucher costing %s for %s %s #%s as cashed.'), $row['points'], $row['first'], $row['last'], $row['user_serial']));
				}
			break; // case '1':

			case '2': // store purchase 
				$sql = "SELECT sp.*, ps.prize_available FROM store_purchases AS sp JOIN prizes_store AS ps ON ps.prize_id=sp.prize_id AND (ps.school_id=" . $school_id . " OR isnull(ps.school_id)) WHERE code_id=" . substr($scan_code, 1);
				echo "<input type='hidden' name='SQL' value='" . $sql . "'>";
				$row = mysql_fetch_assoc(mq($sql));
				
				if (!$row) {
					return T_('Unable to find store voucher with this scan code.');
				} 
				else {
					if ($row['scan_date'] > 0) {
						return "Store Voucher already scanned.";
					}
					else {
						mq("UPDATE store_purchases SET scan_date = NOW() WHERE code_id=" . substr($scan_code, 1));
						$prize_available = $row['prize_available'] - $row['prize_quantity'];
						mq("UPDATE prizes_store SET prize_available=" . $prize_available . "  WHERE prize_id=" . $row['prize_id'] . " AND (school_id=" . $school_id . " OR isnull(school_id))");
						return "Store Voucher found.";
					}
				}
			break;
			
			default:
				return sprintf(T_('Unknown type of scan code %s'), $scan_code);
			break; // default
			
		} // switch($scan_code[0]) 
		
    break; // case 18:

	default:
		return sprintf(T_('Code %s failed, invalid number of digits. Perhaps the scanner did not get a good read.'), $scan_code);
    break; // default 
}
?>
