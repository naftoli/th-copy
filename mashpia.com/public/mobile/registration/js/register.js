/**
 * JS file for /mobile/registration/
 */
// page setup
if ( !checkDateInput() ) { $('#dob, input[type="date"]').datepicker({ format: "yyyy-mm-dd" }); }
$("#successModal").on('hidden.bs.modal', function( event ) { window.location = "/mobile/reg/parent_detail.html" } );
$("#errorModal").on('hidden.bs.modal', function () { $("#errorBody").empty() });
$("#error2Modal").on('hidden.bs.modal', function () { $("#error2Body").empty() })
$('[data-toggle="popover"]').popover();
hebrew_keyboard.attach( "#first_he, #last_he" ); // use hebrew in the right places

// when clicking an element with a data-dismiss attribute
// dismiss the element referenced in the data-target attribute
// don't know why just these modals are not working without this
$("[data-dismiss]").click( function( event ){
    event.target.dataset.target && $(event.target.dataset.target).modal('hide')
})

var myshliach = 61;
var anash_kinder = 269;
var showClasses = 0; // global var to determine if we need to show link to myshliach online classes
var selected_index;
var current_user;
var school_id;
var user_prizes = {}
var khk_fee = 18
var usa = [
    'U.S.A',
    'United States',
    'US',
    'USA'
]

var state = {
    users: [], // the users we are registering
    selected_users: [], // users selected in step-1
    cart: [], // items that the user is paying for
    shipping_type: 1 // 1 or 2
}

var registrationApp = function() {
    var api_url = '/api/registration/user_registration.php'; // API endpoint for this page

    var Msg1 = "Tzivos Hashem Enrollment for ";
        
    var Msg3 = "CKids Enrollment for ";
    var Msg4 = "Chidon Enrollment for ";
    var Msg5 = " (includes coordinator and study guide)";//
    var Msg6 = "Yahadus Book for ";
    var Msg7 = " (Shipping Included)";
    var Msg8 = "To Enroll for MyShliach's online weekly classes please click ";
    var Msg9 = "here";
    //var Msg10 = "";
    //var Msg11 = "";

    var Err1 = "You must select at least one type of registration.";
    var Err2 = "You must choose which book is being studied.";
    var Err3 = "You must indicate if you have already purchased a book or not.";
    var Err4 = "You have not selected where you bought the book.";
    var Err5 = "You must enter the store information for your book purchase.";
    var Err6 = "You must indicate whether you would like to purchase a book or not.";
    var Err7 = "You must indicate how you will be learning for chidon.";
    var Err8 = "You must indicate your acknowledgment of the Shabbaton fee.";
    var Err9 = "You must enter the serial number of the student who recruited you.";
    var Err10 = "You must enter the name of the school that you are attending.";
    var Err11 = "You must indicate your acceptance of participation in Tzivos Hashem Media.";
    var Err12 = "Could not update Profile Picture. We will try again when pressing 'Confirm'.";
    var Err13 = "You must choose how you prefer your name to be displayed!";
    var Err14 = "You must choose a yarmulka size!";
    var Err15 = "You must enter the hebrew plaque name as well as english and hebrew name for custom items.";
    var Err16 = "Could not confirm your registration. Ensure that the required fields are filled in or contact support";
    var Err17 = "You need to enter your English Name that you are known by";
    var Err18 = "You need to enter your Hebrew Name that you are known by";;
    //var Err19 = "";
    //var Err20 = "";
    var picError = "You must upload a picture of your child!"

    if (localStorage.getItem("locallang") == "he") {
         Msg1 = "ההרשמה לצבאות ה' עבור ";
        
         Msg3 = "ההרשמה לסי קידס עבור ";
         Msg4 = "הרשמה לחידון עבור ";
         Msg5 = "(כולל מתאם ומדריך לימודי";// 
         Msg6 = "ספר יהדות עבור ";
         Msg7 = "(דמי המשלוח כלולים)";
         Msg8 = "להרשם לשיעורים השבועיים של מיי שליח נא להקליק  ";
         Msg9 = "כאן";
        // Msg10 = "";
        // Msg11 = "";

         Err1 = "חובה לבחור לפחות הרשמה אחת .";
         Err2 = "חובה לבחור באיזה ספר למדתם.";
         Err3 = "חובה לציין האם רכשתם את הספר או לא.";
         Err4 = "לא בחרת היכן הספר נקנה";
         Err5 = "חובה להקליד את הכתובת של החנות בה נקנה הספר";
         Err6 = "חובה לציין האם אתם רוצים לקנות את הספר או לא .";
         Err7 = "חובה לציין כיצד אתם מתכוונים ללמוד לחידון.";
         Err8 = "חובה לציין שאתם מבינים את עלות השבתון";
         Err9 = "חובה לבחור מי צירף אתכם";
         Err10 = "חובה להכניס את שם בית הספר או תלמוד תורה בו אתם לומדים";
         Err11 = "חובה לציין את הסכמתכם להשתתפות במדיה של צבאות ה";
         Err12 = "לא ניתן לעדען את תמונת הפרופיל . נא לנסות שוב!";
         Err13 = "";
         Err14 = "";
         Err15 = "";
         Err16 = "לא ניתן היה לאשר את הרישום שלך. ודא כי השדות הנדרשים מלאים או צור קשר עם התמיכה";
         Err17 = "";
         Err18 = "";
         picError = ""
    }

    // navigation buttons
    $(".start-step-1").click( step1 );
    $(".start-step-2").click( step2 );
    $(".start-step-3").click( step3 );

    // form handlers
    $("#step-2 form").submit( confirmUser );
    $("#step-3 form").submit( confirmShipping );
    $("#step-4 form").submit( registerUsersHandler );
    $("#step-4 #cc-number").keyup( validateCardInput );
    image_upload( {}, onImageUploaded );
    // run the first step
    step1();

    /*********************** CODE TO GUIDE USER THROUGH STEPS ***********************/
    function noChildren(){
        showSection('no-children')
    }
    
    // select users
    function step1() {
        window.location.hash = 'step-1';
        showSection( "step-1" );
        toggleLoading( "step-1", true );

        getUsers().then( function( users ) { 
            console.log( users );

            if( users.length === 0 ) return noChildren();

            setupNonThSchoolList()

            if( users.length === 1 ) return step2();

            var html = '';
            state.users.forEach( function( user ) {
                html += templates.child( user );
            });
            $("#children").html( html );

            toggleLoading( "step-1", false );
        });
    }

    function setupNonThSchoolList() {
        // setup non th school list
        $.post('api/getNonThSchools.php', function(result) {
            var res = JSON.parse(result)
            var html = '<option value="-1" selected>Please Choose</option>'
            for (let s in res) {
                let key = s.substring(0, s.length - 1)
                html += '<option value=' + key + '>' + res[s] + "</option>";
            }
            html += '<option value="0">My school is not listed</option>'
            $("#non_th_school_id").empty()
            $("#non_th_school_id").append(html)
        })
    }

    var pleaseSelectErr = "Please select at least one child";
    var pleaseSelectErr_he = "נא לבחור לפחות ילד אחד";

    // show selected users
    function step2() {
        window.location.hash = 'step-2';
        state.selected_users = [];
        // state.cart = [];
        // make sure that we have at least one user
        if ( state.users.length === 1 ) {
            state.selected_users.push( Object.assign(
                { confirmed: false }, state.users[0]
            ));
        } else if (state.users.length === 0 || $('#step-1 #children input:checked').length === 0) {
            if (localStorage.getItem("locallang") == "he")
                return showError(pleaseSelectErr_he);
             return showError(pleaseSelectErr );
        }
        // determine if the navigation should show
        if ( state.users.length == 1 ) {
            $('#step-2 .navigation').hide();
        } else {
            $('#step-2 .navigation').show();
        }
        // get only the users that they checked
        $.each( $('#step-1 #children input:checked'), function( index, checkbox ) {
            var user = Object.assign( { confirmed: false }, // set confirmed to false and get a new object
                state.users.find( function( user ){ // filter the user options
                    return user.user_id == checkbox.dataset.user_id // only return users with the correct ID
                })
            );
            state.selected_users.push( user );
        });

        // remove the user if we had him before...
        // var user_ids = state.selected_users.map( function( user ) { return user.user_id } );
        // state.cart = state.cart.filter( function(item) { return user_ids.includes( item.meta.user_id ) } );

        // show anash kinder text if anash kinder school
        if ([61, 269].includes(user.school.school_id)) {
            $("#anash_kinder_text").show()
            $(".myshliachTerms").show()
        }
        else {
            $("#anash_kinder_text").hide()
            $(".myshliachTerms").hide()
        }

        // yahadus registration
        var australian = [ 55, 66, 110, 112, 180, 256 ];
        $('.book-bought').click( function() {
            if ( $(this).val() === '0' ) {
                console.log(school_id)
                if ( !australian.includes( school_id ) ) {
                    if (school_id == 61) {
                        $(".yahadus-myshliach").show()
                        $('#yahadus-registration').hide();
                        // $('#step-2 form #yahadus-registration-no').hide();
                    } else {
                        $(".yahadus-myshliach").hide()
                        $('#yahadus-registration').show();
                        // $('#step-2 form #yahadus-registration-no').show();
                    }
                }
                // } else {
                //     $('#step-2 form #book-purchase').hide();
                //     $("#step-2 form .book-purchase-myshliach").hide()
                // }
                $('#book-purchase').hide()
            } else if ( $(this).val() === '1' ) {
                $( '#yahadus-registration input' )[0].checked = false;
                $( '#yahadus-registration').hide();
                // $( '#step-2 form #yahadus-registration-no input' )[0].checked = false;
                // $( '#step-2 form #yahadus-registration-no').hide();
                $('#book-purchase').show();
                $(".yahadus-myshliach").hide()
                if ( school_id == 61 ) {
                    $(".book-purchase-item").hide()
                    $(".book-purchase-myshliach").show()
                } else {
                    $(".book-purchase-item").show()
                    $(".book-purchase-myshliach").hide()
                }
            }
        });

        $('.book-purchase').change( function() {
            var purchaseValue = $(this).val();
            if ( purchaseValue == 'store' ) $("#step-2 form #book-purchase-details").show();
            else $("#step-2 form #book-purchase-details").hide();
        });

        // show the page
        templates.showUser( state.selected_users[0], 0 );
        showSection('step-2');

        current_index = parseInt( $("#current_index").val() );
        selected_user = state.selected_users[ current_index ];
        school_id = selected_user.school.school_id;

        // show study guides info for all non Australian schools
        // if ( !australian.includes( school_id ) && school_id != anash_kinder && school_id != myshliach ) $("#study-guides").show();

        var addressErr = "The address, city, state, zip and country fields cannot be left blank!";
        var shippingChargeMsg30 = "There is an extra shipping charge of <b>$30.</b><br />";
        var shippingChargeMsg15 = "There is an extra shipping charge of <b>$15.</b>"

        if (localStorage.getItem("locallang") == "he") {
            addressErr = "הכתובת , העיר והמיקוד הם שדות חובה ";
            shippingChargeMsg30 = "ישנו חיוב נוסף של דמי משלוח על סך  " + "<b>$30.</b><br />";
            shippingChargeMsg15 = "ישנו חיוב נוסף של דמי משלוח על סך  " + "<b>$15.</b><br />";

        }

        // show address modal if purchasing book and from anash kinder
        $("#yahadus-registration input").click( function() {
            if ( $(this).val() == '1' && selected_user.school.school_id === anash_kinder ) {
                if (selected_user.parentAccount.admin_address1.length) $("#ship-address1").val( selected_user.parentAccount.admin_address1 )
                else $("#ship-address1").attr('placeholder', 'Address line 1')

                if (selected_user.parentAccount.admin_address2.length) $("#ship-address2").val( selected_user.parentAccount.admin_address2 );
                else $("#ship-address2").attr('placeholder', 'Address line 2')

                if (selected_user.parentAccount.admin_city.length) $("#ship-city").val( selected_user.parentAccount.admin_city );
                else $("#ship-city").attr('placeholder', 'city')

                if (selected_user.parentAccount.admin_state.length) $("#ship-state").val( selected_user.parentAccount.admin_state );
                else $("#ship-state").attr('placeholder', 'state')

                if (selected_user.parentAccount.admin_postal.length) $("#ship-zip").val( selected_user.parentAccount.admin_postal );
                else $("#ship-zip").attr('placeholder', 'zip')

                if (selected_user.parentAccount.admin_country.length) $("#ship-country").val( selected_user.parentAccount.admin_country );
                else $("#ship-country").val('USA')

                $("#shipping-modal").modal('show');
                $("#update-shipping").click( function() {
                    var info = {};
                    info.address1 = $("#ship-address1").val();
                    info.address2 = $("#ship-address2").val();
                    info.city = $("#ship-city").val();
                    info.state = $("#ship-state").val();
                    info.zip = $("#ship-zip").val();
                    info.country = $("#ship-country").val();
                    if (!(info.address1 && info.city && info.state && info.zip && info.country)) {
                        alert(addressErr);
                        return false;
                    }
                    var thisUser = selected_user; // needed for scope
                    $.post("updateAddress.php", { info: info }, function( res ) {
                        if ( res.success ) {
                            alert( res.data );
                            thisUser.parentAccount.admin_country = info.country;
                            // if (thisUser.parentAccount.admin_country.toUpperCase() == 'USA') $("#yahadus-shipping").html(shippingChargeMsg15);
                            // else $("#yahadus-shipping").html(shippingChargeMsg30);
                            $("#yahadus-shipping").html(shippingChargeMsg15)
                            $("#shipping-modal").modal('hide');
                        } else {
                            alert( res.error );
                        }
                    });
                });
            }
        });

        $.post('api/tasks/getSchools.php', function( result ) {
            if ( result.success ) {
                var schools = result.data;
                var html = '';
                for ( var s in schools ) {
                    html += "<option value=" + schools[s] + ">" + s + "</option>";
                }
                $("#school").append( html );
            } else {
                alert( success.error-div );
            }
        });

        $("#school").change(function () {
            var gradeMsg = "Select Grade";
            if (localStorage.getItem("locallang") == "he") {
               
                gradeMsg = "בחירת כיתה";
            }
            $.post('api/tasks/getGrades.php', { school_id: $(this).val() }, function( result ) {
                if ( result.success ) {
                    var grades = result.data;
                    var html = "<option value='0'>" + gradeMsg+"</option>";
                    for ( var g in grades ) {
                        html += "<option value=" + g + ">" + grades[g] + "</option>";
                    }
                    $("#grade").empty();
                    $("#grade").append( html );
                } else {
                    alert( result.error  );
                }
            });
        });

        $("#grade").change(function () {
            var studentMsg = "Select Student";
            if (localStorage.getItem("locallang") == "he") {

                studentMsg = "בחירת תלמיד";
            }
            $.post('api/tasks/getStudents.php', { class_id: $(this).val() }, function( result ) {
                if ( result.success ) {
                    var users = result.data;
                    var html = "<option value='0'>" + studentMsg+"</option>";
                    for ( var u in users ) {
                        html += "<option value=" + users[u] + ">" + u + "</option>";
                    }
                    $("#user").empty();
                    $("#user").append( html );
                } else {
                    alert( result.error  );
                }
            });
        });
        current_user = selected_user.user_id // for using current_user in chidon prizes cart
    }

    // select shipping option
    function step3() {
        if( state.selected_users.length === 0 ) return step1();
        window.location.hash = 'step-3';
        toggleLoading( 'step-3', true );
        showSection('step-3');
        // remove any shipping items from the cart
        state.shipping_type = 1;
        chayolei_user_ids = state.cart.map( function( item ){ 
            // skip for any non chayolei registration or chayolei lite registration
            return item.meta.registration_type !== 'chayolei' || item.meta.lite_version != undefined || item.meta.user_id
        });
        getShipping(
            // limit to kids registering for Tzivos Hashem
            state.selected_users.filter( function ( user ){
                return user.registrationStatus.chayolei === false && chayolei_user_ids.includes( user.user_id );
            // and just get their school ids
            }).map( function( user ) { 
                return user.school.school_id;
            })
        ).then( function( response ){
            if( !response ) return step4(); // skip straight to step 4
            $("#shipping-type-1").text("$" + response);
            // response.forEach( function( rate ) { 
            //     $("#shipping-type-" + rate.type).text("$" + rate.rate) 
            // });
            toggleLoading( 'step-3', false );
        });
    }
    
    // cart / payment information
    function step4() {
        if( state.selected_users.length === 0 ) return step1();
        window.location.hash = 'step-4';
        showSection("step-4");

        console.log(state.cart)
        
        var total = state.cart.reduce( function( total, item ) { return parseInt(total) + parseInt(item.price) }, 0 )
        if ( total <= 0 ){
            return registerUsers( { payment: { total: 0 } } );
        }

        templates.renderCheckout( state.cart );

        toggleLoading( 'payment', true );
        getPaymentProfiles().then( function( payment_profiles ){
            if ( !payment_profiles || payment_profiles.length === 0 ){
                templates.toggleNewCard( true );
                $("#card-on-file").hide();
            } else {
                templates.toggleNewCard( false );
                $("#card-on-file").show();
                templates.renderPaymentOptions( payment_profiles );
            }
            toggleLoading( 'payment', false );
        })
    }

    /*********************** FORM HANDLERS ***********************/
    function checkField( field ) {
        // skip if we are editing registration
        if (field.field === '#chidon-fee' && selected_user.getChidonInfo) return false
        let value, checked
        switch (field.type) {
            case 'value':
                value = $(field.field).val()
                console.log(field.field + '=' + value)
                if (value == '' || value == 0 || value == '0' || !value.length) return field.error
                break
            case 'amount':
                // make sure we have a number greater than 0
                value = parseInt($(field.field).val())
                console.log(field.field + '=' + value)
                if (value <= 0) return field.error
                break
            case 'check':
            case 'yes/no':
                // if 'only' key then check that
                if (field.only) {
                    if (field.only == 'new' && selected_user.registrationStatus.new_to_chidon == 0) break
                }
                checked = $(field.field).is(":checked")
                if (!checked) return field.error
                break
            case 'functionOnly':
                break
        }
        return false
    }

    function confirmUser(event) {
        event.preventDefault();

        // make sure picture is uploaded
        if ($("#user-img").attr('src').includes('addphoto.png')) {
            return showError(picError)
        }
        
        // update the user's information
        var postData = {};  var user_changed = false;
        current_index = parseInt( $("#current_index").val() );
        selected_user = state.selected_users[ current_index ];
        console.log( selected_user );

        // find all changed fields
        $( event.target ).serializeArray().forEach( function( item ) {
            if ( selected_user[ item.name ] == '' || 
                ( selected_user[ item.name ] && selected_user[ item.name ] != item.value  )
            ) {
                user_changed = true;
                selected_user[ item.name ] = postData[ item.name ] = item.value;
            }
        });
        // if the user changed update him in the background...
        if ( user_changed ) {
            state.selected_users[ current_index ] = selected_user;
            updateUser( selected_user.user_id, postData ).then( function() {
                return getUsers();
            });
        }

        // make sure not to recharge khk if editing info (for cart info)
        if (selected_user.getChidonInfo && selected_user.getChidonInfo.khk_reg === '1') khk_fee = 0

        // Detect and validate the charges accepted.
        selected_charges = {
            chayolei: $('#chayolei-registration input')[0].checked,
            // chayolei_lite: $('#chayolei-lite-registration input')[0].checked,
            ckids: $('#ckids-registration input')[0].checked, 
            chidon: $('#chidon-registration input')[0].checked,
            yahadus: $('#yahadus-registration input')[0].checked,
            khk: $("#khk input")[0].checked
        }
        if ( selected_charges.chayolei === false 
            // && selected_charges.chayolei_lite === false 
            && selected_charges.ckids === false
            && selected_charges.chidon === false 
            // && selected_charges.yahadus === false
        ){
            return showError(
                Err1
            );
        }

        if ( selected_charges.chayolei === true ) {
            // check that privacy policy is checked off
            if (! $("#media").is(":checked")) {
                return showError('You must indicate your acceptance of our Privacy Policy.')
            }
        }

        if ( selected_charges.chidon === true ) {
            let mandFields = [
                {
                    field: '#chidon-fee',
                    type: 'amount',
                    error: 'You must choose how much you are paying for chidon registration'
                },
                {
                    field: '.limmud',
                    type: 'check',
                    error: 'You must choose a limmud track'
                },
                {
                    field: '.recruit',
                    type: 'yes/no',
                    only: 'new',
                    error: 'You must indicate if someone recruited you',
                    yesDependents: [
                        {
                            field: '#recruited_by_user_serial',
                            type: 'value',
                            error: 'You have indicated that you have been recruited, please enter the serial number of the friend that recruited you'
                        }
                    ]
                },
                {
                    field: '#nameChoice',
                    type: 'value',
                    error: 'You must enter the name you would like to have on the your personalized bottle'
                },
                {
                    field: '#chidon-sweater-size',
                    type: 'value',
                    error: 'You must choose a sweater size'
                },
                {
                    field: '#chidon-book',
                    type: 'amount',
                    error: 'You must choose the book number you will be using'
                },
                {
                    field: '.book-bought',
                    type: 'yes/no',
                    error: 'You must indicate whether you purchased a book or not',
                    yesDependents: [
                        {
                            field: '.book-purchase',
                            type: 'check',
                            error: 'You must indicate where you bought your book'
                        },
                        {
                            field: '#bookVersion',
                            type: 'amount',
                            error: 'You must indicate which book version you will be using'
                        },
                    ],
                    noDependents: [
                        {
                            field: '.yahadus',
                            type: 'check',
                            error: 'You have not indicated whether you would like to purchase a book or not',
                            schoolException: 61
                        },
                    ]
                },
                {
                    field: '.yahadus-poll',
                    type: 'check',
                    error: 'You must indicate how you will be learning for the Chidon'
                },
                {
                    field: '#shabbaton',
                    type: 'check',
                    error: 'You must indicate your acceptance of all Terms (1st term not checked)'
                },
                {
                    field: '#heNameSpelling',
                    type: 'check',
                    error: 'You must indicate your acceptance of all Terms (2nd term not checked)'
                },
                {
                    field: '#enNameSpelling',
                    type: 'check',
                    error: 'You must indicate your acceptance of all Terms (3rd term not checked)'
                },
                {
                    field: '#edits',
                    type: 'check',
                    error: 'You must indicate your acceptance of all Terms (4th term not checked)'
                },
                {
                    field: '#privacy',
                    type: 'check',
                    error: 'You must indicate your acceptance of all Terms (5th term not checked)'
                }
            ]

            // check all fields
            let error
            let errors = []
            for (field of mandFields) {
                error = checkField(field)
                if (error) errors.push(error)
                else if (field.type == 'yes/no') {
                    // check dependants if relevant
                    let elem = field.field + ':checked'
                    let value = $(elem).val()
                    if (value === '1') {
                        if (field.yesDependents) {
                            for (dependent of field.yesDependents) {
                                error = checkField(dependent)
                                if (error) errors.push(error)
                            }
                        }
                    } else if (value === '0') {
                        if (field.noDependents) {
                            for (dependent of field.noDependents) {
                                if (dependent.schoolException && selected_user.school.school_id == dependent.schoolException) continue // don't validate if exception for this school
                                error = checkField(dependent)
                                if (error) errors.push(error)
                            }
                        }
                    }
                }
            }
            // check myshliach term if relevant
            if (selected_user.school_id == myshliach) {
                if (! $("#myshliach").is(":checked")) {
                    errors.push("You must indicate your acceptance of all Terms (6th term not checked)")
                }
            }
            console.log(errors)
            if (errors.length) {
                let html = ''
                for (let i = 1; i <= errors.length; i++) {
                    html +=  i + ') ' + errors[i-1] + '.<br />'
                }
                return showError(html)
            }

            // check yarmulka
            if ($(".gender:checked").val() === 'M' && $("#yarmulka-size").val() == 0) {
                return showError(Err14)
            } else if ($(".gender:checked").val() === 'F') {
                $("#yarmulka-size").val(0)
            }

            // make sure that if they bought a book from a store, that the store info is filled out
            if ( $(".book-purchase:checked").val() == 'store' && ($("#store-name").val().trim() == '' || $("#store-city").val().trim() == '' ) ) {
                return showError(Err5);
            }
        }

        // make sure non th school field is not empty
        if ( [ 269, 61 ].includes( selected_user.school.school_id ) ) {
            var non_th_school = $("#non_th_school_id").val();
            if (non_th_school == 0 || non_th_school == '0' || non_th_school == '-1') {
                non_th_school = $("#non_th_school").val().trim()
                if (non_th_school.length < 3) return showError(Err10);
            }
        }

        // validate that they have accepted to be used in media campaigns
        // if ( $(event.target).find( "#media" )[0].checked ){
        //     $(event.target).find( "#media" )[0].checked = false;
        // } else {
        //     return showError(Err11 )
        // }
        // remove the user if we had him before...
        state.cart = state.cart.filter( function(item) { return item.meta.user_id != selected_user.user_id } );
        console.log(state.cart)
        // and re-add him to the cart
        if ( selected_charges.chayolei ) {
            selected_user.registrationRates.chayolei = parseInt( $("select#chayolei-fee").val() );
            var discount = selected_user.getDiscount.length ? selected_user.getDiscount[0].amount : 0
            state.cart.push({
                description: Msg1 + selected_user.first,
                price: selected_user.registrationRates.chayolei,
                meta: {
                    type: 'registration',
                    user_id: selected_user.user_id,
                    registration_type: 'chayolei',
                    paid: selected_user.registrationRates.chayolei - discount,
                    discount: discount
                }
            });
        }
        if ( selected_charges.chayolei_lite ) {
            selected_user.registrationRates.chayolei = 0;
            state.cart.push({
                description: Msg1 + selected_user.first,
                price: selected_user.registrationRates.chayolei,
                meta: {
                    type: 'registration',
                    user_id: selected_user.user_id,
                    registration_type: 'chayolei',
                    paid: selected_user.registrationRates.chayolei,
                    lite_version: 1
                }
            });
        } 
        if ( selected_charges.ckids ) {
            selected_user.registrationRates.chayolei = 0;
            state.cart.push({
                description: Msg3 + selected_user.first,
                price: selected_user.registrationRates.chayolei,
                meta: {
                    type: 'registration',
                    user_id: selected_user.user_id,
                    registration_type: 'chayolei',
                    paid: selected_user.registrationRates.chayolei,
                    ckids: 1
                }
            });
        } 
        if ( selected_charges.chidon ) {
            var anash = selected_user.school.school_id === anash_kinder;
            var myshliach = selected_user.school.school_id === myshliach;
            var recruited = $(".recruit:checked").length ? $(".recruit:checked").val() : 0
            var recruited_by = $("#recruited_by_user_serial").val()
            if (!recruited || recruited === '0' || recruited_by == '') recruited_by = 0
            let fee = parseInt($("#chidon-fee").val())
            let poll = []
            $(".yahadus-poll").each( function() {
                if ($(this).is(":checked")) poll.push(this.value)
            })
            if ( [ 269, 61 ].includes( selected_user.school.school_id ) ) showClasses = 1;
            state.cart.push({
                description: Msg4 + selected_user.first + ' ' + (myshliach || anash ? selected_user.school.school_name : '') + ( anash ? Msg5 : ''),
                price: fee,
                meta: {
                    type: 'registration',
                    user_id: selected_user.user_id,
                    registration_type: 'chidon',
                    paid: fee,
                    track: $(".limmud:checked").val(),
                    size: $("select#chidon-sweater-size").val(),
                    yarmulka: $("select#yarmulka-size").val(),
                    book: $("select#chidon-book").val(),
                    purchased: $(".book-bought:checked").val(),
                    purchasedWhere: $(".book-purchase:checked").length ? $(".book-purchase:checked").val() : '',
                    store: {
                        store_name: $("#store-name").val(), 
                        store_city: $("#store-city").val()
                    },
                    bookVersion: $("#bookVersion").val(),
                    recruited: recruited,
                    recruitedBy: recruited_by,
                    poll: poll,
                    name_pref: $("#nameChoice").val(),
                    comments: $("#comments").val(),
                    chidon_prizes: user_prizes[current_user]
                }
            });
        }
        if ( selected_charges.yahadus ) {
            var shipping_included = selected_user.school.shipping_method !== 'pickup';
            var shipping_charge = 0;
            var cost = 40
            if ( selected_user.school.school_id == anash_kinder ) {
                shipping_included = true; // override for anash kinder to make sure shipping is being charged
                if (usa.includes(selected_user.parentAccount.admin_country)) shipping_charge = 15;
                else return showError('You cannot order a Yahadus Book and have it shipped outside of the USA!');
            } else {
                // shipping to non USA schools is an additional $15
                let country = selected_user.school.school_country
                if (! usa.includes(country)) {
                    shipping_charge = 15
                }
            }
            // // don't add to cart if anash kinder / myshliach and not in USA
            // if (
            //     ! [ 269, 61 ].includes( selected_user.school.school_id ) ||
            //     ( [ 269, 61 ].includes( selected_user.school.school_id ) && selected_user.parentAccount.admin_country.toUpperCase() == 'USA' )
            // ) {
                state.cart.push({
                    description: Msg6 + selected_user.first + ( shipping_included ? Msg7 : '' ),
                    price: shipping_included ? (cost + shipping_charge) : cost,
                    meta: {
                        type: 'registration',
                        user_id: selected_user.user_id,
                        registration_type: 'yahadus',
                        paid: shipping_included ? (cost + shipping_charge) : cost
                    }
                });
            // }
        }
        if ( selected_charges.khk && khk_fee ) {
            state.cart.push({
                description: selected_user.first + " KHK Enrollment",
                price: khk_fee,
                meta: {
                    type: 'registration',
                    registration_type: 'khk',
                    paid: khk_fee,
                    user_id: selected_user.user_id
                }
            })
        }

        // show modal for chidon prizes
        if (selected_charges.chidon) {
            setupChidonPrizes()
        }

        else nextStep()
        // nextStep()
    }

    function setupChidonPrizes() {
        // initialize user prize cart
        user_prizes[current_user] = []
        // get prizes
        $.post('api/getPrizes.php', { user_id: current_user }, function(results) {
            var res = JSON.parse(results)
            console.log(res)
            var html = ''

            for (let prize of res) {
                var id = prize.prize_id
                var height = 'height: 100px;'
                if (prize.personalization) height = 'height: 135px;'
                var personalization = prize.personalization ? 1 : 0
                html += `<div style="${height} border-bottom: 1px solid #D3D3D3; margin-top: 10px;">
                        <img src="https://mashpia.com${prize.prize_picture}" style="float: right; height: 50px;" />
                        <input type="checkbox" class="prize" name="prize_${id}" data-info="${id}:${prize.price}:${personalization}" `
                if (prize.selected) html += 'checked '
                if (prize.quantity <= 0) html += 'disabled '
                html += `/>
                        ${prize.prize_name} (${prize.quantity} left in stock)<br />
                        ${parseInt(prize.price)} Credits`
                if (prize.color) {
                    html += `<br />Color: ${prize.color}`
                }
                if (prize.size) {
                    html += `<br />Size: ${prize.size}`
                }
                if (prize.personalization) {
                    html += `<br /><span style="font-size: 12px">${prize.personalization}. `
                    if (id == 175) html += 'Limit 12 characters.'
                    else html += 'Limit 22 characters.'
                    html += ` <input type="text" name="he_name_${id}" id="he_name_${id}" class="he_name${id == 175 ? ' bracelet' : ''}" data-info="${id}" /></span>`
                }
                html += `</div>`

                if (prize.selected) addToPrizes(id, prize.price, personalization)
            }
            $("#listOfPrizes").empty()
            $("#listOfPrizes").append(html)
            $("#prizes").modal('show')

            $(".prize").click( function(e) {
                var info = $(this).data('info')
                var infoArr = info.split(':')
                var prize = infoArr[0]
                var price = infoArr[1]
                var personalization = infoArr[2]
                if ($(this).is(":checked")) {
                    if (!addToPrizes(prize, price, personalization)) {
                        e.target.checked = false
                    }
                } else {
                    if (!removeFromPrizes(prize)) {
                        e.target.checked = true
                    }
                }
            })

            $(".he_name").blur( function (e) {
                var he_name = e.target.value
                var id = $(this).data('info')
                if (!addHeName(id, he_name)) {
                    // alert('Error adding hebrew name')
                    // add prize to list
                    $(this).parent().parent().find('.prize').trigger('click')
                    if (!addHeName(id, he_name)) {
                        alert('Error adding hebrew name')
                    }
                }
            })
        })
    }

    $("#prizes").on('hidden.bs.modal', function (e) {
        if (validatePrizes()) {
            addToCart() // add prize cart to state.cart
            nextStep()
        }
        else {
            $("#prizes").modal('show')
        }
    })

    function validatePrizes() {
        if (!user_prizes[current_user] || !user_prizes[current_user].length) {
            alert('You must choose which prizes you would like to receive if you are eligible!')
            return false
        }

        // make sure we have at least 65 credits
        var total = 0
        // make sure that he name was filled out if its needed
        for (var p of user_prizes[current_user]) {
            total += parseInt(p.price)
            if (total < 65) {
                alert('You must choose at least 65 credits worth of prizes.')
                return false
            }
            if (parseInt(p.personalization) && (!p.he_name || p.he_name == '')) {
                alert('You must enter a hebrew name for the prizes that need it!')
                return false
            }
        }
        return true
    }

    function addToPrizes(prize, price, personalization) {
        var MAX = 75
        var total = 0
        var found = false
        for (var p of user_prizes[current_user]) {
            total += parseInt(p.price)
            if (p.id == prize) {
                found = true
            }
        }
        if (! found) {
            if (total + parseInt(price) > MAX) {
                alert('You cannot choose more than 75 credits worth of prizes.')
                return false
            } else {
                var prizeToAdd = { id: prize, price: price, personalization: personalization }
                user_prizes[current_user].push(prizeToAdd)
                return true
            }
        }
    }

    function removeFromPrizes(prize) {
        for (var i in user_prizes[current_user]) {
            var p = user_prizes[current_user][i]
            if (p.id == prize) {
                user_prizes[current_user].splice(i, 1)
                return true
            }
        }
        return false
    }

    function addHeName(prize_id, he_name) {
        for (var p in user_prizes[current_user]) {
            if (user_prizes[current_user][p].id == prize_id) {
                var prize = user_prizes[current_user][p]
                prize.he_name = he_name
                return true
            }
        }
        return false
    }

    function addToCart() {
        for (var item of state.cart) {
            if (item.meta.registration_type == 'chidon' && item.meta.user_id == current_user) {
                item.meta.chidon_prizes = user_prizes[current_user]
            }
        }
    }

    function nextStep() {
        current_index += 1;
        if ( state.selected_users.length <= current_index ){
            step3();
        } else {
            selected_user = state.selected_users[ current_index ]
            current_user = selected_user.user_id // for using current_user in chidon prizes cart
            school_id = selected_user.school.school_id
            templates.showUser( selected_user, current_index );
            $('html, body').animate({ scrollTop: 0 }, 'fast'); // scroll to the top of the page
        }
    }

    function confirmShipping( event ){
        event.preventDefault();
        // remove any old shipping items from the cart
        state.cart = state.cart.filter( function(item) { return item.meta.type != 'shipping' } );
        
        var selected_type = $("#shipping-type:checked").val();
        $("#selected-shipping-type").val( selected_type );
        state.shipping_type = selected_type;
        
        var shipping_charges = parseInt(
            $("#shipping-type-"+selected_type).text().replace( /^\D+/g, '')
        );
        state.cart.push({
            description: 'Prepaid Shipping',
            price: shipping_charges,
            meta: {
                type: 'shipping',
                shipping_type: selected_type,
                shipping_charges: shipping_charges
            }
        });
        return step4();
    }

    function registerUsersHandler( event ){
        event.preventDefault();
        var postData = {};
        // show loading
        $("#payment-button").html('<i class="fas fa-circle-notch fa-spin fa-2x"></i>');
        postData.payment = formToJSON( event.target );
        // sanitize input
        postData.payment["cc-number"] = postData.payment["cc-number"].replace(/ /g, '');
        postData.payment["cc-exp"] = postData.payment["cc-exp"].replace(/ /g, '');
        postData.payment["x_card_code"] = postData.payment["x_card_code"].replace(/ /g, '');
        // validate form 
        if ( postData.payment["cc-number"] ) {
            event.target.checkValidity();
            $( event.target ).addClass('was-validated');
        }
        registerUsers( postData );
    }

    /*********************** HELPER FUNCTIONS ***********************/
    // navigate to a specific section
    function showSection( id ){
        $( "#pages section" ).hide();
        $( "#pages section#" + id ).show();
    }
    $(window).bind('hashchange', function() {
        // support sub-routing in step-2
        if ( window.location.hash.match(/^#step-2-[0-9]/g) ) {
            var index = parseInt(window.location.hash.split('-')[2]);
            templates.showUser( state.selected_users[index], index );
            if( $("section#step-2" ).css('display') == 'none' ) showSection( 'step-2' );
        } else if( $("section" + window.location.hash ).css('display') == 'none' ){
            if ( window.location.hash === '#step-1' ) return step1();
            if ( window.location.hash === '#step-2' ) return step2();
            if ( window.location.hash === '#step-3' ) return step3();
            if ( window.location.hash === '#step-4' ) return step4();
        }
    });
    // toggle the loading dot
    function toggleLoading( id, loading ){
        id = "#" + id;
        if( !loading ) {
            $( id + " .spinner").hide();
            $( id + " .content").show();
        } else {
            $( id + " .spinner").show();
            $( id + " .content").hide();
        }
    }
    // Auto-detect credit card type
    function validateCardInput( event ){
        var cardInput = $("#cc-number");
        cardInput.removeClass( "visa mastercard amex discover" );
        $("#x_card_code").attr("placeholder", "XXX" );
        // digits only
        if ( !event.key.match(/[0-9]/) )
            event.target.value = event.target.value.replace( /[^0-9 ]/g, '' );
        // decect card type from: https://www.regular-expressions.info/creditcard.html
        var cardNumber = event.target.value.replace(/\D/g, '');
        if ( cardNumber.match( /^4[0-9]{12}(?:[0-9]{3})?$/g ) ) {
            cardInput.addClass( "visa" );
        } else if ( cardNumber.match( /^(?:5[1-5][0-9]{2}|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)[0-9]{12}$/g ) ) {
            cardInput.addClass( "mastercard" );
        } else if ( cardNumber.match( /^3[47][0-9]{13}$/g ) ) {
            cardInput.addClass( "amex" );
            $("#x_card_code").attr("placeholder", "XXXX" );
        } else if ( cardNumber.match( /^6(?:011|5[0-9]{2})[0-9]{12}$/g ) ){
            cardInput.addClass( "discover" );
        }
    }

    /*********************** API CALLS ***********************/
    function getUsers(){
        return new Promise( function( resolve, reject ){
            APIRequest( 'GET', api_url + '?action=getUsers', {}, function( response ) {
                state.users = [];
                response.users.forEach( function( user ) {
                    user.dob = user.dob ? user.dob.split(" ")[0] : user.dob;
                    if ( user.registrationStatus.chayolei === false || user.registrationStatus.chidon === false ||
                            user.registrationStatus.chidonEdit === true )
                        state.users.push( user );
                });
                if ( state.users.length === 1 ) state.selected_users = state.users;
                resolve( state.users );
            });
        });
    }

    // TODO, update to point to unified core API
    function updateUser( user_id, postData ){
        return new Promise( function( resolve, reject ){
            $.post("/api/core/users?id=" + user_id, postData, function( response ) {
                if ( !response.success ) {
                    showError(Err16 );
                }
                resolve( response );
            });
        });
    }

    function getShipping( school_ids ){
        return new Promise( function( resolve, reject ){
            APIRequest( 'POST', api_url + '?action=getShipping', { school_ids: school_ids }, resolve);
        });
    }

    function getPaymentProfiles(){
        return new Promise( function( resolve, reject ){
            APIRequest( 'GET', '/api/payments/profiles.php', {}, resolve);
        });
    }

    function registerUsers( postData ){
        return new Promise( function( resolve, reject ){
            var cart_details = state.cart.map( function(item){ return item.meta } );
            postData.registrations = cart_details.filter( function( item ) { return item.type == 'registration' } );
            postData.shipping = cart_details.find( function( item ) { return item.type == 'shipping' } );
            postData.shipping = postData.shipping || { shipping_charges: 0, shipping_type: 0 };
            APIRequest( 'POST', api_url + '?action=registerUsers', postData, resolve)
        }).then( function( data ) {
            if ( showClasses ) {
                $("#successModal #success").append("<p>"+Msg8+"<a href='https://merkos302.formstack.com/forms/chidon_shiurim_registration'>"+Msg9+"</a></p>");
            }
            $("#successModal").modal('show'); 
        });
    }

    /**
     * onImageUploaded
     * 
     * callback for when image is uploaded to server.
     * 
     * updates the UI and form ( steps 1 and 2 ) and attempts to update the users profile picture
     * 
     * @param {object} data 
     */
    function onImageUploaded( data ){
        var user_id = $( "#step-2 form #user_id" ).val();
        
        $("input#mobile_pic").val( data.filename );
        $("img#user-img, #child-" + user_id + " img").attr( "src", data.location );
        
        $.post("/api/core/users?id=" + user_id, { mobile_pic: data.filename }, function( response ){
            if ( !response.success ){
                showError( Err12);
            };
        });
    }
}();

var templates = function(){
    return {
        child: function( child ){
            return '<div class="child col-12 col-lg-6" id="child-' + child.user_id + '"><label>' +
                '<div class="row">' +
                    '<div class="col-4">' +
                        '<img src="' + child.profilePicture + '" />' +
                    '</div><div class="col-6">' +
                        '<p class="name">' + child.first + " " + child.last + '<div style="font-size: 1em;">Serial: ' + child.user_serial + '</div></p>' +
                        /*
                        ( child.registrationStatus.chayolei === false ? 
                            child.school.inst_id === 10 ? ( '<p class="reg_cost">CKids Registration: $0</p>' ) :
                            ( '<p class="reg_cost">Tzivos Hashem: $' + (!child.registrationStatus.confirmation ? child.registrationRates.chayolei : 0) ) : '' ) +
                            // ( '<p class="reg_cost">Tzivos Hashem: $' + child.registrationRates.chayolei + '<br />($0 Free Lite Edition)</p>' ) : '' ) +
                        ( child.registrationStatus.chidon === false ? 
                            ( '<p class="reg_cost">Chidon: $' + child.registrationRates.chidon + '</p>' ) : '' ) +
                         */
                    '</div><div class="col-2">' +
                        '<input type="checkbox" data-user_id="' + child.user_id + '" />' +
                        '<span class="checkbox"></span>' +
                    '</div>' +
                '</div>' +
            '</label></div>';
        },
        setChidonReg: function( user ) {
            /*
            There's a couple of different scenarios that can be happening
            1. a non-registered child is shown for the first time
            2. a non-registered child is shown by coming "back" to him/her
            3. a non-registered child is shown "after" a child who was previously registered
            4. a registered child is shown for editing "first" time
            5. a registered child is shown by coming "back" to that child (so child has both original info and cart info)
            **/
            let html = '<option value="0">Select Amount to Pay</option>'
            let fees = [20, 25, 30, 40];
            if (user.school.school_id == 61) fees = [30, 35, 40, 50]
            else if (user.school.school_id == 269) fees = [50, 55, 60, 70]
            for (let fee of fees) {
                html += `<option value="${fee}">$${fee}</option>`
            }
            $("#chidon-fee").empty().append(html)

            if (! user.getChidonInfo) {
                // non registered child
                // check if coming after a prev registered child
                if ($("#chidon").prop('disabled')) {
                    $("#chidon").attr('disabled', false)
                    $("#chidon-fee").attr('disabled', false)
                    // if ($("#chidon").is(":checked")) {
                        // reset chidon fee
                        $("#chidon-fee").html(html)
                        $("#chidon-fee").show()
                        $("#reg_text").show()
                        $("#chidon-registration").find('#chidon-reg-text').html('<strong>I would like to enroll for Chidon</strong>')
                    // }
                    if ($("#chidon").is(":checked")) $("#chidon").trigger('click')
                }
                // same idea for khk
                if ($("khk_enrollment").prop('disabled')) {
                    $("#khk_enrollment").attr('disabled', false)
                    if ($("#khk_enrollment").is(":checked")) $("#khk-reg-text").html('Registration Fee: An additional $' + khk_fee)
                }
                $("#khk-reg-info").show()
                $("#khk-edit-info").hide()
            } else {
                if (! $("#chidon").is(":checked")) $("#chidon").trigger('click')
                $("#chidon").attr('disabled', true)
                $("#chidon-fee").html("<option value='0'>$0</option>")
                $("#chidon-fee").attr('disabled', true)
                $("#chidon-fee").hide()
                $("#reg_text").hide()
                $("#chidon-registration").find('#chidon-reg-text').html(`
                        <strong>You have already enrolled for $${user.getChidonInfo.amount}, the payment can not be edited. 
                    `)
                $("#chidon-details").show()
                // also khk
                $("#khk-reg-info").hide()
                $("#khk-edit-info").show()
            }
        },
        showUser: function( user, index ){
            if( index > 0 ) window.location.hash = 'step-2-' + index;

            $(".chayolei_year").text(user.regYears.chayolei)
            $(".chidon_year").text(user.regYears.chidon)

            // hide all sections that only should be shown under certain circumstances
            $(".hide").hide();

            // hide chayolei reg if not applicable
            if (user.registrationStatus.chayolei) $("#chayolei-registration").hide()
            else $("#chayolei-registration").show()

            // hide chidon reg if not applicable
            if (user.registrationStatus.chidon && !user.getChidonInfo) $("#chidon-registration").hide()
            else $("#chidon-registration").show()

            // determine if need to show non th school fields or not
            if (user.school.school_id === anash_kinder || user.school.school_id === myshliach) {
                $( '#non_th_school_id' ).val( user.non_th_school_id );
                $( '#non_th_school' ).val( user.non_th_school );
                $("#non_th_school_id_div").show()
                if (user.non_th_school_id == 0) $("#non_th_school_div").show()
            }

            $("#non_th_school_id").change( function () {
                if ($(this).val() === '0') {
                    $("#non_th_school_div").show()
                } else {
                    $("#non_th_school_div").hide()
                }
            })

            // add the dropdown for naftali
            var class_select = $( '#class_name select' );
            class_select.html('');
            if ( [ 269, 61 ].includes( user.school.school_id ) ) {
                // get the class list and update the dropdown
                $.get( "api/classes.php", { 'school_id': user.school.school_id }, function( response ) {
                    class_select.html('');
                    response.data.forEach( function( option ) {
                        class_select.append("<option value='" + option.id + "'>" + option.name + "</option>");
                    });
                    class_select.val( user.class_id );
                    $( '#class_name' ).show(); // make sure it is visiable
                });
            } else {
                $( '#class_name' ).hide(); // hide it
            }

            // setup the index state
            $( '#step-2 form #current_index' ).val( index );

            // populate fields with current values
            $( '#user_id' ).val( user.user_id );
            $( '#mobile_pic' ).val( user.mobile_pic );
            $( '#mobile_pic + img' ).attr( 'src', user.profilePicture );
            $( '.gender[value=\'' + user.gender + '\']')[0].checked = true;
            $( '#school_name' ).val( user.school.school_name );
            $( '#first' ).val( user.first );
            $( '#last' ).val( user.last );
            $( '#first_he' ).val( user.first_he );
            $( '#last_he' ).val( user.last_he );
            $( '#lang_id' ).val( user.lang_id );
            $( '#dob' ).val( user.dob );

            // setup the payment options
            templates.toggleRates( user );

            // add shipping text where appropriate
            if (usa.includes(user.parentAccount.admin_country)) {
                if (user.school.school_id === anash_kinder)  $("#yahadus-shipping").html("There is an extra shipping charge of <b>$15.</b>");
                else $("#yahadus-shipping").html("Your book will be shipped to your school free of charge.");
            } else {
                if (user.school.school_id === anash_kinder)  $("#yahadus-shipping").html("We do not currently ship internationally. Orders placed to another country will be canceled.")
                else $("#yahadus-shipping").html("There is an extra shipping charge of <b>$15.</b>");
            }

            // show khk if relevant
            if (!user['registrationStatus']['khk']) {
                $("#khk").show()
                $("#khkWhatsapp").show()
            } else {
                $("#khk").hide()
                $("#khkWhatsapp").hide()
            }

            // show/hide yarmulka
            $("#chidonWhatsapp").show()
            if (user.gender == 'M') {
                $("#yarmulka").show()
                $("#boysWhatsapp").show()
                $("#girlsWhatsapp").hide()
                $("#khkBoys").show()
                $("#khkGirls").hide()
            } else {
                $("#yarmulka").hide()
                $("#girlsWhatsapp").show()
                $("#boysWhatsapp").hide()
                $("#khkBoys").hide()
                $("#khkGirls").show()
            }

            if (user.registrationStatus.new_to_chidon == 1) {
                $("#chidonRecruitment").show()
            } else {
                $("#chidonRecruitment").hide()
                $("#recruited_by_user_serial").val('')
            }

            var bookHtml = "<option value='0'>Please choose</option>"
            for (var i = 2011; i <= 2022; i++) {
                bookHtml += `<option value='${i}'>${i}</option>`
            }
            $("#step-2 form #bookVersion").empty()
            $("#step-2 form #bookVersion").append(bookHtml)

            // setup yahadus poll html
            let poll = {
                own: 'On my own',
                chavrusa: 'With a chavrusa (study partner)',
                parent: 'With a parent',
                grandparent: 'With a Bubby or Zaidy',
                online: 'On the online class',
                thechidon: 'Online at thechidon.com'
            }
            let html = ''
            for (let k in poll) {
                html += `<label>
                            <div style="float: left; padding-right: 15px;">
                                <input type="checkbox" class="yahadus-poll" value="${k}" />
                                <span class="checkbox"></span>
                            </div>
                            <div style="float: right;">
                                ${poll[k]}
                            </div>
                        </label><br />`
            }
            $("#yahadus-poll").empty().append(html)

            // reset all fields
            let resets = [
                {
                    field: '#chidon-fee',
                    type: 'select',
                    cart: 'paid'
                },
                {
                    field: '.limmud',
                    type: 'radio',
                    cart: 'track',
                    db: 'test_type'
                },
                {
                    field: '.recruit',
                    type: 'radio',
                    cart: 'recruited',
                    triggerEvent: true,
                    db: 'custom'
                },
                {
                    field: '#recruited_by_user_serial',
                    type: 'text',
                    cart: 'recruitedBy',
                    db: 'recruited_by'
                },
                {
                    field: '#nameChoice',
                    type: 'text',
                    cart: 'name_pref',
                    db: 'name_pref'
                },
                {
                    field: '#yarmulka-size',
                    type: 'select',
                    cart: 'yarmulka',
                    db: 'yarmulka'
                },
                {
                    field: '#chidon-sweater-size',
                    type: 'select',
                    cart: 'size',
                    db: 'size'
                },
                {
                    field: '#chidon-book',
                    type: 'select',
                    cart: 'book',
                    db: 'book'
                },
                {
                    field: '.book-bought',
                    type: 'radio',
                    cart: 'purchased',
                    triggerEvent: true,
                    db: 'custom'
                },
                {
                    field: '.book-purchase',
                    type: 'radio',
                    cart: 'purchasedWhere',
                    db: 'location'
                },
                {
                    field: '#bookVersion',
                    type: 'select',
                    cart: 'bookVersion',
                    db: 'version'
                },
                {
                    field: '.yahadus',
                    type: 'radio',
                    triggerEvent: true
                },
                {
                    field: '.yahadus-poll',
                    type: 'multi',
                    cart: 'poll',
                    db: 'poll'
                },
                {
                    field: '#comments',
                    type: 'text',
                    cart: 'comments',
                    db: 'comments'
                },
                {
                    field: '#agreements input',
                    type: 'checkbox',
                    cart: true,
                    db: 'custom'
                },
                {
                    field: '#khk_enrollment',
                    type: 'checkbox',
                    db: 'khk_reg'
                },
                {
                    field: '#media',
                    type: 'checkbox',
                },
                {
                    field: '#myshliach',
                    type: 'checkbox'
                }
            ]

            // reset all fields
            for (reset of resets) {
                switch (reset.type) {
                    case 'checkbox':
                    case 'radio':
                    case 'multi':
                        $(reset.field).each( function() {
                            this.checked = false
                        })
                        break
                    case 'text':
                        $(reset.field).val('')
                        break
                    case 'select':
                        let value = 0
                        $(reset.field).val(value)
                        break
                }
            }

            this.setChidonReg( user ) // can only do it after the resets bc the chidon fee value is reset to 20

            if ([61, 269].includes(user.school.school_id)) {
                $("#anash_kinder_text").show()
                $(".myshliachTerms").show()
            }
            else {
                $("#anash_kinder_text").hide()
                $(".myshliachTerms").hide()
            }

            // find out if the cart already has info for this child
            let info = state.cart.filter(item => item.meta.user_id == user.user_id)
            if (info.length) {
                let yahadus = false
                // trigger clicks for the registrations already checked off
                for (item of info) {
                    if (item.meta.registration_type === 'chayolei') {
                        $("#chayolei").trigger('click')
                        $("#media").trigger('click')
                    }
                    if (item.meta.registration_type === 'chidon') {
                        document.getElementById('chidon').checked = false
                        $("#chidon").trigger('click')
                        for (elem of resets) {
                            if (elem.cart) {
                                switch (elem.type) {
                                    case 'text':
                                    case 'select':
                                        $(elem.field).val(item.meta[elem.cart])
                                        break
                                    case 'checkbox':
                                        $(elem.field).each( function() {
                                            this.checked = true
                                        })
                                        break
                                    case 'radio':
                                        // for some inputs we need to trigger a click event
                                        $(elem.field).each( function() {
                                            if (this.value === item.meta[elem.cart]) {
                                                if (elem.triggerEvent) $(this).trigger('click')
                                                else this.checked = true
                                            }
                                        })
                                        break
                                    case 'multi':
                                        $(elem.field).each(function () {
                                            if (item.meta[elem.cart] && item.meta[elem.cart].includes(this.value)) this.checked = true
                                        })
                                        break
                                }
                            }
                        }
                    }
                    if (item.meta.registration_type === 'yahadus') {
                        yahadus = true
                        $(".yahadus").each( function() {
                            if (this.value === '1') this.checked = true
                        })
                    }
                    if (item.meta.registration_type === 'khk') {
                        $("#khk_enrollment").each( function() {
                            this.checked = true
                        })
                    }
                }
                // check off no for yahadus book if yes is not selected
                if (! yahadus) {
                    $(".yahadus").each( function() {
                        if (this.value === '0') this.checked = true
                    })
                }
            } else {
                // check if child already registered and we are pulling up their info to allow changes
                if (user.getChidonInfo) {
                    let info = user.getChidonInfo
                    for (elem of resets) {
                        if (elem.db) {
                            switch (elem.type) {
                                case 'text':
                                case 'select':
                                    if (info[elem.db]) $(elem.field).val(info[elem.db])
                                    break
                                case 'checkbox':
                                case 'radio':
                                    switch (elem.field) {
                                        // some fields have exceptions
                                        case '#khk_enrollment':
                                            if (parseInt(info.khk_reg)) {
                                                $(elem.field).each( function() {
                                                    this.checked = true
                                                })
                                                $(elem.field).attr('disabled', true)
                                            }
                                            break
                                        case '.recruit':
                                            if (user.registrationStatus.khk === false) $("#chidonRecruitment").show()
                                            $(elem.field).each( function() {
                                                if (parseInt(info.recruited_by) && this.value == 1) $(this).trigger('click')
                                                else if (!parseInt(info.recruited_by) && this.value == 0) this.checked = true
                                            })
                                            break
                                        case '.book-bought':
                                            $(elem.field).each( function() {
                                                if (this.value == 1 && info.location && info.location.length) $(this).trigger('click')
                                                else if (this.value == 0 && (!info.location || (info.location && !info.location.length))) {
                                                    $(this).trigger('click')
                                                    $(".yahadus").each( function() {
                                                        if (this.value == 0) this.checked = true
                                                    })
                                                }
                                            })
                                            break
                                        case '#agreements input':
                                        case '#media':
                                            $(elem.field).each( function() {
                                                this.checked = true
                                            })
                                            break
                                        default:
                                            // for some inputs we need to trigger a click event
                                            $(elem.field).each( function() {
                                                if (info[elem.db] && this.value === info[elem.db]) {
                                                    if (elem.triggerEvent) $(this).trigger('click')
                                                    else this.checked = true
                                                }
                                            })
                                            break
                                    }
                                    break
                                case 'multi':
                                    if (info[elem.db]) {
                                        let values = info[elem.db].split(',') // coming from db it's a string
                                        $(elem.field).each(function () {
                                            if (values.includes(this.value)) this.checked = true
                                        })
                                    }
                                    break
                            }
                        }
                    }
                }
            }

            // ("#step-2 form select#yahadus-poll option").prop('selected', false);

            // reset name preference
            // $("#nameChoice").val('')

            // reset chidon limmud tracks
            // var limmud = $(".limmud")
            // for (var l of limmud) {
            //     l.checked = false
            // }
            //
            // if (!user['registrationStatus']['chidon']) {
            //     $("#chidonWhatsapp").show()
            //     $("#chidonLimmud").show()
            // } else {
            //     $("#chidonWhatsapp").hide()
            //     $("#chidonLimmud").hide()
            // }

            // $("#step-2 form #yarmulka-size").val(0)
            // $("#step-2 form #chidon-sweater-size").val(0)
            //
            // // reset the book field
            // $("#step-2 form #chidon-book").val(0);
            // // reset book bought info
            // $("#step-2 form input.book-bought").prop('checked', false);
            // $("#step-2 form #book-purchase").hide();
            // // yahadus
            // $( '#step-2 form #yahadus-registration input' )[0].checked = false;
            // // $( '#step-2 form #yahadus-registration-no input' )[0].checked = false;
            // $( '#step-2 form #yahadus-book-number' ).text( user.class_grade - 4 );
            // $( '#step-2 form #yahadus-registration').hide();
            // // $( '#step-2 form #yahadus-registration-no').hide();
            // $(".yahadus-myshliach").hide()
            // // $(".book-purchase-myshliach").hide()

            // $("#step-2 form input#shabbaton")[0].checked = false;
            // $("#step-2 form input.recruit")[0].checked = false;
            // $("#step-2 form input.recruit")[1].checked = false;
            // $("#school").empty();
            // $("#school").html("<option value='0'>Select School</option>");
            // $("#grade").empty();
            // $("#grade").html("<option value='0'>Select Grade</option>");
            // $("#user").empty();
            // $("#user").html("<option value='0'>Select Student</option>");
            // if ( user.school.shipping_method === 'pickup' ) {
            //     $( '#step-2 form #yahadus-cost' ).text( '$55' );
            //     $( '#step-2 form #yahadus-real-cost' ).text( 36 )
            //     $( '#step-2 form #yahadus-text').text( '' );
            // } else { 
            //     $( '#step-2 form #yahadus-cost' ).text( '$60' );
            //     $( '#step-2 form #yahadus-real-cost' ).text( 45 )
            //     $( '#step-2 form #yahadus-text').text( '. Price includes shipping cost.' );
            // }

            // if ( user.school.school_id == 269 ) {
            //     $("#book_no_school").show()
            //     $("#book_school_usa").hide()
            //     $("#book_school_not_usa").hide()
            // } else {
            //     $("#book_no_school").hide()
            //     // shipping to non USA schools is an additional $15
            //     var usa = [
            //         'U.S.A',
            //         'United States',
            //         'US',
            //         'USA'
            //     ]
            //     var country = user.school.school_country
            //     if (usa.includes(country)) {
            //         $("#book_school_usa").show()
            //         $("#book_school_not_usa").hide()
            //     } else {
            //         $("#book_school_usa").hide()
            //         $("#book_school_not_usa").show()
            //     }
            // }

            console.log( user );
        },
        toggleRates: function( user ) {
            ['chayolei', 'chidon'].forEach( function(type) {
                $("#" + type).checked = false;
                if (user.registrationStatus[type] === false) {
                    $("#" + type + "-registration").show()
                    if (type === 'chayolei') {
                        // setup chayolei fee dropdown
                        let htmlFee = '';
                        let rates = [100, 75, 60, 55, 50, 45, 40];
                        let rate = user.registrationRates[type]
                        for (let n of rates) {
                            if (n < user.registrationRates[type]) break;
                            htmlFee += "<option value=" + n + ">$" + n + "</option>";
                        }
                        $('#chayolei-fee').empty();
                        $('#chayolei-fee').append(htmlFee);
                        $('#chayolei-fee').val(user.registrationRates[type])
                    }
                }
            })
        },
        renderCheckout: function( cart ){
            console.log(cart)
            let total = cart.reduce( function( total, item ) { return parseInt(total) + parseInt(item.price) }, 0 );
            for (i = 0; i < cart.length; i++) {
                if (cart[i].meta.discount) total -= parseInt(cart[i].meta.discount)
            }
            if (total < 0) total = 0;
            $("#charges").html('');
            // add each item
            cart.forEach( function( item ){
                // only show when there's a charge
                if (item.price > 0) {
                    $("#charges").append('<div class="row">' +
                        '<div class="col-10">' + item.description + '</div>' +
                        '<div class="col-2 reg_cost">$' + item.price + '</div>'
                        + "</div>");
                    if (item.meta.discount) {
                        $("#charges").append('<div class="row">' +
                            '<div class="col-10">Discount</div>' +
                            '<div class="col-2 reg_cost">-$' + item.meta.discount + '</div>'
                            + "</div>");
                    }
                }
            });
            // add the total row
            var text = "Total Balance";
            if ( Cookies.get('lang') == 'he' || localStorage.getItem('locallang') == 'he' ) text = "איזון כולל";
            $("#charges").append( '<div class="row total-row">' +
                '<div class="col-9 col-md-10"><strong>' + text + '</strong></div>' +
                '<div class="col-3 col-md-2 reg_cost">$' + total + '</div>'
            + "</div>" );
            $("#total").val( total );
        },
        toggleNewCard: function( required ){
            if ( required ) $("#new-card").show();
            else $("#new-card").hide();

            $.each( $("#new-card input"), function( index, input ){
                input.required = required;
            });
        },
        renderPaymentOptions: function( payment_profiles ){
            var html = '';
            payment_profiles.forEach( function( payment, index ){
                var cc = payment.payment.creditCard;
                cc.cardType = cc.cardType || "Unknown";
                var msg = '<span>' + cc.cardType + ' ending in ' + cc.cardNumber.slice( 4 ) + '</span>';
                if (Cookies.get('lang') == 'he' || localStorage.getItem('locallang') == 'he') msg = '<span dir="rtl">כרטיס ' + cc.cardType + ' המסתיים בספרות ' + cc.cardNumber.slice( 4 ) + '</span>'
                html += 
                '<div class="payment-option cc-number identified ' + cc.cardType.toLowerCase() + '">' +
                    '<label class="radio-label">' +
                        '<input type="radio" id="payment_profile" name="payment_profile" value="' + 
                            payment.customerPaymentProfileId + '"' + 
                            ( index === 0 ? "checked" : "" ) + '/>' +
                        '<span class="radio"></span>' +
                    '</label>&nbsp;' + msg +
                '</div>';
            });
            var msg2 = '<span>New Card</span>';
            if (Cookies.get('lang') == 'he' || localStorage.getItem('locallang') == 'he') msg2 = "<span>כרטיס (אשראי) חדש</span>"
            html +=
            '<div class="payment-option">' + 
                '<label class="radio-label">' +
                    '<input type="radio" id="payment_profile" name="payment_profile" value=""/>' +
                    '<span class="radio"></span>' + 
                '</label>&nbsp;' + msg2 +
            '</div>';

            $("#card-on-file").html( html );
            $("input#payment_profile").change( function( event ){
                templates.toggleNewCard( !event.target.value );
            });
        }
    }
}();