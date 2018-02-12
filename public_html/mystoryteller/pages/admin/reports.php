<?
session_start();
if (!isset($_SESSION['login']) && $_SESSION['login'] != 2) {
	header("Location: index.php");
	exit;
}

require_once '../db.php';
$purchases = array();
$sql = "select purchase_id, name, email, code, paid, date from purchases";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$purchases[$row['purchase_id']] = $row;
}

$details = array();
foreach ($purchases as $id => $purchase) {
	$sql = "select pd.downloaded, pd.download_date, c.title 
			from purchase_details pd 
			join cds c on pd.cd_id = c.id 
			where pd.purchase_id = " . $id;
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$details[$id][] = $row;
	}
}

include 'header.php'; 
?>

<style>
	.table-bordered {
		font-size: 10px;
	}
	.table-bordered th, .table-bordered td {
		padding: 3px;
	}
</style>

<section id="tz-main"><!-- tz-main-->

    <section class="tz-main-body">

        <div class="container-fluid"><!--start container-fluid-->

            <div class="tz-inner"><!--start tz-inner-->

              <section class="tz-content-wrap row-fluid">

                    <section id="tz-content" class="span8">

                        <section id="tz-component">

                            <div class="TzBlog blog">

                                <div class="TzBlogInner">
                                	
                                	<p>
                                		Click <a href='summary.php'>here</a> to view Summary by Month
                                	</p>

									<table class="table">
										<tr>
											<th>Purchase ID</th>
											<th>Purchase Code</th>
											<th>Name</th>
											<th>Email</th>
											<th>Date Purchased</th>
											<th>Amount Paid</th>
										</tr>
										<?
										foreach ($purchases as $id => $purchase) {
											echo "<tr><td>" . $id . "</td><td>" . $purchase['code'] . "</td><td>" . $purchase['name'] . "</td><td>" . 
												$purchase['email'] . "</td><td>" . $purchase['date'] . "</td><td>" . 
												$purchase['paid'] . "</td></tr>";
											echo "<tr><td colspan='5'><table class='table-bordered'><tr><th>Title</th><th>Downloaded</th><th>Download Date</th></tr>";
											foreach ($details[$id] as $detail) {
												echo "<tr><td>" . $detail['title'] . "</td><td>" . ($detail['downloaded'] ? 'yes' : 'no') . 
													"</td><td>" . $detail['download_date'] . "</td></tr>";
											}
											echo "</table></td></tr>";
										}
										?>
									</table>
									
									<div class="clearfix"></div>

                                </div>

                            </div><!--end TzBlog-->
                            
                    <div class="clr"></div>

                </section><!--end tz-content-wrap-->

            </div><!--end tz-inner-->

      </div><!--end container-fluid-->

    </section><!--end tz-main-body-->

</section><!--end tz-main-->

<? include 'footer.php'; ?>