<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
// redirect to https
if ((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') || $_SERVER['SERVER_PORT'] != 443) {
    header("Location: https://" . $_SERVER['SERVER_NAME'] . "/chidonOld/schoolReg/");
}
//********************* AUTHENTICATION *********************//
$admin_auth = array('school');
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

//********************* LOAD THE LIST OF SCHOOLS *********************//
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true );
$schools = $as->getSchools();

// and get the chidon year....
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>School Shabbaton Enrollment | Tzivos Hashem</title>
    <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
    <style type='text/css'>
        .options{text-align: center;}
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
        h5 {
            font-size: smaller;
        }
    </style>
</head>

<body>
<? include $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
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
<? } ?>

<div id="school_shabbaton">
    <h2>School Responsibilities</h2>
    Dear Chidon Coordinator,
    <br /><br/>
    In order to participate in Shabbaton, please read carefully.
    <br /><br/>
    <ul>
        <li>Congratulate each of your Chayolim for their incredible success.
        <li>To motivate them to do their best on the Chidon Final and for your Representatives to shine on stage.
        <li>Determine and finalize your school’s Representatives.
        <li>Plan and execute an incredible launch for the Chidon Registration and Chidon Drive and encourage every Chayol to sign up for their respective rewards.
        <li>Choose which option your school will be rewarding your Chayolim with.
    </ul>
    <br />

    <h4>Review Eligibility Report</h4>
    Click <a href="https://mashpia.com/chidonTests/eligibility.php">here</a> for the eligibility report. If you feel like any
    child's eligibility status should change, you must contact headquarters before you announce to the children what they received.
    After you announce to the children what they received no changes can be made.
    <br /><br />

    <h4>Distribute Letters to Eligible Chayolim</h4>
    <br/>

    <div style="margin-left: 50px;">
        <h5>Mitzvah Maven</h5>
        Click <a href="https://docs.google.com/document/d/137vSz1wutOzSLJmatnd317TiD3WJPdBgvuPkUNvPTvw/edit?usp=sharing">here</a>
        for the Mitzvah Maven Letter to be given to all children who went for the Shabbaton, but only passed Mitzvah Maven.
        <br /><br />

        <h5>Maven Pro</h5>
        The Maven/Pro Letter for all children (
        <a href="https://docs.google.com/document/d/1QuFdoOsyRUx2zjHCDF2ams2s--LMdNQgsqlI08dEM9s/edit?usp=sharing">boys</a>,
        <a href="https://docs.google.com/document/d/1UtXImBW-onFjek0zCMjnFaEoDfHEOQzlYSdUPlJcbsU/edit?usp=sharing">girls</a>
        ) who went for the Pro/Expert test but only passed Maven/pro tests (but were not moved onto the Mavin/pro track).
        <br /><br />

        <h5>Pro Expert</h5>
        Click <a href="https://docs.google.com/document/d/1J0PdNSL2CYHo96qFPF5LuoWKM4FIfwwdnuWMfS-N0Ps/edit">here</a>
        for the Pro/Expert Letter for all children who passed Pro/Expert or children who were put on the Mavin Pro and passed on their track.
        <br /><br />

        <h5>Trophy contestant</h5>
        Click <a href="https://docs.google.com/document/d/1ueSXopH6tA-Zbcg9l9zHmFYQa3JceBsxFyxNexttxxA/edit">here</a>
        for the Trophy Contestant Letter for all children who are eligible to compete for the Chidon Trophy.
        <br /><br />
    </div>

    <h4>Encourage your students to learn</h4>
    I will do everything in my power to encourage the Chayolim to prepare for the final.
    <ul>
        <li>Make sure every child has a review calendar study schedule.
        <li>Motivate the children to keep to the study schedule.
        <li>Constantly remind children how many days left to the final.
        <li>Encourage them to do their best. Remind the chayolim that:
        <ul>
            <li>60% on their final will get them a Certificate
            <li>70% on their final will get them a Plaque.
            <li>80% on their final will get them a Stage Plaque.
            <li>90% on their final will get them a Medal.
        </ul>
    </ul>
    <br />

    <h4>Trophy contestants</h4>
    <ul>
        <li>Make a meeting with your Trophy Contestant.
        <li>Congratulate each Chayol for becoming a Trophy Contestant. This shows that they are from the highest achievers in the whole world.
        <li>Remind the Chayolim that there are only three trophies: one gold, one silver and one bronze for each grade.
        <li>Encourage them to do their best and earn a trophy for your school.
    </ul>
    <br />

    <h4>Kol Hatorah Kula kids</h4>
    <ul>
        <li>Click here for the Kol Hatorah Kula letter to give to the KHK kids.
        <li>Make a special meeting with your Kol Hatorah Kula Chayolim
        <li>Tell them that they have achieved the Chidon dream and have learned all 613 Mitzvos
        <li>Remind them that if they get 70% on the Kol Hatorah Kula Final, they are going to receive the special
            Kol Hatorah Kula Plaque (even if they are not going to be a Representative).
        <li>Remind the Representatives to watch the Kol Hatorah kula rapid response from last year now and prepare to
            be able to answer their question. It could make or break the entire game show. No pressure.
    </ul>
    <br />

    <h4>Confirm Representatives</h4>
    Fill in your information into <a href="https://docs.google.com/spreadsheets/d/1YeTe-kMQZOQZXEPwpNlnXLedY47Z8xEilFfESJkrG9I/edit#gid=1674901266">this</a>
    spreadsheet to set up a time that you will be confirming your Representatives with HQ.
    Before you come on the call you should know who your Representatives are.
    <br /><br />

    <h4>How do I know to know who our School Representatives are?</h4>
    Each year, 400 Chayolim (200 boys & 200 girls) get to represent their schools on stage at the Grand Chidon Event.
    <br /><br />
    Each school may be eligible for one school Representative in each grade.
    <br /><br />
    To qualify as your school grade Representative, you must score at least an 85% average between all 4 Pro/Expert/Trophy tests answering at least (119 out of 140 questions correctly.)
    <br /><br />
    In addition you must have the highest score in your grade within your school.
    <br /><br />
    We will combine your scores from Pro/Expert/Trophy of all four tests. If you score the highest in your grade, in your school, you will be eligible to be your School’s Grade Representative on stage.
    <br /><br />
    In the case of two Chayolim in a grade that ties, we will then take a look at the tie breaker question (on the 4th test) to determine your school’s grade representative. In the case that even the tie breaker questions are a tie, a raffle will be made to determine your School’s Grade Representative.
    <br /><br />
    In the case that there are extra spaces on stage, schools that have over 30 Chayolim who qualify as Chidon Contestants will get an extra spot on stage.
    <br /><br />
    If after those spots have been given there are still spots remaining, then the Chayolim in that grade with the highest averages in the world will be given a spot on stage.
    <br /><br />
    Disclaimer: In the case that there are not enough spots on stage, they will go to the School Grade Representatives that have the highest marks in the world.
    <br /><br />

    <h2>Options</h2>
    <form>
        <h4>Option A</h4>
        $100 School trip<br />
        $100 Prizes<br />
        <input type="radio" name="choice" class="choice" value="A" /> I would like to go with Option A. I understand that my school must confirm their trip budget in order to receive the trip money
        <br/><br/>

        <h4>Option B</h4>
        $50 VR rental budget<br />
        $50 School Trip Budget<br />
        $100 Prizes<br />
        <input type="radio" name="choice" class="choice" value="B" /> I would like to go with Option B. I understand that my school must confirm their trip budget in order to receive the trip money
        <br /><br />
        <h5>Rent Kosher VR goggles</h5>
        I would like to rent <input type="number" id="rent" class="vr" size="2" style="width: 30px;" /> Goggles for our school. There will be a $365 hold on your credit card per Goggles until we receive them back at HQ
        <br /><br />

        <h5>Buying Kosher VR goggles<h4></h4>
        Our School would like to purchase the VR Goggles to keep:
        <br />
        1 - 9 Googles $500 each<br />
        10 - 19 Goggles $400 each<br />
        20 and up $365 each.<br />
        We would like to buy <input type="number" id="buy" class="vr" size="2" style="width: 30px;"> Goggles
        <br /><br/>

        <h4>Option C</h4>
        $200 Prizes<br />
        <input type="radio" name="choice" class="choice" value="C" /> I would like to go with Option C.
        <br/><br />

        <h4>Total Hold: $<span id="total_hold">0</span></h4>
        <h4>Total Charge: $<span id="total_charge">0</span></h4>

        <!--
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
        -->
        <h2>Terms</h2>
        <input type="checkbox" name="agreement" id="agreement" /> I accept the school responsibilities as well as any fees that we may incur.<br />
        <input type="checkbox" name="agreement2" id="agreement2" /> I understand that enrollment will not open for my students until I have completed the registration process.
        <br />

        <h2></h2>
        <input type="submit" id="submit" value="Submit" />
    </form>

</div>
</body>
<script>
    function calculateTotal() {
        let total_hold = 0;
        let total_charge = 0;

        const rent = parseInt($("#rent").val())
        if (rent > 0) {
            total_hold += 365 * rent
        }

        const buy = parseInt($("#buy").val())
        if (buy > 0) {
            if (buy < 10) {
                total_charge += 500 * buy
            } else if (buy < 20) {
                total_charge += 400 * buy
            } else {
                total_charge += 365 * buy
            }
        }

        $("#total_hold").text(total_hold)
        $("#total_charge").text(total_charge)

        return [rent, buy, total_hold, total_charge]
    }

    $( function() {
        // getCCInfo();

        // $("#school_id").change( function() {
        //     getCCInfo();
        // });

        $(".vr").change(calculateTotal)

        $("#submit").click( function( evt ) {
            evt.preventDefault();

            // find out choice
            let choice = 0
            $(".choice").each( function() {
                if ($(this).is(":checked")) {
                    choice = $(this).val()
                }
            })
            if (choice == 0) {
                alert("You must choose an option!")
                return false;
            }

            if ( !$("#agreement").is(":checked") || !$("#agreement2").is(":checked") ) {
                alert("You must agree to all terms!");
                return false;
            }

            let [rent, buy, total_hold, total_charge] = calculateTotal()
            let info = []
            info['rent'] = rent
            info['buy'] = buy
            info['hold'] = total_hold
            info['charge'] = total_charge
            info['choice'] = choice

            // let cc = {};
            // if ( $(".cc_info:checked").val() == 1 ) {
            //     // cc info must be filled out
            //     let cardnum = $("#cardnumber").val().trim();
            //     let exp = $("#exp").val().trim();
            //     let cvc = $(".cvc").val().trim();
            //
            //     if ( cardnum == '' || exp == '' || cvc == '' ) {
            //         alert("All Card Info must be entered.");
            //         return false;
            //     }
            //
            //     if ( cardnum.length < 15 || cardnum.length > 16 ) {
            //         alert( cardnum.length );
            //         alert("Cardnumber must be 15 or 16 digits.");
            //         return false;
            //     }
            //
            //     if ( exp.indexOf('/') == -1 || exp.length != 5 ) {
            //         alert("Invalid Expiry format. No spaces allowed.");
            //         return false;
            //     }
            //
            //     cc.card = cardnum;
            //     cc.exp = exp;
            //     cc.cvc = cvc;
            // }

            let school_id = $("#school_id").val()
            if ( school_id > 0 ) {
                $.post('/ajax/chidon/registerSchool2.php', { school_id: school_id, ...info }, function( error ) {
                    if ( error ) alert( error );
                    else {
                        alert("You have successfully enrolled your school to the Shabbaton.");
                    }
                });
            }
        });

        function getCCInfo() {
            let school_id = $("#school_id").val();
            if ( school_id ) {
                $.post('/ajax/chidon/getSchoolInfo.php', { school : school_id }, function( school_info ) {
                    let school = JSON.parse( school_info );
                    if ( !(school.authorize_customer_profile_id && school.authorize_payment_profile_id) ) {
                        alert("As you don't have any credit card on file, you will need to provide us with a credit card.");
                        $("#ccOnFile").hide();
                        $(".cc_info").eq(1).attr('checked', true);
                    } else {
                        $(".cc_info").eq(0).attr('checked', true);
                        $("#ccOnFile").show();
                    }
                    // set
                });
            } else {
                $("#ccOnFile").hide();
                $(".cc_info").eq(1).attr('checked', true);
            }
        }
    });
</script>
</html>