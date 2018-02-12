<?php
class Child{

	function __construct($child_id) {
		$sql = "SELECT * FROM users WHERE user_id=" . $child_id;
		$query = mysql_query($sql);
		return mysql_fetch_assoc($query);
	}
}
?>