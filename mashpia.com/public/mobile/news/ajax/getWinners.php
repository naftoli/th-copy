<?
require '../../../db.php';

$auction_id = mysql_real_escape_string( $_POST['raffle'] );

$info = array();
$sql = "select s.school_name, u.user_id, u.first, u.last, u.gender, p.prize_name, a.auction_name     
        from auction_winners aw 
        join auctions a using (auction_id) 
        join users u using (user_id) 
        join schools s on (u.school_id = s.school_id) 
        join prizes_auction p using (prize_id) 
        where aw.auction_id = " . $auction_id;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	//get rank
	$sql2 = "select rank_name from ranks where rank_ord = (
		select max(rank_ord) from rank_marks where user_id = " . $row['user_id'] . ")";
	$result2 = mysql_query($sql2);
	$row2 = mysql_fetch_assoc($result2);
	
	$info['raffle'] = $row['auction_name'];
	$gender = strtolower($row['gender']);
	if ($gender == 'm') {
		$info['boys'][] = array(
			'rank'	=> $row2['rank_name'],
			'name'	=> $row['first'] . ' ' . $row['last'],
			'school'=> $row['school_name'], 
			'prize'	=> $row['prize_name']
		);
	} else if ($gender == 'f') {
		$info['girls'][] = array(
			'rank'	=> $row2['rank_name'],
			'name'	=> $row['first'] . ' ' . $row['last'],
			'school'=> $row['school_name'], 
			'prize'	=> $row['prize_name']
		);
	}
}
echo json_encode($info);
?>