<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Order Form</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
        <script type="text/javascript">
            $(function() {
                $("#order_form").submit(function() {
                    var errors = '';
                    if ($("#address").val() == '' || $("#city").val() == '' || $("#state").val() == '' || $("#zip").val() == '') {
                            errors += "School Name, Base Commander, and Address are mandatory.";
                    }
                    if (($("#posters").val() == '') && ($("#brochures").val() == '')) {
                        errors += "\nYou have not ordered anything!";
                    }
                    if (!$("#terms").is(":checked")) {
                        errors += "\nPlease indicate that you accept the credit card fees.";
                    }
                    if (errors != '') {
                        alert(errors);
                        return false;
                    }
                });
                
                getAddress();
                 
                $("#school_name").change(function() {
                    getAddress();
                });
            });
            
            function getAddress() {
                $.post('ajax/getAddress.php', {id : $("#school_name").val()}, function(success) {
                    var address = $.parseJSON(success);
                    $("#address").val(address.shipping_address1);
                    $("#address2").val(address.shipping_address2);
                    $("#city").val(address.shipping_city);
                    $("#state").val(address.shipping_state);
                    $("#zip").val(address.shipping_postal);
                    $("#country").val(address.shipping_country);
                });
            }
        </script>
        <style type='text/css'>
            table {
                font-size: 14px;
            }
            th, td {
                padding: 3px 10px;
            }
            .page-break {
                page-break-after: always;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Order Form</h1>
        
        <?
        if (isset($_POST['submit'])) {
            
            $school_id = (int)$_POST['school_name'];
            $bc = $admin_user['admin_id'];
            $shipping['address'] = mysql_real_escape_string(trim($_POST['address']));
            $shipping['address2'] = mysql_real_escape_string(trim($_POST['address2']));
            $shipping['city'] = mysql_real_escape_string(trim($_POST['city']));
            $shipping['state'] = mysql_real_escape_string(trim($_POST['state']));
            $shipping['zip'] = mysql_real_escape_string(trim($_POST['zip']));
            $shipping['country'] = mysql_real_escape_string(trim($_POST['country']));
            $posters = (int)trim($_POST['posters']);
            $brochures = (int)trim($_POST['brochures']);
            $method = mysql_real_escape_string(trim($_POST['shipping_method']));
            
            $sql = "insert into 5774_orders values('', $school_id, $bc, $posters, $brochures, '$method', null)";
            //echo $sql;
            if (mysql_query($sql)) {
                $sql = "update schools set shipping_address1 = " . $shipping['address'] . ", shipping_address2 = " . $shipping['address2'] . ", 
                        shipping_city = " . $shipping['city'] . ", shipping_postal = " . $shipping['zip'] . ", shipping_country = " . $shipping['country'] . " 
                        where school_id = " . $school_id;
                mysql_query($sql);
                echo "Your order has been recieved.<br />Thank you!";                        
            }
            
        } else {
        
            require_once 'class.adminSchools.php';
            $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
            $schools = $as->getSchools();
            
            $sqlName = "select first, last from admins where admin_id = " . $admin_user['admin_id'];
            $resName = mysql_query($sqlName);
            $rowName = mysql_fetch_assoc($resName); 
            ?>
            
            <form action="order_form.php" method="post" id="order_form">
                <table>
                    <tr>
                        <td valign="top" align="right">*Name of School:</td>
                        <td>
                            <select name="school_name" id="school_name">
                                <?
                                foreach ($schools as $id => $school) {
                                    echo "<option value='$id'>" . $school . "</option>";
                                }
                                ?>
                            </select>
                    </tr>
                    <tr>
                        <td valign="top" align="right">*Base Commander:</td>
                        <td><?=$rowName['first'] . ' ' . $rowName['last']?></td>
                    </tr>
                    <tr>
                        <td valign="top" align="right">*Shipping Address:</td>
                        <td>
                            <input type='text' name='address' id="address" size="32" /><br />
                            <input type='text' name='address2' id="address2" size="32" /><br />
                            <input type='text' name='city' size="14" id="city" />
                            <input type='text' name='state' size='2' id="state" />
                            <input type='text' name='zip' size='10' id="zip" /><br />
                            <input type='text' name='country' id="country" size="32" />
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" align="right">Date of Order:</td>
                        <td><?=date('Y-m-d')?></td>
                    </tr>
                    <tr>
                        <td valign="top" align="right">Number of Posters:</td>
                        <td><input type='text' name='posters' size='3' id="posters" /></td>
                    </tr>
                    <tr>
                        <td valign="top" align="right">Number of Registration Brochures:</td>
                        <td><input type='text' name='brochures' size='3' id="brochures" /></td>
                    </tr>
                    <tr>
                        <td valign="top" align="right">Shipping Method:</td>
                        <td>
                            <input type="radio" name="shipping_method" value="pick_up" class="shipping_method" checked="checked" /> Pick Up<br />
                            <input type="radio" name="shipping_method" value="overnight" class="shipping_method" /> Overnight<br />
                            <input type="radio" name="shipping_method" value="second_day" class="shipping_method" /> 2nd Day Air<br />
                            <input type="radio" name="shipping_method" value="third_day" class="shipping_method" /> 3rd Day Air<br />
                            <input type="radio" name="shipping_method" value="ground" class="shipping_method" /> Ground<br />                        
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center">
                            <input type="checkbox" name="terms" id="terms" />  I understand that the shipping costs<br />will be charged to my credit card. 
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center">
                            <input type="submit" name="submit" value="submit" />
                            <input type="reset" name="reset" value="reset" />
                        </td>
                    </tr>
                </table>
            </form>
        <? } ?>        
    </body>
</html>