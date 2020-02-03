<?php
// ini_set('display_errors', 1);
// redirect to https
if ((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') || $_SERVER['SERVER_PORT'] != 443) {
    header("Location: https://" . $_SERVER['SERVER_NAME'] . "/chidon_school_reg.php");
}
//********************* AUTHENTICATION *********************//
$admin_auth = array('school'); 
require('header.php');

//********************* LOAD THE LIST OF SCHOOLS *********************//
require_once 'class.adminSchools.php';       
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true );
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
            In order to participate in Shabbaton, you will need to fulfill the following requirements:
            <ul>
              <li>Confirm the status of all Chidon participants in your school, and remove any students who will not be attending the Shabbaton from the system. 
              You currently have <span id="numStudents">0</span> students enrolled. Please <a href="review_enrollment.php" target="_blank">review them here</a> to confirm that only students who qualify AND plan to attend Shabbaton are in the system.</li>
              <li>Attend the Chidon Shabbaton Conference call on Monday, March 16 / 20 Adar (for girls), or Monday, March 23 / 27 Adar (for the boys).</li>
              <li>Provide one chaperone for every 50 students who will be able to fulfill the chaperone duties as detailed <a href="https://docs.google.com/document/d/1MLoZrLdBqylp4wzgwzNvFRu1o0bQH_KWIYuHtt-729c/edit" target="_blank">here</a>.</li>
              <ul>
                <li>In the event that a chaperone will not be available to walk children home, you may instead provide a dedicated walking supervisor who will be able to fulfill those duties, as detailed in the document linked above.</li>
                <li>In the event that a chaperone is not at the program on time or does not complete their responsibilities, a $200 penalty PER DAY PER CHAPERONE will be applied. 
                The fee will be charged to the credit card on file, or the one provided in the form below.</li>
              </ul>
            </ul>
            
            <form>
              <h2>Bus Home From Event</h2>
              <div class="input_group input_full">
                Please confirm where your students will go on Sunday after the event in Newark:<br />
                <input type="radio" name="bus" class="bus" value="1" /> On a Chidon Shabbaton bus direct to the airport. 
                  <select name="airport" id="airport" style="width: 110px;"><option>Please Select</option><option>Newark</option><option>LGA</option><option>JFK</option></select><br />
                <input type="radio" name="bus" class="bus" value="2" /> On a Chidon Shabbaton bus back to Crown Heights drop off location (President and Kingston).<br />
                <input type="radio" name="bus" class="bus" value="0" /> My school will provide our own buses to drive them home (out of town).
              </div>

              <h2>Food Trip Home</h2>
              <div class="input_group input_full">
                <input type="checkbox" name="food" id="food" /> Yes! I would like to receive food and snacks for the trip back home.
              </div>

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
                      <input type="text" id="exp" name="exp" class="exp" placeholder="MM/YY" />
                  </label>
              </div>
              <div class="input_group input_half">
                  <label>
                      Security Code<br/>
                      <input type="text" id="cvc" name="cvc" class="cvc" placeholder="XXX" />
                  </label>
              </div>
              <h2>Terms</h2>
              <input type="checkbox" name="agreement" id="agreement" /> I accept the above mentioned responsibilities as well as any fees that we may incur.<br />
              <input type="checkbox" name="agreement2" id="agreement2" /> I understand that enrollment will not open for my students until I have completed the registration process, including registering a chaperone.
            </form>

        </div>    
        
        <br />
        <a class='button' id="next_page" href='/chidon_school_reg2.php'>Save & Continue to Chaperone(s)<i class="fa fa-arrow-right"></i></a>
    </body>
    <script>
      $( function() {
        getCCInfo();
        getNumChidonChildren();

        $("#school_id").change( function() {
          getCCInfo();
          getNumChidonChildren();
        });

        $("#next_page").click( function( evt ) {
          evt.preventDefault();

          if ( !$("#agreement:checked").length || !$("#agreement2:checked").length ) {
            alert("You have not agreed to terms!");
            return false;
          }

          if ( !$(".bus:checked").length ) {
            alert("You need to choose one of the three bus options.");
            return false;
          } else {
            var bus = $(".bus:checked").val();
            if ( parseInt( bus ) == 1 ) {
              // check airport
              var airport = $("#airport").val();
              if ( airport == 'Please Select' ) {
                alert("You must choose the airport.");
                return false;
              }
            } else {
              var airport = '';
            }
          }

          let cc = {};
          if ( $(".cc_info:checked").val() == 1 ) {
            // cc info must be filled out
            let cardnum = $("#cardnumber").val().trim();
            let exp = $("#exp").val().trim();
            let cvc = $(".cvc").val().trim();

            if ( cardnum == '' || exp == '' || cvc == '' ) {
              alert("All Card Info must be entered.");
              return false;
            }

            if ( cardnum.length < 15 || cardnum.length > 16 ) {
              alert( cardnum.length );
              alert("Cardnumber must be 15 or 16 digits.");
              return false;
            }

            if ( exp.indexOf('/') == -1 || exp.length != 5 ) {
              alert("Invalid Expiry format. No spaces allowed.");
              return false;
            }

            cc.card = cardnum;
            cc.exp = exp;
            cc.cvc = cvc;
          }

          let school_id = $("#school_id").val();
          let food = $("#food").is(":checked");
          if ( school_id ) {
            $.post('/ajax/chidon/registerSchool.php', { school_id: school_id, bus: bus, airport: airport, food: food, cc_info: cc }, function( error ) {
              if ( error ) alert( error );
              else {
                alert("You have successfully enrolled your school to the Shabbaton.");
                location.href = '/chidon_school_reg2.php'; // redirect
              }
            });
          }
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

        function getNumChidonChildren() {
          const year = <?= $year ?>;
          const school_id = $("#school_id").val();
          if ( school_id > 0 ) {
            $.post('ajax/chidon/getNumChildren.php', { school_id : school_id, year : year }, function( res ) {
              const result = JSON.parse( res );
              if ( result.success ) {
                $("#numStudents").text( result.num );
              } else {
                console.log( result.error );
              }
            });
          } else {
            $("#numStudents").text( '0' );
          }
        }
      });
    </script>
</html>