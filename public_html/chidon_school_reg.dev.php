<?php
ini_set('display_errors', 1);
// redirect to https
if ((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') || $_SERVER['SERVER_PORT'] != 443) {
    header("Location: https://mashpia.com/chidon_school_reg.php");
}
//********************* AUTHENTICATION *********************//
$admin_auth = array('school'); 
require('header.php');

//********************* LOAD THE LIST OF SCHOOLS *********************//
require_once 'class.adminSchools.php';       
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
if ($admin_user['auth'] == 'super') $schools[82] = 'Avrohom Academy'; // add A Academy to test for superusers...
// and get the chidon year....
require_once 'class.globalSettings.php';
$year = GlobalSettings::getChidonYear();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Chidon Chaperones | Tzivos Hashem</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<!--        Modal and other form elements -->
        <link href="/styles/admin/modal.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/forms.css" rel="stylesheet" type="text/css"/>
        <!--        Rotating Spinner, grey dropdowns and fancy checkboxes... -->
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
        <style type='text/css'>
            table {font-size: 12px;width: 100%;}
            th, td {padding: 3px 5px;}
            caption {border-bottom: dashed 1px black;}
            .options{text-align: center;}
            
            div.fullProgram {
                font-size: 14px;
                line-height: 1.5;
            }
            div.fullProgram ul {
                margin-left: 10px;
                list-style-type: circle;
                font-style: italic;
            }
            a.button{display: inline-block;}
            a#next_page{float: right;margin-bottom: 20px;}
            a#prev_page{float: left;}
            .warning{
                font-size: 16px; font-weight: bold; color: red;
            }
            div#chaperones {
                margin-bottom: 25px;
            }
            div#chap_modal h3 {
                margin: 10px 0px;
                padding: 3px 10px;
                font-size: 1.2em;
                border-bottom: 1px solid #888;
            }
            .s-size {
                width: 95%;
                margin-left: 5%;
                margin-top: 10px;
                display: none;
            }
            .s-size select {
                width: auto;
            }
            .modal-content {
                width: 850px;
                margin: 5% auto;
            }
        </style>
    </head>

    <body>
        <? include('admin_header.php'); ?>
        <?php include($_SERVER['DOCUMENT_ROOT']."/chidon_passwords.php"); // require a password to use this page... ?>
        <h1>Chidon Chaperones</h1>
        
        <p class="warning">
            <i>Disclaimer: Your students will not be able to enroll for shabbaton before you complete registration for your school's chaperones.</i>
        </p>
        
        <? if(count($schools) == 1) { ?>
            <select id="school_id" name="school_id" class="hidden" disabled>
                <option value="<?=array_keys($schools)[0]?>"><?=array_values($schools)[0]?></option>
            </select>
        <? } else { ?>
            <div class="options">
                <div class="row">
                    <i class="fa fa-university" aria-hidden="true"></i> School: 
                    <select id="school_id" name="school_id">
                        <option value="" selected>All Schools</option>
                        <? foreach($schools as $school_id => $school_name){?>
                            <option value="<?=$school_id?>"><?=$school_name?></option>
                        <?}?>
                    </select>
                </div>
            </div>
        <?}?>
        <br/>
        <div class="options">
            <a class="button" id="generate_chaps_report">
                <i class="fa fa-refresh" aria-hidden="true"></i>
                <?=count($schools) == 1 ? "Refresh" : "Refresh / Load";?> School Chaperones
            </a>
<!--            <a class="button" id="generate_csv">
                <i class="fa fa-save" aria-hidden="true"></i> Export to CSV (Excel)
            </a>-->
        </div>
        
        <h2>Registered Chaperones</h2>
        
        <div id="chaperones"></div>
        
        <div id="actions" class="options">
            <a class="button" id="create_chaperone">
                <i class="fa fa-plus" aria-hidden="true"></i> Create Chaperone
            </a>
        </div>
        
        <div class="modal" id="chap_modal">
            <div class="modal-content">
                <h1>
                    <span id="heading">Create</span> Chaperone
                    <span class="close" id="update_cc_exit">×</span>
                </h1>
                <? if ($admin_user['auth'] == "super") { ?>
                    <p class="warning create_chidon_info">
                        <i>Disclaimer: The chaperone will be assigned to the school currenty selected on the master dropdown. Not always the one who's list you are looking at. Please refresh the report to be 100% sure you are on the correct school.</i>
                    </p>
                <? } ?>
                <form>
                    <h3>Chaperone Info</h3>
                    <input type="hidden" id="action" value="create"/>
                    <input type="hidden" id="chap_id" value=""/>
                    <div class="input_group input_half">
                        <label>
                            First Name<br/>
                            <input type="text" id="first_name" name="first_name" required />
                        </label>
                    </div>
                    <div class="input_group input_half">
                        <label>
                            Last Name<br/>
                            <input type="text" id="last_name" name="last_name" required />
                        </label>
                    </div>
                    <div class="input_group input_half">
                        <label>
                            Cell Number<br/>
                            <input type="text" id="number" name="number" required />
                        </label>
                    </div>
                    <div class="input_group input_half">
                        <label>
                            E-Mail<br/>
                            <input type="text" id="email" name="email" required />
                        </label>
                    </div>
                    <div class="input_group input_half ">
                        <label>
                            DOB<br/>
                            <input type="date" id="dob" name="dob" min="1950-01-01" max="2009-12-31" required />
                        </label>
                    </div>
                    <div class="input_group input_half edit_chidon_info">
                        <label>
                            Sweater Size<br/>
                            <select name="s_size" class="s_size">
                                <option value="" disabled>Not Getting A Sweater</option>
                                <option value="s">Small</option>
                                <option value="m">Medium</option>
                                <option value="l">Large</option>
                                <option value="xl">XLarge</option>
                                <option value="xxl">XXLarge</option>
                            </select>
                        </label>
                    </div>
                    
                    <h3>Accomodation Info</h3>
                    <div class="input_group input_half">
                        <label>
                            Name<br/>
                            <input type="text" id="accName" name="accName" required />
                        </label>
                    </div>
                    <div class="input_group input_half">
                        <label>
                            Address<br/>
                            <input type="text" id="accAddress" name="accAddress" required />
                        </label>
                    </div>
                    <div class="input_group input_half">
                        <label>
                            Cross Streets<br/>
                            <input type="text" id="accCrossSt" name="accCrossSt" required />
                        </label>
                    </div>
                    <div class="input_group input_half">
                        <label>
                            Phone Number<br/>
                            <input type="text" id="accPhone" name="accPhone" required />
                        </label>
                    </div>
                    <div class="input_group input_half">
                        <label>
                            Vehicle<br/>
                            <input type="radio" name="vehicle" class="vehicle vehicle_0" value="0" /> NO
                            <input type="radio" name="vehicle" class="vehicle vehicle_1" value="1" /> YES
                        </label>
                    </div>
                    
                    <div class="create_chidon_info">
                        <h3>Chidon Info</h3>
                        <div class="fullProgram">
                            <strong>The full Chidon program includes:</strong><br />
                            Trips, Meals, Transportation and an awesome Sweatshirt!<br />
                        </div>
                        <div class="input_group input_full">
                            <label>
                                Will you be joining us for the full chidon program (4th and 5th Grade, Thursday and Friday)?<br/><br/>
                                <input type="radio" name="full" class="full full_1" value="1" />
                                YES, I would like to join the program and pay $100.<br />
                                <div class="s-size">
                                    My Sweater size is:
                                    <select name="s_size_yes" class="s_size_yes">
                                        <option value="s">Small</option>
                                        <option value="m">Medium</option>
                                        <option value="l">Large</option>
                                        <option value="xl">XLarge</option>
                                        <option value="xxl">XXLarge</option>
                                    </select>
                                </div>
                            </label>
                        </div>

                        <div class="input_group input_full">
                            <label>
                                <input type="radio" name="full" class="full full_0" value="0" />
                                NO, I will not be attending the full Chidon program with my students. However I understand that I will be on call in NY throughout the entire Shabbaton.
                                
                                <div class="s-size">
                                    <input type="checkbox" name="sweater" class="sweater" />
                                    Although I am not joining the program, I would like to purchase a sweater for $20.<br />
                                    My Sweater size is:
                                    <select name="s_size_no" class="s_size_no">
                                        <option value="s">Small</option>
                                        <option value="m">Medium</option>
                                        <option value="l">Large</option>
                                        <option value="xl">XLarge</option>
                                        <option value="xxl">XXLarge</option>
                                    </select> 
                                </div>

                            </label>
                        </div>
                        <div class="input_group input_full">
                            <strong>Total Due: $<span class="total">0</span></strong>
                        </div>
                        <div class="showAgree" style="display:none">
                            <div class="input_group input_full">
                                <label>
                                    Card Number<br/>
                                    <input type="text" id="cardnumber" name="cardnumber" class="cardnum" placeholder="4111 1111 1111 1111" />
                                </label>
                            </div>
                            <div class="input_group input_half">
                                <label>
                                    Expiration<br/>
                                    <input type="text" id="exp" name="exp" class="exp" placeholder="MMYY" />
                                </label>
                            </div>
                            <div class="input_group input_half">
                                <label>
                                    Zip Code<br/>
                                    <input type="text" id="zip" name="zip" class="zip" placeholder="XXXXX" />
                                </label>
                            </div>
                        </div>
                        <div class="input_group input_full">
                            <input type="checkbox" name="terms" id="terms" /> 
                            I have read <a href="https://docs.google.com/document/d/1ex-JrDYEq8hjUcusd4LQCbRn9QWjAQaHWCBoYPHsEkU/edit?usp=sharing" target="_blank">this page</a> and accept the responsibilities of the chaperone as well as pay any charges indicated above.
                        </div>
                        
                        <div class="input_group input_full" style="text-align: center">
                            <a class="button" id="chap_prev" style="float: left; display: none;">
                                <i class="fa fa-arrow-left" aria-hidden="true"></i> Previous Chaperone
                            </a>
                            <a class="button" id="chap_add">
                                <i class="fa fa-plus" aria-hidden="true"></i> Add Another Chaperone
                            </a>
                            <a class="button" id="chap_next" style="float: right; display: none;">
                                Next Chaperone <i class="fa fa-arrow-right" aria-hidden="true"></i>
                            </a>
                        </div>
                        <div style="clear: both"></div>
                    </div>
                    <div class="input_group input_full" style="text-align: center">
                        <input type="submit" name="submit" class="submit" value="Add Chaperone" />
                    </div>
                </form>
            </div>
        </div>
        
        <a class='button' id="next_page" href='/enrollment.php'>Activate Enrollment <i class="fa fa-arrow-right"></i></a>
        <script src="/js/admin/components/modal.js"></script>
        <script src="/scripts/chidon/chidon_school_reg.php.js"></script>
    </body>
    <script>
        //$(".del").click( function(e) {
        //    e.preventDefault();
        //    var conf = confirm('Are you sure you want to delete this chaperone?');
        //    if (conf) {
        //        var id = $(this).parent().parent().attr('id');
        //        var tr = this;
        //        $.post('ajax/delChap.php', { id : id }, function( success) {
        //            if (parseInt(success) == 1) {
        //                $(tr).parent().parent().remove();
        //            } else {
        //                alert('Error deleting.');
        //            }
        //        });
        //    }
        //});
        
        //function calcTotal(elem) {
        //    var total = 0;
        //    var tables = $(elem).find('table');
        //    tables.each( function(i, v) {
        //        var val = parseInt($(v).find(".full:checked").val());
        //        if (val) {
        //            total += 100;
        //        } else {
        //            var sweater = $(v).find('.sweater');
        //            if ($(sweater).is(":checked")) {
        //                total += 20;
        //            }
        //        }
        //    });
        //    $(elem).find(".total").text('$' + total);
        //    $(elem).find(".totalCharge").val(total);
        //    if (total) {
        //        $(elem).find(".showAgree input").attr('required', true);
        //        $(elem).find(".showAgree").show();
        //        $(elem).find(".submit").val("Pay and Add Chaperone");
        //    } else {
        //        $(elem).find(".showAgree input").attr('required', false);
        //        $(elem).find(".showAgree").hide();
        //        $(elem).find(".submit").val("Add Chaperone");
        //    }
        //}
        
        //$("form").submit( function(e) {
        //    e.preventDefault();
        //    var form = this;
        //    var school = $(this).attr('id');
        //    var agree = $(form).find('.terms:checked').length;
        //    if (!agree) {
        //        alert('You must agree to terms.');
        //        return false;
        //    }
        //    var amount = parseInt($(form).find('.totalCharge').val());
        //    var ccnum, ccexp, cczip;
        //    if (amount) {
        //        ccnum = $(form).find('.cardnum').val();
        //        ccexp = $(form).find('.exp').val();
        //        cczip = $(form).find('.zip').val();
        //    } else {
        //        ccnum = 0;
        //        ccexp = 0;
        //        cczip = 0;
        //    }
        //    
        //    var fields = ['fname','lname','number','email','mm','dd','yy','accName','accAddress','accPhone','accCrossSt'];
        //    
        //    var tables = $(form).children('table');
        //    var chaperones = [];
        //    var error = false;
        //    tables.each( function(i, v) {
        //        var user = {};
        //        for (var f in fields) {
        //            var elem = '.' + fields[f];
        //            var val = $(this).find(elem).val().trim();
        //            if (val == 0 || val == '') {
        //                alert('All Fields are Mandatory.');
        //                error = true;
        //                return false;
        //            }
        //            var prop = fields[f];
        //            user[prop] = val;
        //        }
        //        //user.name = $(this).find('.name').val().trim();
        //        //user.email = $(this).find('.email').val().trim();
        //        //user.number = $(this).find('.number').val().trim();
        //        if ($(this).find('.vehicle:checked')) {
        //            user.vehicle = parseInt($(this).find('.vehicle:checked').val());
        //        } else {
        //            alert('You must indicate for each chaperone whether they are using a vehicle or not.');
        //            error = true;
        //            return false;
        //        }
        //        
        //        if ($(this).find('.full:checked')) {
        //            user.full = parseInt($(this).find('.full:checked').val());
        //        } else {
        //            alert('You have not indicated for every chaperone whether they are joining the full program or not.');
        //            error = true;
        //            return false;
        //        }
        //        if (user.full == 1) {
        //            user.s_size = $(this).find(".s_size_yes").val();
        //        } else {
        //            if ($(this).find('.sweater').is(":checked")) {
        //                user.s_size = $(this).find(".s_size_no").val();
        //            } else {
        //                user.s_size = 0;
        //            }
        //        }
        //        chaperones.push(user);
        //        console.log(user);
        //    });
        //    /*
        //    if (amount && !$(form).find('.agree:checked').length) {
        //        alert('You must indicate your acceptance of the charge.');
        //        return false;
        //    }
        //    */
        //    // create chaperones
        //    if (!error) {
        //        $.post('ajax/addChaperone.php', {
        //            year : year,
        //            school : school,
        //            info : chaperones,
        //            amount : amount,
        //            ccnum : ccnum,
        //            ccexp : ccexp,
        //            cczip : cczip
        //        }, function( error ) {
        //            if (parseInt(error) != 0) alert(error);
        //            else {
        //                alert("Chaperone(s) added.");
        //                location.reload(true);
        //            }
        //        });
        //    }
        //
        //    return false;
        //});
    </script>
</html>