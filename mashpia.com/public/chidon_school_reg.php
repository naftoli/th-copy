<?php
ini_set('display_errors', 1);
// redirect to https
if ((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') || $_SERVER['SERVER_PORT'] != 443) {
    header("Location: https://" . $_SERVER['SERVER_NAME'] . "/chidon_school_reg.php");
}
//********************* AUTHENTICATION *********************//
$admin_auth = array('school'); 
require('header.php');

//********************* LOAD THE LIST OF SCHOOLS *********************//
require_once 'class.adminSchools.php';       
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
// if ($admin_user['auth'] == 'super') {
//   // forward to chaperone page
//   header("Location: chidon_school_reg2.php");
//   exit;
// }

// and get the chidon year....
require_once 'class.globalSettings.php';
$year = GlobalSettings::getChidonYear();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>School Shabbaton Enrollment | Tzivos Hashem</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<!--        Modal and other form elements -->
        <link href="/styles/admin/modal.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/forms.css" rel="stylesheet" type="text/css"/>
        <!--        Rotating Spinner, grey dropdowns and fancy checkboxes... -->
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
        <style type='text/css'>
            a.button{display: inline-block;}
            a#next_page{float: right;margin-bottom: 20px;}
            .options{text-align: center;}
            .warning{
                font-size: 16px; font-weight: bold; color: red;
            }
            #school_shabbaton {
              line-height: 1.5;
              font-size: 16px;
            }
            #school_shabbaton ul {
              margin-left: 20px;
            }
            #school_shabbaton li {
              margin-left: 20px;
              list-style: decimal !important;
            }
        </style>
    </head>

    <body>
        <? include('admin_header.php'); ?>
        <?php include($_SERVER['DOCUMENT_ROOT']."/chidon_passwords.php"); // require a password to use this page... ?>
        <h1>School Shabbaton Enrollment</h1>

        <? if(count($schools) == 1) { ?>
            <select id="school_id" name="school_id" class="hidden" disabled>
                <option value="<?=array_keys($schools)[0]?>"><?=array_values($schools)[0]?></option>
            </select>
        <? } else { ?>
            <div class="options">
                <div class="row">
                    <i class="fa fa-university" aria-hidden="true"></i> School: 
                    <select id="school_id" name="school_id">
                        <option value="0" selected>All Schools</option>
                        <? foreach($schools as $school_id => $school_name){?>
                            <option value="<?=$school_id?>"><?=$school_name?></option>
                        <?}?>
                    </select>
                </div>
            </div>
        <?}?>

        <div id="school_shabbaton">
            <h2>School Responsibilities</h2>
            In order for your school to be able to participate in the shabbaton, you need to be aware of the following:
            <ul>
              <li>You must have 1 Chaperone enrolled before parent enrollment opens.</li>
              <li>You must have 1 Walking Counselor enrolled for every 12 students that are "contestants".</li>
              <li>If some "contestants" are not coming, you need to "delete" them to lower the number of walking counselors needed.</li>
              <li>There is a $100 fee PER DAY PER CHAPERONE / WALKING COUNSELOR in the event that my chaperone / walking counselor is not by the program on time or does not follow their responsibilities. The fee will be charged to 
                the credit card on file, or the one you provide us in the form below.</li>
            </ul>
            
            <form>
              <?php if ($admin_user['auth'] != 'super') : ?>
              <h2>Bus Home</h2>
              <div class="input_group input_full">
                Please choose one of the following options:<br />
                <input type="radio" name="bus" class="bus" value="1" /> My bus is leaving from the Chidon Event Venue to Newark Airport after the event.<br />
                <input type="radio" name="bus" class="bus" value="2" /> My bus is leaving from the Chidon Event Venue to Crown Heights drop off location; President and Kingston after the event.<br />
                <input type="radio" name="bus" class="bus" value="0" /> My bus is leaving from the Chidon Event Venue to our school and we dont need a bus from the Chidon Event.
              </div>
              <?php else : ?>
              <input type="hidden" name="bus" class="bus" value="0" />
              <?php endif; ?>

              <h2>Credit Card Info</h2>
              <div id="ccOnFile">
                <input type="radio" name="cc_info" class="cc_info" value="0" checked /> Use Credit Card on file<br />
                <input type="radio" name="cc_info" class="cc_info" value="1" /> Use New Credit Card<br />
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
              <h2>Terms</h2>
              <input type="checkbox" name="agreement" id="agreement" /> I accept the above mentioned responsibilities as well as any fees that we may incur.
            </form>

        </div>    
        
        <br />
        <a class='button' id="next_page" href='/chidon_school_reg2.php'>Save & Continue to Chaperone(s)<i class="fa fa-arrow-right"></i></a>
    </body>
    <script>
      $( function() {
        getCCInfo();

        $("#school_id").change( getCCInfo );

        $("#next_page").click( function( evt ) {
          evt.preventDefault();

          if ( !$("#agreement:checked").length ) {
            alert("You have not indicated your agreement to the terms and fees.");
            return false;
          }

          if ( !$(".bus:checked").length ) {
            alert("You need to choose one of the three bus options.");
            return false;
          } else {
            var bus = $(".bus:checked").val();
          }

          if ( $(".cc_info:checked").val() == 1 ) {
            // cc info must be filled out
            let cardnum = $("#cardnumber").val().trim();
            let exp = $("#exp").val().trim();
            let zip = $(".zip").val().trim();

            if ( cardnum == '' || exp == '' || zip == '' ) {
              alert("All Card Info must be entered.");
              return false;
            }

            // create new profile id
          }

          let school_id = $("#school_id").val();
          if ( school_id ) {
            // figure out payment that is to be used

            $.post('/ajax/chidon/registerSchool.php', { school_id: school_id, bus: bus });
          }
          
          location.href = '/chidon_school_reg2.php';
        });

        function getCCInfo() {
          let school_id = $("#school_id").val();
          if ( school_id ) {
            $.post('ajax/chidon/getSchoolInfo.php', { school : school_id }, function( school_info ) {
              let school = JSON.parse( school_info );
              if ( !(school.authorize_customer_profile_id && school.authorize_payment_profile_id) ) {
                alert("As you don't have any credit card on file, you will need to provide us with a credit card.");
                $("#ccOnFile").hide();
                $(".cc_info").eq(1).attr('checked', true);
              } else {
                $(".cc_info").eq(0).attr('checked', true);
                $("#ccOnFile").show();
              }
            });
          } else {
            $("#ccOnFile").hide();
            $(".cc_info").eq(1).attr('checked', true);
          }
        }
      });
    </script>
</html>