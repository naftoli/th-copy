<?php
ini_set('display_errors', 1);
if ((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') || $_SERVER['SERVER_PORT'] != 443) {
    header("Location: https://mashpia.com/chidon_school_reg.php");
}

$admin_auth = array('school'); 
require('header.php');

require_once 'class.adminSchools.php';       
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
if ($admin_user['auth'] == 'super') $schools[82] = 'Avrohom Academy';

require_once 'class.globalSettings.php';
$year = GlobalSettings::getChidonYear();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Register Chidon Chaperones</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <style type='text/css'>
            table {font-size: 12px;}
            th, td {padding: 3px 10px;}
            caption {border-bottom: dashed 1px black;}
            td div.fullProgram {
                font-size: 14px;
                line-height: 1.3;
            }
            td div.fullProgram ul {
                margin-left: 10px;
                list-style-type: circle;
                font-style: italic;
            }
            a.button{display: inline-block;}
            a#next_page{float: right;margin-bottom: 20px;}
            a#prev_page{float: left;}
        </style>
    </head>

    <body>
        <? include('admin_header.php'); ?>
        <h1>Register Chidon Chaperones</h1>
        
        <p style="font-size: 16px; font-weight: bold; color: red;">
            <i>Disclaimer: Your students will not be able to enroll for shabbaton before you complete registration for your school's chaperones.</i>
        </p>
        
        <?php foreach ($schools as $id => $school) : ?>
            <h3><?=$school?></h3>
            
            <h2>Registered Chaperones</h2>
            
            <?php
            $chaps = array();
            $sql = "SELECT * FROM th_chidon_schools ts "
                    ."JOIN th_chidon_chaps tc USING (school_id) "
                    ."WHERE ts.registered = 1 "
                    ."AND ts.year = " . $year . " "
                    ."AND tc.year = " . $year . " "
                    ."AND ts.school_id = " . $id;
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                $chaps[] = $row;
            }
            if (!empty($chaps)) {
                ?>
                <table>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Full Program</th>
                        <th>Sweater Size</th>
                        <th></th>
                    </tr>
                    
                <?php
                foreach ($chaps as $chap) { ?>
                    <tr id="<?=$chap['th_chidon_chap_id']?>">
                        <td><?=$chap['first_name']?></td>
                        <td><?=$chap['last_name']?></td>
                        <td><?=$chap['email']?></td>
                        <td><?=$chap['phone']?></td>
                        <td><?=$chap['full_program'] ? 'yes' : 'no';?></td>
                        <td><?=$chap['sweater_size']?></td>
                        <td><a href='#' class='del'>delete</a></td>
                    </tr>
                <? } ?>
                </table>
            <? } else {
                echo "No Registered Chaperones.";
            } ?>
            
            <h2>Add Chaperone</h2>
            <form id="<?=$id?>">
                <p>
                    Number of Chaperones adding:
                    <select name="chapNum" class="chapNum">
                    <? for ($i = 1; $i < 6; $i++) { ?>
                        <option value="<?=$i?>"><?=$i?></option>
                    <? } // end dropdown from 1 to 6 ?>
                    </select>
                </p>
                <table>
                    <tr>
                        <td>First Name</td>
                        <td><input type="text" name="fname" class="fname" required /></td>
                    </tr>
                    <tr>
                        <td>Last Name</td>
                        <td><input type="text" name="lname" class="lname" required /></td>
                    </tr>
                    <tr>
                        <td>Cell Number</td>
                        <td><input type="text" name="number" class="number" required /></td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td><input type="email" name="email" class="email" required /></td>
                    </tr>
                    <tr>
                        <td>DOB</td>
                        <td>
                            <select name="mm" class="mm" required>
                                <option value="0">Month</option>
                                <? for ($i = 1; $i < 13; $i++) { ?>
                                    <option value="<?=($i < 10 ? "0" : "").$i?>"><?=$i?></option>
                                <? } ?>
                            </select>
                            <select name="dd" class="dd" required>
                                <option value="0">Day</option>
                                <? for ($i = 1; $i < 32; $i++) { ?>
                                    <option value="<?=($i < 10 ? "0" : "").$i?>"><?=$i?></option>
                                <? } ?>
                            </select>
                            <select name="yy" class="yy" required>
                                <option value="0">Year</option>
                                <? for ($i = 1950; $i < 2010; $i++) { ?>
                                    <option value="<?=$i?>"><?=$i?></option>
                                <? } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <h3>Accomodation Info</h3>
                        </td>
                    </tr>
                    <tr>
                        <td>Name</td>
                        <td><input type="text" name="accName" class="accName" required /></td>
                    </tr>
                    <tr>
                        <td>Address</td>
                        <td><input type="text" name="accAddress" class="accAddress" required /></td>
                    </tr>
                    <tr>
                        <td>Cross Streets</td>
                        <td><input type="text" name="accCrossSt" class="accCrossSt" required /></td>
                    </tr>
                    <tr>
                        <td>Phone Number</td>
                        <td><input type="text" name="accPhone" class="accPhone" required /></td>
                    </tr>
                    <tr>
                        <td>Vehicle</td>
                        <td>
                            <input type="radio" name="vehicle<?=$id?>" class="vehicle" value="0" /> NO<br />
                            <input type="radio" name="vehicle<?=$id?>" class="vehicle" value="1" /> YES
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="fullProgram">
                                <br />
                                The full Chidon program includes:
                                <ul>
                                    <li>Trips</li>
                                    <li>Meals</li>
                                    <li>Transportation</li>
                                    <li>Sweatshirt</li>
                                </ul>
                                <br />
                                Will you be joining us for the full chidon program (4th and 5th Grade Thursday and Friday)?
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="right"><input type="radio" name="full<?=$id?>" class="full" value="1" /></td>
                        <td>
                            YES, I would like to join the program and pay $100.<br />
                            My Sweater size is:
                            <select name="s_size_yes" class="s_size_yes">
                                <option value="s">Small</option>
                                <option value="m">Medium</option>
                                <option value="l">Large</option>
                                <option value="xl">XLarge</option>
                                <option value="xxl">XXLarge</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td align="right"><input type="radio" name="full<?=$id?>" class="full" value="0" /></td>
                        <td>NO, I will not be attending the full Chidon program with my students,
                        However I understand that I will be on call in NY throughout the entire Shabbaton.</td>
                    </tr>
                    <tr class="showSize" style="display:none">
                        <td align="right"><input type="checkbox" name="sweater" class="sweater" /></td>
                        <td>
                            Although I am not joining the program, I would like to purchase a sweater for $20.<br />
                            My Sweater size is:
                            <select name="s_size_no" class="s_size_no">
                                <option value="s">Small</option>
                                <option value="m">Medium</option>
                                <option value="l">Large</option>
                                <option value="xl">XLarge</option>
                                <option value="xxl">XXLarge</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                </table>
                <br />
                <p>
                    Total Due: <span class="total">$0</span><br />
                    <div class="showAgree" style="display:none">
                        <table>
                            <tr>
                                <td>Card Number:</td>
                                <td><input name="cardnumber" type="text" class="cardnum" placeholder="Card Number" /></td>
                            </tr>
                            <tr>
                                <td>Expiration:</td>
                                <td><input name="exp" type="text" class="exp" placeholder="Expiration MMYY" /></td>
                            </tr>
                            <tr>
                                <td>Zip Code:</td>
                                <td><input name="zip" type="text" class="zip" placeholder="Zip Code" /></td>
                            </tr>
                        </table>
                        <br />
                    </div>
                    
                    <div>
                        <input type="checkbox" name="terms" class="terms" /> 
                        I have read <a href="https://docs.google.com/document/d/1ex-JrDYEq8hjUcusd4LQCbRn9QWjAQaHWCBoYPHsEkU/edit?usp=sharing" target="_blank">this page</a>
                        and accept the responsibilities of the chaperone.
                    </div>
                    <br />
                    <input type="hidden" name="total" class="totalCharge" />
                    <input type="submit" name="submit" class="submit" value="Add Chaperone" />
                </p>
                    <tr>
                        <td</td>
                        <td></td>
                    </tr>
                    
                </table>
            </form>
        <?php endforeach; ?>
        <!--<div class="modal" id="modal">
            <span class="close">&times;</span>
            <form id="modalForm">
                <table>
                    <tr>
                        <td>Name</td>
                        <td><input type="text" class="name" /></td>
                    </tr>
                    <tr>
                        <td>Cell Number</td>
                        <td><input type="text" class="number" /></td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td><input type="email" class="email" /></td>
                    </tr>
                    <tr class="xtra"></tr>
                </table>
            </form>
        </div>
        <style>
            .modal {
                display: none;
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                box-shadow: 0 0 60px 10px rgba(0, 0, 0, 0.9);
                padding: 20px;
            }
            .modal .close {
                float: right;
                cursor: pointer;
            }
        </style>-->
        <a class='button' id="next_page" href='/enrollment.php'>Activate Enrollment <i class="fa fa-arrow-right"></i></a>
    </body>
    <script>
        /*
        $(".edit").click( function(e) {
            e.preventDefault();
            var id = $(this).parent().parent().attr('id');
            var row = $(this).parent().parent();
            
            var name = $(row).find('td').eq(0).text();
            var email = $(row).find('td').eq(1).text();
            var phone = $(row).find('td').eq(2).text();
            var full = $(row).find('td').eq(3).text();
            var size = $(row).find('td').eq(4).text();
            
            //$(".modal").find('.name').val(name);
            $(".modal").find('.number').val(phone);
            $(".modal").find('.email').val(email);
            
            var html = '';
            if (full == 'no') {
                html = "<td colspan='2'><table><tr><td colspan='2'><input type='radio' name='upgrade' class='full' /> Please updgrade me to full program ($80)</td></tr>";
                if (size == '') {
                    html += "<tr><td colspan='2'><input type='radio' name='upgrade' class='sweater' /> Please add a sweater for me ($20)</td></tr>";
                }
                html += "<tr><td>My Sweater size is:</td>\
                        <td>\
                            <select class='s_size'>\
                                <option value='s'>Small</option>\
                                <option value='m'>Medium</option>\
                                <option value='l'>Large</option>\
                                <option value='xl'>XLarge</option>\
                                <option value='xxl'>XXLarge</option>\
                            </select></td></tr></table></td>";
            }
            //alert(html);
            $(".modal").find('.xtra').empty();
            $(".modal").find('.xtra').append(html);
            $(".modal").show();
        });
        
        $(".modal .close").click( function() {
            $(".modal").hide();
        });
        */
        $('body').mousedown(function(e) {
            var clicked = $(e.target); 
            if (clicked.is('.modal') || clicked.parents().is('.modal')) {
                return;
           } else {
             $('.modal').hide();
           }
        });
        
        var year = <?=$year?>;        
        $(".chapNum").change( function() {
            var val = parseInt($(this).val());
            // find num of current tables
            var table = $(this).parent().next('table');
            var all = table.siblings('table');
            var num = all.length;
            num++; // add first table
            
            if (num < val) {
                var diff = val - num;
                for (var i = 0; i < diff; i++) {
                    table.after(table.clone());
                }
                var newTables = table.siblings('table');
                var numNew = newTables.length;
                newTables.each( function(i, v) {
                    var name = $(v).find(".full").attr('name');
                    $(v).find(".full").attr('name', name + '_' + i);
                    
                    var vehicle_name = $(v).find(".vehicle").attr('name');
                    $(v).find(".vehicle").attr('name', vehicle_name + '_' + i);
                });
            } else if (num > val) {
                for (var i = num; i > val; i--) {
                    all.eq(i-2).remove();
                }
            }
        });
        
        $(".full").live('click', function() {
            if (parseInt($(this).val())) {
                $(this).parent().parent().parent().find(".showSize").hide();
            } else {
                $(this).parent().parent().parent().find(".showSize").show();
            }
            calcTotal($(this).parent().parent().parent().parent().parent());
        });
        
        $(".sweater").live('click', function() {
            calcTotal($(this).parent().parent().parent().parent().parent());
        });
        
        $(".del").click( function(e) {
            e.preventDefault();
            var conf = confirm('Are you sure you want to delete this chaperone?');
            if (conf) {
                var id = $(this).parent().parent().attr('id');
                var tr = this;
                $.post('ajax/delChap.php', { id : id }, function( success) {
                    if (parseInt(success) == 1) {
                        $(tr).parent().parent().remove();
                    } else {
                        alert('Error deleting.');
                    }
                });
            }
        });
        
        function calcTotal(elem) {
            var total = 0;
            var tables = $(elem).find('table');
            tables.each( function(i, v) {
                var val = parseInt($(v).find(".full:checked").val());
                if (val) {
                    total += 100;
                } else {
                    var sweater = $(v).find('.sweater');
                    if ($(sweater).is(":checked")) {
                        total += 20;
                    }
                }
            });
            $(elem).find(".total").text('$' + total);
            $(elem).find(".totalCharge").val(total);
            if (total) {
                $(elem).find(".showAgree input").attr('required', true);
                $(elem).find(".showAgree").show();
                $(elem).find(".submit").val("Pay and Add Chaperone");
            } else {
                $(elem).find(".showAgree input").attr('required', false);
                $(elem).find(".showAgree").hide();
                $(elem).find(".submit").val("Add Chaperone");
            }
        }
        
        $("form").submit( function(e) {
            e.preventDefault();
            var form = this;
            var school = $(this).attr('id');
            var agree = $(form).find('.terms:checked').length;
            if (!agree) {
                alert('You must agree to terms.');
                return false;
            }
            var amount = parseInt($(form).find('.totalCharge').val());
            var ccnum, ccexp, cczip;
            if (amount) {
                ccnum = $(form).find('.cardnum').val();
                ccexp = $(form).find('.exp').val();
                cczip = $(form).find('.zip').val();
            } else {
                ccnum = 0;
                ccexp = 0;
                cczip = 0;
            }
            
            var fields = ['fname','lname','number','email','mm','dd','yy','accName','accAddress','accPhone','accCrossSt'];
            
            var tables = $(form).children('table');
            var chaperones = [];
            var error = false;
            tables.each( function(i, v) {
                var user = {};
                for (var f in fields) {
                    var elem = '.' + fields[f];
                    var val = $(this).find(elem).val().trim();
                    if (val == 0 || val == '') {
                        alert('All Fields are Mandatory.');
                        error = true;
                        return false;
                    }
                    var prop = fields[f];
                    user[prop] = val;
                }
                //user.name = $(this).find('.name').val().trim();
                //user.email = $(this).find('.email').val().trim();
                //user.number = $(this).find('.number').val().trim();
                if ($(this).find('.vehicle:checked')) {
                    user.vehicle = parseInt($(this).find('.vehicle:checked').val());
                } else {
                    alert('You must indicate for each chaperone whether they are using a vehicle or not.');
                    error = true;
                    return false;
                }
                
                if ($(this).find('.full:checked')) {
                    user.full = parseInt($(this).find('.full:checked').val());
                } else {
                    alert('You have not indicated for every chaperone whether they are joining the full program or not.');
                    error = true;
                    return false;
                }
                if (user.full == 1) {
                    user.s_size = $(this).find(".s_size_yes").val();
                } else {
                    if ($(this).find('.sweater').is(":checked")) {
                        user.s_size = $(this).find(".s_size_no").val();
                    } else {
                        user.s_size = 0;
                    }
                }
                chaperones.push(user);
                console.log(user);
            });
            /*
            if (amount && !$(form).find('.agree:checked').length) {
                alert('You must indicate your acceptance of the charge.');
                return false;
            }
            */
            // create chaperones
            if (!error) {
                $.post('ajax/addChaperone.php', {
                    year : year,
                    school : school,
                    info : chaperones,
                    amount : amount,
                    ccnum : ccnum,
                    ccexp : ccexp,
                    cczip : cczip
                }, function( error ) {
                    if (parseInt(error) != 0) alert(error);
                    else {
                        alert("Chaperone(s) added.");
                        location.reload(true);
                    }
                });
            }

            return false;
        });
    </script>
</html>