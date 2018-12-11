<?
require "blog/wp-blog-header.php";

$info = array();
$sql = "select ID from wp.wp_posts where post_type = 'birthday'";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$id = $row['ID'];
	$sql2 = "select meta_value from wp.wp_postmeta where post_id = " . $id . " and meta_key = 'user_id'";
	$result2 = mysql_query($sql2);
	$row2 = mysql_fetch_assoc($result2);
	$info[(int)$row2['meta_value']][] = $id;
}

foreach ($info as $user_id => $post_ids) {
	$total = count($post_ids);
	if ($total > 1) {
		for ($i = 1; $i < $total; $i++) {
			$sql = "delete from wp.wp_postmeta where post_id = " . $post_ids[$i];
			$sql2 = "delete from wp.wp_posts where ID = " . $post_ids[$i];
			//mysql_query($sql);
			//mysql_query($sql2);
		}
	}
}
echo "Done.";
echo "<pre>";
print_r( $info );
echo "</pre>";
?>