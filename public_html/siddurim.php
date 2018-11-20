<?
// TODO, update ./purchas_siddurim.php to use the new models to charge the card.
include_once( __DIR__ .'/under_construction.php' );
die();

$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Siddurim</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
            .page-break {
                page-break-after: always;
            }
        </style>
        <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
        <script type="text/javascript">
            $( function() {
                var siddur = 25.00;
                $(".qty").blur( function() { 
                	var elem = $(this).parent().find('.total');
                	var qty = 0;
                	$(this).parent().children('.qty').each( function() { 
                		var val = $(this).val();
                		if ( val )
                			qty += parseInt( val );
                	});
                    total = (qty * siddur);
                    elem.text( total );
                });
                
                $("#siddur").submit( function() {
                    return confirm('Your credit card will be charged!\nAre you sure you want to continue?');
                })
            });
        </script>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Siddurim</h1>
        <?
        if ( isset( $_GET['msg'] ) ) {
            echo "<div align='center' style='color:red'>";
            echo $_GET['msg'];
            echo "</div>";
        }
        $price = 25;
        require_once 'class.adminSchools.php';
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        
        echo "<form action='purchase_siddurim.php' method='post' id='siddur'>";
        foreach ( $schools as $school_id => $school ) {
        	include 'get_siddurim_info.php';			
            ?>
            <h2><?=$school?></h2>
            IYH we will be sending you:<br />
            <?=$boys?> Blue Siddurim<br />
            <?=$girls?> Purple Siddurim<br />
            <? 
            if ( $blue || $purple ) {
	            if ( $blue && $purple ) {
	            	echo $blue . " additional Blue siddurim that you have purchased.<br />";
	            	echo $purple . " additional Purple siddurim that you have purchased.<br />";
	            } else {
	            	echo ($blue ? $blue : $purple) . " additional " . ($blue ? 'blue' : 'purple') . " siddurim that you have purchased.<br />";
				}
			}
			?>
            <br />
            Please use the following form to order more siddurim.<br />
            Each Siddur costs $36 but we are offering it to you for $<?=$price?>.<br /><br />
            <div class='siddurim'>
                Blue Siddurim Qty: <input type="text" name="blueQty<?=$school_id?>" class="qty" size="3"><br />
                Purple Siddurim Qty: <input type="text" name="purpleQty<?=$school_id?>" class="qty" size="3"><br />
                Total: $<span name="total<?=$school_id?>" class="total">0</span><br />
            </div>
            <? } ?>
            <br /><input type="submit" name="submit" value="submit" id="submit" />
        </form>
    </body>
</html>