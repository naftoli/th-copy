<? 
include 'inc/head.php';

$cds = array();
if (!empty($_SESSION['cart'])) {
	require_once 'db.php';
	$sql = "select * from cds where id in (" . implode(',', $_SESSION['cart']) . ") order by ord";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$cds[] = $row;
	}
}
?>

<section id="tz-main"><!--start tz-main-->

    <section class="tz-main-body">

        <div class="container-fluid"><!--start container-fluid-->

            <div class="tz-inner"><!--start tz-inner-->            	

                <section class="tz-content-wrap row-fluid">

                  <section id="tz-content" class="span4">

                        <section id="tz-component">

<? if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) : ?>

<div class="container">
    <div class="row">
        <div class="col-sm-12 col-md-10 col-md-offset-1">
            <table class="table table-hover" style="margin: auto; width: 70%">
            	<!--
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">Total</th>
                        <th> </th>
                    </tr>
                </thead>
                -->
                <tbody>
                	
                	
                	<? if (in_array(100, $_SESSION['cart'])) : ?>
                	<tr>
                        <td class="col-sm-8 col-md-6">
                        <div class="media">
                            <a class="thumbnail pull-left" href="#"> 
                            	<img class="media-object" src="http://icons.iconarchive.com/icons/wwalczyszyn/iwindows/96/Music-Library-icon.png" style="width: 72px; height: 72px;"> </a>
                            <div class="media-body">
                                <h4 class="media-heading">Promo - All 11 CDs</a></h4>                           
                            </div>
                        </div></td>
                        <!--
                        <td class="col-sm-1 col-md-1" style="text-align: center">
                        	<input type="text" class="form-control" value="1" style="width: 30px; vertical-align: left;" disabled="disabled">
                        </td>
                        -->
                        <td class="col-sm-1 col-md-1 text-center"><strong></strong></td>
                        <td class="col-sm-1 col-md-1 text-center" style="color: black;"><strong><span class='price'>
                        	49.99</span></strong></td>
                        <td class="col-sm-1 col-md-1">
                        <button type="button" class="btn btn-danger remove" id="100">
                            <span class="glyphicon glyphicon-remove"></span> Remove
                        </button></td>
                    </tr>
                	<? endif; ?>
                	
                	
                	<? foreach ($cds as $cd) { ?>
                	
                    <tr>
                        <td class="col-sm-8 col-md-6">
                        <div class="media">
                            <a class="thumbnail pull-left" href="#"> 
                            	<img class="media-object" src="http://icons.iconarchive.com/icons/wwalczyszyn/iwindows/96/Music-Library-icon.png" style="width: 72px; height: 72px;"> </a>
                            <div class="media-body">
                                <h4 class="media-heading"><a href="albums/story.php?id=<?=$cd['id']?>"><?=$cd['title']?></a></h4>                           
                            </div>
                        </div></td>
                        <!--
                        <td class="col-sm-1 col-md-1" style="text-align: center">
                        	<input type="text" class="form-control" value="1" style="width: 30px; vertical-align: left;" disabled="disabled">
                        </td>
                        -->
                        <td class="col-sm-1 col-md-1 text-center"><strong></strong></td>
                        <td class="col-sm-1 col-md-1 text-center" style="color: black;">
                        	<strong>
                        		<span class='price'>
		                        	<?
		                        	if (in_array($cd['id'], array(12,13))) {
		                        		echo $cd['discount_price'];
									} else {
		                        		echo $cd['price'];
									}
		                        	?>
                        		</span>
                        	</strong>
                        </td>
                        <td class="col-sm-1 col-md-1">
                        <button type="button" class="btn btn-danger remove" id="<?=$cd['id']?>">
                            <span class="glyphicon glyphicon-remove"></span> Remove
                        </button></td>
                    </tr>
                    
                    <? } ?>
                    
                    
                    <? if (isset($_SESSION['coupon']) && $_SESSION['coupon'] == 'msth5775') : ?>
                    <tr>
                        <td>   </td>
                        <td><h5>Coupon Code Applied</h5></td>
                        <? 
                        if ($_SESSION['cart'][0] == 100) {
                        	$amount = 10.00; 
						} else {
							$amount = count($_SESSION['cart']);
						}
						?> 
                        <td class="text-right"><h5><strong>-<?=$amount?>.00</strong></h5></td>
                        <td>   </td>
                    </tr>
                    <? else : ?>
                    
                    
                    <tr>
                        <td>   </td>
                        <td><h5>Coupon Code</h5></td>
                        <td class="text-right"><input type="text" id="coupon" /></td>
                        <td>
                        	<button type="button" class="btn btn-danger" id="apply">
                            	Apply
                        	</button>
                        </td>
                    </tr>
                    <? endif; ?>  
                                     
                    <tr>
                        <td>   </td>
                        <td><h5>Total</h5></td>
                        <td class="text-right"><h5><strong id="total"></strong></h5></td>
                        <td>   </td>
                    </tr>
                  
                    <tr>
                        <td>   </td>
                        <td>   </td>
                        <td style="width: auto;">
	                        <a href="index.php">
	                        	<button type="button" class="btn btn-warning" id="continue">
	                            	Continue Shopping
	                        	</button>
	                        </a>
	                    </td>
                        <td>
                        	<a href="checkout.php">
                         		<button type="button" class="btn btn-success">
                          			Checkout
                          		</button>
                          	</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<? else : ?>
<div style='color: black'>There are no items in your cart.<br />
	Click <a href="index.php">here</a> to add items to your cart.</div>
<? endif; ?>                     

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
	$( function() {
		//calculate total on page load
		calcTotal();
		
		$(".remove").click( function() {
			var remove = confirm("Are you sure you want to delete this?");
			if (remove) {
				var id = $(this).attr('id');
				$.post('remove.php', {id : id}, function(data) {
					if (data == 1) {
						location.href = 'cart.php';
					} else {
						alert(data);
					}
				});
			}
		});
		
		$("#apply").click( function() {
 			var code = $(this).parent().prev().find("#coupon").val().trim();
			if (code == 'msth5775' || code == 'MSTH5775') {
				$.post('applyCoupon.php', {code : code}, function(data) {
					if (data == 1) {
						location.href = 'cart.php';
					} else {
						alert(data);
					}
				});
			} else {
				alert("Invalid Coupon");
			}
		});
	});
	
	function calcTotal() {
		var price = $(".price");
		var total = 0;
		$.each(price, function() {
			var val = $(this).text();
			total += parseFloat(val);
		});
		//check for coupon
		<? if (isset($_SESSION['coupon']) && $_SESSION['coupon'] == 'msth5775') : ?>
			if (total == 39.99) total = 29.99;
			else total -= price.length;
		<? endif; ?>
		$("#total").text('$' + total);
	}
</script>
