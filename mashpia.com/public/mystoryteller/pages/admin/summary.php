<?
session_start();
if (!isset($_SESSION['login']) && $_SESSION['login'] != 2) {
	header("Location: index.php");
	exit;
}

require_once '../db.php';

$now = new DateTime();
$beginning = new DateTime('2014-09-01');

$diff = $now->diff($beginning);
$total = $diff->format('%m');

$dates[] = $beginning->format('Y-m-d');
for ($i = 0; $i < $total; $i++) {
	$dates[] = $beginning->add(new DateInterval('P1M'))->format('Y-m-d');
}

$summary = array();
for ($i = 0; $i < $total; $i++) {
	$sql = "select sum(paid) as total from purchases where name != 'Leah Perl' and date >= '" . $dates[$i] . "' 
			and date < '" . $dates[$i+1]. "'";
	//echo $sql . "<br />";
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	$summary[$i] = $row['total'];
}
$sql = "select sum(paid) as total from purchases where name != 'Leah Perl' and date >= '" . $dates[$i] . "' 
		and date <= '" . $now->format('Y-m-d') . "'";
//echo $sql . "<br />";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$summary[$i] = $row['total'];

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

									<table class="table">
										<tr>
											<th>From Date</th>
											<th>To Date (not including)</th>
											<th>Total</th>
										</tr>
										<?
										$total = count($summary);
										$total--;
										for ($i = 0; $i < $total; $i++) {
											echo "<tr><td>" . $dates[$i] . "</td><td>" . $dates[$i+1] . 
												"</td><td>" . $summary[$i] . "</td></tr>";
										}
										echo "<tr><td>" . $dates[$i] . "</td><td>" . 
											$now->format('Y-m-d') . "</td><td>" . $summary[$i] . "</td></tr>";
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