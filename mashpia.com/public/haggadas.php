<?
// TODO, update purchas_haggadas.php to use the new models to charge the card.
include_once( __DIR__ .'/under_construction.php' );
die();

$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Haggadas</title>
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
                var haggada = 14.99;
                $(".qty").blur( function() {
                    var qty = $(this).val();
                    $(this).next().next("span").html( qty * haggada );
                });
                
                $("#haggada").submit( function() {
                    return confirm('Your credit card will be charged!\nAre you sure you want to continue?');
                })
            });
        </script>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Haggadas</h1>
        <?
        if ( isset( $_GET['msg'] ) ) {
            echo "<div align='center' style='color:red'>";
            echo $_GET['msg'];
            echo "</div>";
        }
        $price = 14.99;
        require_once 'class.adminSchools.php';
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        
        echo "<form action='purchase_haggadas.php' method='post' id='haggada'>";
        foreach ( $schools as $school_id => $school ) {
            include 'haggada.inc.php';
            //find out if school has already purchased any extra haggadas
            $sql = "select sum(paid) as total
                    from haggada_purchases 
                    where school_id = " . $school_id;
            $result = mysql_query( $sql );
            $row = mysql_fetch_assoc( $result );
            $qty = $row['total'] / $price;
            ?>
            <h2><?=$school?></h2>
            IYH on Chof Beis Adar we will be sending you:<br />
            <?=$registered?> Haggadah’s for your registered Chayolim<br />
            <?=$teachers?> for your teachers<br />
            1 for your base commander<br />
            <? if ( $qty > 0 ) echo $qty . " additional haggados that you have purchased.<br />"; ?>
            Total: <?=$registered + $teachers + 1 + ($qty > 0 ? $qty : 0)?> Haggadas<br />
            <br />
            Please use the following form to order more haggadas.<br />
            Each Haggada costs $19.99 but we are offering it to you for $<?=$price?>.<br /><br />
                Qty: <input type="text" name="qty<?=$school_id?>" class="qty" size="6"><br />
                Total: $<span name="total<?=$school_id?>" class="total">0</span><br />
            <? } ?>
            <br /><input type="submit" name="submit" value="submit" id="submit" />
        </form>
    </body>
</html>