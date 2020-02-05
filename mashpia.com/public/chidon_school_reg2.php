<?php
// ini_set('display_errors', 1);
// redirect to https
if ((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') || $_SERVER['SERVER_PORT'] != 443) {
    header("Location: https://" . $_SERVER['SERVER_NAME'] . "/chidon_school_reg2.php");
}
//********************* AUTHENTICATION *********************//
$admin_auth = array('school'); 
require('header.php');

//********************* LOAD THE LIST OF SCHOOLS *********************//
require_once 'class.adminSchools.php';       
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true );
$schools = $as->getSchools();

// and get the chidon year....
require_once 'class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

// check if school is registered for chidon already if not super user
$errorMsg = '';
if ($admin_user['auth'] != 'super' && count( $schools ) == 1) {
    $chidon_school = "select * from th_chidon_chaps where chap_type = 1 and year = " . $year . " and school_id = " . key( $schools );
    $chidon_school_res = mysql_query( $chidon_school );
    if ( mysql_num_rows( $chidon_school_res ) == 0 ) {
        $errorMsg .= "You have not yet enrolled your school for the Shabbaton. You need to go back to the Shabatton Enrollment page.";
    }
}
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

        <?php
        if ( !empty( $errorMsg ) ) {
            if ( count( $schools ) == 1 ) {
                echo "<div style='color: red;'>";
                echo $errorMsg;
                echo "</div>";
                echo "<br /><a class='button' id='prev_page' href='/chidon_school_reg.php'><i class='fa fa-arrow-left'></i> Enroll School to Shabbaton</a>";
                exit;
            }
        }
        ?>
        
        <p class="warning">
            <i>Please note: You must complete registration for your school's chaperones before your students can enroll in Shabbaton.</i>
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
                    <span id="heading">Create Chaperone</span> 
                    <span class="close" id="update_cc_exit">×</span>
                </h1>
                <? if ($admin_user['auth'] == "super") { ?>
                    <p class="warning create_chidon_info">
                        <i>Disclaimer: The chaperone will be assigned to the school currently selected on the master dropdown. Not always the one who's list you are looking at. Please refresh the report to be 100% sure you are on the correct school.</i>
                    </p>
                <? } ?>
                <form>
                    <input type="hidden" id="action" value="create" />
                    <input type="hidden" id="chap_id" value="" />
                    <input type="hidden" id="school_id_val" value="0" />
                    <input type="hidden" name="chap_type" id="chap_type" value="1" />
                    <!-- <h3>Chaperone Type</h3>
                    <div class="input_group input_half">
                        <label>
                            <input type="radio" class="chap_type chap_type_1" name="chap_type" value="1" checked="checked" /> Chaperone<br />
                            <input type="radio" class="chap_type chap_type_2" name="chap_type" value="2" disabled /> Walking Counsellor
                        </label>
                    </div> -->

                    <h3>Chaperone Info</h3>
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
                    <!-- <div class="input_group input_half edit_chidon_info">
                        <label>
                            Sweater Size<br/>
                            <select name="s_size" class="s_size">
                                <option value="">Not Getting A Sweater</option>
                                <option value="s">Small</option>
                                <option value="m">Medium</option>
                                <option value="l">Large</option>
                                <option value="xl">XLarge</option>
                                <option value="xxl">XXLarge</option>
                            </select>
                        </label>
                    </div> -->
                    
                    <div class="input_group input_half">
                        <label>
                            Chidon Type<br/>
                            <select name="chidon_type" class="chidon_type">
                                <option value="boys">Boys</option>
                                <option value="girls">Girls</option>
                            </select>
                        </label>
                    </div>
                    
                    <h3>Accommodation Info</h3>
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
                    <!-- <div class="input_group input_half">
                        <label>
                            Cross Streets<br/>
                            <select name="accCrossSt" id="accCrossSt">
                            <?php
                            // array of cross streets
                            $cross1 = ['Nostrand', 'New York', 'Brooklyn', 'Kingston', 'Albany', 'Troy', 'Schenectady', 'Utica'];
                            $cross2 = ['S Johns Place', 'Lincoln place', 'Eastern Parkway', 'Union Street', 'President Street', 'Carrol Street', 'Crown Street', 
                                'Montgomery Street', 'Empire Blvd', 'Lefferts Avenue', 'East New York Avenue', 'Maple Street', 'Rutland'];
                            for ( $i = 0; $i < count( $cross1 ); $i++ ) {
                                for ( $j = 0; $j < count( $cross2 ); $j++ ) {
                                    $street = $cross1[$i] . ' & ' . $cross2[$j];
                                    echo "<option value='" . $street . "'>" . $street . "</option>";
                                }
                            }
                            ?>
                            </select>
                            <!-- <input type="text" id="accCrossSt" name="accCrossSt" required /> -->
                    <!--    </label>
                    </div> -->
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
                    
                    <!-- <div class="create_chidon_info"> -->
                        <h3>Sweater</h3>
                        <div class="input_group input_full" style="color: red;">
                            <i>Chaperones will be required to wear their Chidon sweaters throughout the Shabbaton.</i>
                        </div>
                        <div class="input_group input_full">
                            <label>
                                <input type="hidden" name="sweater" class="sweater" value="1" />
                                <div class="s-size">
                                    Sweater size:
                                    <select name="s_size" class="s_size">
                                        <option value="s">Small</option>
                                        <option value="m">Medium</option>
                                        <option value="l">Large</option>
                                        <option value="xl">XLarge</option>
                                        <option value="xxl">XXLarge</option>
                                        <option value="xxxl">XXXLarge</option>
                                    </select>
                                </div>
                            </label>
                        </div>
                        <!-- <div class="input_group input_full">
                            <strong>Total Due: $<span class="total">0</span></strong>
                        </div> -->
                        <!-- <h3>Credit Card Info</h3>
                        <div class="showAgree">
                            <div class="input_group input_full">
                                <strong>In addition to any sweater charges, I understand that there is $100 fee per day in the event that my chaperone / walking counselor is 
                                not by the program on time or does not follow their responsibilities. </strong> 
                            </div>
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
                        </div> -->
                    <!-- </div> -->
                    
                    <div class="input_group input_full" style="line-height: 1.4">
                        <h3>Terms</h3>
                        <input type="checkbox" name="terms" id="terms" />
                        I have read and provided my chaperone with <a href="https://www.google.com/url?q=https://docs.google.com/document/d/1MLoZrLdBqylp4wzgwzNvFRu1o0bQH_KWIYuHtt-729c/edit&sa=D&ust=1580840674748000&usg=AFQjCNGfCmwjnkSkvtwMf5wGy7qmE8RyBA">this document</a> and understand and accept the responsibilities of the chaperone. <br />
                        <input type="checkbox" name="terms" id="terms2" />
                        I understand that the chaperone will be responsible to take a group of chayolim home on Thursday night, Friday afternoon, and Motzei Shabbos after the program. 
                        If the chaperone will be unable to do this, you will need to provide another dedicated walking supervisor to do so instead. <br />
                        <span id="walking_super_text">
                            <input type="checkbox" name="walking_supervisor" id="walking_supervisor" /> 
                            I will provide a dedicated walking supervisor at an additional fee of $20.
                        </span>
                    </div>

                    <div id="walking_super_form" style="display: none">
                        <input type="hidden" name="supervisor_chap_type" id="supervisor_chap_type" value="2" />

                        <h3>Walking Supervisor Info</h3>
                        <div class="input_group input_half">
                            <label>
                                First Name<br/>
                                <input type="text" id="supervisor_first_name" name="supervisor_first_name" />
                            </label>
                        </div>
                        <div class="input_group input_half">
                            <label>
                                Last Name<br/>
                                <input type="text" id="supervisor_last_name" name="supervisor_last_name" />
                            </label>
                        </div>
                        <div class="input_group input_half">
                            <label>
                                Cell Number<br/>
                                <input type="text" id="supervisor_number" name="supervisor_number" />
                            </label>
                        </div>
                        <div class="input_group input_half">
                            <label>
                                E-Mail<br/>
                                <input type="text" id="supervisor_email" name="supervisor_email" />
                            </label>
                        </div>
                        <div class="input_group input_half ">
                            <label>
                                DOB<br/>
                                <input type="date" id="supervisor_dob" name="supervisor_dob" min="1950-01-01" max="2009-12-31" />
                            </label>
                        </div>
                        
                        <div class="input_group input_half">
                            <label>
                                Chidon Type<br/>
                                <select name="supervisor_chidon_type" class="supervisor_chidon_type">
                                    <option value="boys">Boys</option>
                                    <option value="girls">Girls</option>
                                </select>
                            </label>
                        </div>

                        <h3>Sweater</h3>
                        <div class="input_group input_full" style="color: red;">
                            <i>Walking supervisors can be provided with a Shabbaton sweatshirt for an additional fee of $20.</i>
                        </div>
                        <div class="input_group input_full">
                            <label>
                                <div class="s-size">
                                    Sweater size:
                                    <select name="supervisor_s_size" class="supervisor_s_size">
                                        <option value="">Not Getting A Sweater</option>
                                        <option value="s">Small</option>
                                        <option value="m">Medium</option>
                                        <option value="l">Large</option>
                                        <option value="xl">XLarge</option>
                                        <option value="xxl">XXLarge</option>
                                        <option value="xxxl">XXXLarge</option>
                                    </select>
                                </div>
                            </label>
                        </div>
                        
                        <h3>Accommodation Info</h3>
                        <div class="input_group input_half">
                            <label>
                                Name<br/>
                                <input type="text" id="supervisor_accName" name="supervisor_accName" />
                            </label>
                        </div>
                        <div class="input_group input_half">
                            <label>
                                Address<br/>
                                <input type="text" id="supervisor_accAddress" name="supervisor_accAddress" />
                            </label>
                        </div>
                        <!-- <div class="input_group input_half">
                            <label>
                                Cross Streets<br/>
                                <select name="accCrossSt" id="accCrossSt">
                                <?php
                                // array of cross streets
                                $cross1 = ['Nostrand', 'New York', 'Brooklyn', 'Kingston', 'Albany', 'Troy', 'Schenectady', 'Utica'];
                                $cross2 = ['S Johns Place', 'Lincoln place', 'Eastern Parkway', 'Union Street', 'President Street', 'Carrol Street', 'Crown Street', 
                                    'Montgomery Street', 'Empire Blvd', 'Lefferts Avenue', 'East New York Avenue', 'Maple Street', 'Rutland'];
                                for ( $i = 0; $i < count( $cross1 ); $i++ ) {
                                    for ( $j = 0; $j < count( $cross2 ); $j++ ) {
                                        $street = $cross1[$i] . ' & ' . $cross2[$j];
                                        echo "<option value='" . $street . "'>" . $street . "</option>";
                                    }
                                }
                                ?>
                                </select>
                                <!-- <input type="text" id="accCrossSt" name="accCrossSt" required /> -->
                        <!--    </label>
                        </div> -->
                        <div class="input_group input_half">
                            <label>
                                Phone Number<br/>
                                <input type="text" id="supervisor_accPhone" name="supervisor_accPhone" />
                            </label>
                        </div>
                        <div class="input_group input_half">
                            <label>
                                Vehicle<br/>
                                <input type="radio" name="supervisor_vehicle" class="supervisor_vehicle vehicle_0" value="0" /> NO
                                <input type="radio" name="supervisor_vehicle" class="supervisor_vehicle vehicle_1" value="1" /> YES
                            </label>
                        </div>
                        <h3></h3>
                        <div class="input_group input_full">
                            Your credit card on file will be charged: $<span id="total_charge_span">0</span>
                        </div>
                        <input type="hidden" name="total_charge" id="total_charge" value="0" />
                    </div>

                    
                    <!-- <div class="input_group input_full" style="text-align: center">
                        <a class="button" id="chap_prev" style="float: left; display: none;">
                            <i class="fa fa-arrow-left" aria-hidden="true"></i> Previous Chaperone
                        </a>
                        <a class="button" id="chap_add">
                            <i class="fa fa-plus" aria-hidden="true"></i> Add Another Chaperone
                        </a>
                        <a class="button" id="chap_next" style="float: right; display: none;">
                            Next Chaperone <i class="fa fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div> -->
                    <div style="clear: both"></div>
                    <div class="input_group input_full" style="text-align: center">
                        <input type="submit" name="submit" class="submit" value="Add Chaperone" style="padding: 10px;" />
                    </div>
                </form>
            </div>
        </div>
        
        <a class='button' id='prev_page' href='/chidon_school_reg.php'><i class='fa fa-arrow-left'></i> Enroll School to Shabbaton</a>
        <a class='button' id="next_page" href='/enrollment.php'>Finalize School Registration <i class="fa fa-arrow-right"></i></a>
        <script src="/js/admin/components/modal.js"></script>
        <script src="/scripts/chidon/chidon_school_reg.php.js"></script>
    </body>
</html>