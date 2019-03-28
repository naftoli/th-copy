<?
include 'inc/head.php';

if ( !isset( $_GET['id'] ) && !isset( $_POST['id'] ) ) {
	echo "sorry you need to have a purchase ID.";
	exit;
}

$purchaseID = isset( $_GET['id'] ) ? $_GET['id'] : $_POST['id'];
if ( !is_numeric( $purchaseID ) ) {
	echo "sorry your ID is incorrect.";
	exit;
}
?>

<section id="tz-main"><!--start tz-main-->

    <section class="tz-main-body">

        <div class="container-fluid"><!--start container-fluid-->

            <div class="tz-inner"><!--start tz-inner-->
            	
                <section class="tz-content-wrap row-fluid">

                  <section id="tz-content" class="span4">

                        <section id="tz-component">
                        	
                        	<div style="color: black">
<?
$showForm = true;
if ( isset( $_POST['submit'] ) ) {
	$msg = '';
	$showForm = false;

	$code = $_POST['code'];
	if ( !is_numeric( $code ) ) {
		$msg .= "The code must be only digits.<br />";
		$showForm = true;
	}
	
	$email = $_POST['email'];
	if ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
		$msg .= "Your email is invalid.<br />";
		$showForm = true;
	}
	
	if ( !$showForm ) {
		//get purchase info
		require_once 'db.php';
		$sql = "select name, email, code from purchases where purchase_id = " . mysql_real_escape_string( $purchaseID );
		$result = mysql_query( $sql );
		$purchase = mysql_fetch_assoc( $result );
		
		$items = array();
		$sql = "select cd_id from purchase_details where purchase_id = " . mysql_real_escape_string( $purchaseID ) . " 
				and downloaded = 0";
		$result = mysql_query( $sql );
		if ( mysql_num_rows( $result ) > 0 ) {
			while ( $row = mysql_fetch_assoc( $result ) ) {
				$items[] = $row['cd_id'];
			}
	
			$cds = array();
			$sql = "select * from cds where id in (" . implode(',',$items) . ")";
			$result = mysql_query( $sql );
			while ( $row = mysql_fetch_assoc( $result ) ) {
				$cds[$row['id']] = $row;
			}
			
			if ( $code == $purchase['code'] && strtolower( $email ) == strtolower( $purchase['email'] ) ) {
				//code is valid so provide download link
				echo "<div>Thank you <b>" . $purchase['name'] . "</b> for your purchase.<br />
					Here are the links for your purchase: (Please Note: Once you download it, it will not be available for downloading again.)</div>";
				echo "<ul>";
				foreach ( $items as $item ) {
					$cd = $cds[$item];
					echo "<li>" . $cd['title'] . " - <a href='download.php?cd=" . $cd['id'] . "&c=" . urlencode($code) . "'>" . $cd['download_link'] . "</a></li>";
				}
			} else {
				$msg .= "Your email or purchase code are incorrect.";
				$showForm = true;
			}
		} else {
			$msg .= "You have already downloaded all your cds.";
			$showForm = true;
		}
	} 
}

if ( $showForm ) {
	if ( isset( $msg ) ) {
		echo "<div style='color: red'>" . $msg . "</div>";
	}
	?>
		<form action="purchases.php" method="post">
			<p>
				Please enter your email and purchase code to access your downloads.<br />
				Email: <input type="text" name="email" id="email" /><br />
				Code: <input type="text" name="code" id="code" /><br />
				<input type="hidden" name="id" value="<?=htmlentities( $purchaseID )?>" />
				<input type="submit" name="submit" value="submit" id="submit" />
			</p>
		</form>
<? } ?>
						</div>
					
					</section><!--end component-->

                    </section><!--end tz-content-->

                  <aside id="right-sidebar" class="span4 right-sidebar"><!--end sidebar-nav-->

                    </aside><!--end right-sidebar-->

                    <div class="clr"></div>
                </section><!--end tz-content-wrap-->

            </div><!--end tz-inner-->
            
        </div><!--end container-fluid-->

    </section><!--end tz-main-body-->

</section><!--end tz-main-->

<? include 'inc/footer.html'; ?>

<script>
	$(function() {
		$("#submit").click( function() {
			if ($("#email").val() == '' || $("#code").val() == '') {
				alert("You must enter an email and code.");
				return false;
			}
		});
	});
</script>
