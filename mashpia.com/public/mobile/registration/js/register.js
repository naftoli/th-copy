/**
 * JS file for /mobile/registration/
 */
// page setup
if ( !checkDateInput() ) { $('#dob, input[type="date"]').datepicker({ format: "yyyy-mm-dd" }); }
$("#successModal").on('hidden.bs.modal', function( event ) { window.location = "/mobile/reg/parent_detail.html" } );
$('[data-toggle="popover"]').popover();
hebrew_keyboard.attach( "#first_he, #last_he" ); // use hebrew in the right places

var myshliach = 61;
var anash_kinder = 269;
var showClasses = 0; // global var to determine if we need to show link to myshliach online classes

// fees and shipping
var fees = {
    'regular': {
        'shabbaton': 185,
        'book': 40,
        'shipping': {
            'USA': 5,
            'Canada': 10,
        }
    },
    'special': {
        'book': 40,
        'shipping': 15
    },
    'anash': {
        'shabbaton': 250
    }
}

var registrationApp = function() {
    var api_url = '/api/registration/user_registration.php'; // API endpoint for this page
    var state = {
        users: [], // the users we are registering
        selected_users: [], // users selected in step-1
        cart: [], // items that the user is paying for
        shipping_type: 1 // 1 or 2
    }
    // var registration_year = 5779;
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
            if( users.length === 1 ) return step2();

            var html = '';
            state.users.forEach( function( user ) {
                html += templates.child( user );
            });
            $("#children").html( html );

            toggleLoading( "step-1", false );
        });
    }

    // show selected users
    function step2() {
        window.location.hash = 'step-2';
        state.selected_users = []; state.cart = [];
        // make sure that we have at least one user
        if ( state.users.length === 1 ) {
            state.selected_users.push( Object.assign(
                { confirmed: false }, state.users[0]
            ));
        } else if ( state.users.length === 0 || $('#step-1 #children input:checked').length === 0 ) {
            return showError( 'Please select at least one child' );
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
        var user_ids = state.selected_users.map( function( user ) { return user.user_id } );
        state.cart = state.cart.filter( function(item) { return user_ids.includes( item.meta.user_id ) } );
        // show the page
        templates.showUser( state.selected_users[0], 0 );
        showSection('step-2');

        var current_index = parseInt( $("#current_index").val() );
        var selected_user = state.selected_users[ current_index ];
        var school_id = selected_user.school.school_id;
        var australian = [ 55, 66, 110, 112, 180, 256 ]; 

        // show study guides info for all non Australian schools
        if ( !australian.includes( school_id ) && school_id != anash_kinder && school_id != myshliach ) $("#study-guides").show();

        // show anash kinder text if anash kinder school
        if ( school_id == anash_kinder ) {
            $("#anash_kinder_text").show();
        }

        // yahadus registration
        $('#step-2 form .book-bought').click( function() {
            $("#step-2 form .chidon-reg").show();
            if ( !$("#chidon").is(":checked") ) $("#chidon").trigger('click');
            if ( $(this).val() == 0 ) {
                if ( !australian.includes( school_id ) ) {
                    $( '#step-2 form #yahadus-registration').show();
                    $( '#step-2 form #yahadus-registration-no').show();
                }
                $('#step-2 form #book-purchase').hide();
            } else {
                $( '#step-2 form #yahadus-registration input' )[0].checked = false;
                $( '#step-2 form #yahadus-registration').hide();
                $( '#step-2 form #yahadus-registration-no input' )[0].checked = false;
                $( '#step-2 form #yahadus-registration-no').hide();
                $('#step-2 form #book-purchase').show();
            } 
        });

        $('#step-2 form .book-purchase').change( function() {
            var purchaseValue = $(this).val();
            if ( purchaseValue == 'store' ) $("#step-2 form #book-purchase-details").show();
            else $("#step-2 form #book-purchase-details").hide();
        });

        // make sure only one of the book purchase inputs are checked
        $("#yahadus").click( function() {
            if ( $(this).is(":checked") && $("#no_yahadus").is(":checked") ) $("#no_yahadus").trigger('click');
            if ( $(this).is(":checked") ) {
                // if myshliach / anash kinder need to confirm address
                if ( [ 269, 61 ].includes( selected_user.school.school_id ) ) {
                    $("#ship-address1").val( selected_user.parentAccount.admin_address1 );
                    $("#ship-address2").val( selected_user.parentAccount.admin_address2 );
                    $("#ship-city").val( selected_user.parentAccount.admin_city );
                    $("#ship-state").val( selected_user.parentAccount.admin_state);
                    $("#ship-zip").val( selected_user.parentAccount.admin_postal );
                    $("#ship-country").val( selected_user.parentAccount.admin_country );
                    $("#shipping-modal").modal('show');
                    $("#update-shipping").click( function() {
                        var info = {};
                        info.address1 = $("#ship-address1").val();
                        info.address2 = $("#ship-address2").val();
                        info.city = $("#ship-city").val();
                        info.state = $("#ship-state").val();
                        info.zip = $("#ship-zip").val();
                        info.country = $("#ship-country").val();
                        if ( !(info.address1 && info.city && info.state && info.zip && info.country) ) {
                            alert("The address, city, state, zip and country fields cannot be left blank!");
                            return false;
                        }
                        var current_user = selected_user; // needed for scope
                        $.post("updateAddress.php", { info: info }, function( res ) {
                            if ( res.success ) {
                                alert( res.data );
                                current_user.parentAccount.admin_country = info.country;
                                if ( current_user.parentAccount.admin_country.toUpperCase() == 'USA' ) $("#yahadus-shipping").html("There is an extra shipping charge of <b>$15.</b>");
                                else $("#yahadus-shipping").html("There is an extra shipping charge of <b>$30.</b><br />");
                                $("#shipping-modal").modal('hide');
                            } else {
                                alert( res.error );
                            }
                        });
                    });
                }
            }
        });
        $("#no_yahadus").click( function() {
            if ( $(this).is(":checked") && $("#yahadus").is(":checked") ) $("#yahadus").trigger('click');
        });

        $("input.recruit").click( function() {
            if ( $(this).val() == '1' ) {
                $("#recruited-by").show();
            } else {
                $("#recruited-by").hide();
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

        $("#school").change( function() {
            $.post('api/tasks/getGrades.php', { school_id: $(this).val() }, function( result ) {
                if ( result.success ) {
                    var grades = result.data;
                    var html = "<option value='0'>Select Grade</option>";
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

        $("#grade").change( function() {
            $.post('api/tasks/getStudents.php', { class_id: $(this).val() }, function( result ) {
                if ( result.success ) {
                    var users = result.data;
                    var html = "<option value='0'>Select Student</option>";
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
        
        var total = state.cart.reduce( function( total, item ) { return parseInt(total) + parseInt(item.price) }, 0 );
        if ( total === 0 ){
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
    function confirmUser( event ) {
        event.preventDefault();
        
        // update the user's information
        var postData = {};  var user_changed = false;
        var current_index = parseInt( $("#current_index").val() );
        var selected_user = state.selected_users[ current_index ];
        console.log( selected_user );
        // find all changed feilds
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

        // check if pic is generic or new pic needs to be uploaded
        if ( parseInt($("#needs_new_pic").val()) === 1 ) {
            return showError('You must upload a (new) photo.');
        }

        // Detect and validate the charges accepted.
        selected_charges = {
            chayolei: $('#chayolei-registration input')[0].checked,
            // chayolei_lite: $('#chayolei-lite-registration input')[0].checked,
            ckids: $('#ckids-registration input')[0].checked, 
            chidon: $('#chidon-registration input')[0].checked,
            yahadus: $('#yahadus-registration input')[0].checked
        }
        if ( selected_charges.chayolei === false 
            // && selected_charges.chayolei_lite === false 
            && selected_charges.ckids === false 
            && selected_charges.chidon === false 
            //&& selected_charges.yahadus === false
        ){
            return showError(
                'You must select at least one type of registration.'
            );
        }

        if ( selected_charges.chidon === true ) {
            // check that book was selected
            if ( $("select#chidon-book").val() == 0 ) {
                return showError("You must choose which book is being studied.");
            }

            // make sure sweater size is chosen
            if ( $("select#chidon-sweater-size").val() == '' ) {
                return showError("You must choose a sweater size.");
            }

            if ( !$(".book-bought").is(":checked") ) {
                return showError("You must indicate if you have already purchased a book or not.");
            }
            
            if ( $(".book-bought:checked").val() == '1' ) {
                // make sure something is checked
                if ( !$(".book-purchase").is(":checked") ) {
                    return showError("You have not selected where you bought the book.");
                }
                // make sure that if they bought a book from a store, that the store info is filled out
                console.log( $(".book-purchase:checked").val() );
                if ( $(".book-purchase:checked").val() == 'store' && ($("#store-name").val().trim() == '' || $("#store-city").val().trim() == '' ) ) {
                    return showError("You must enter the store information for your book purchase.");
                }
            } else {
                // make sure they checked either that they want to purchase a book or that they already have a book
                if ( !$("#yahadus").is(":checked") && !$("#no_yahadus").is(":checked") ) {
                    return showError("You must indicate whether you would like to purchase a book or not.");
                }
            }

            var poll = $("#yahadus-poll").val();
            if ( !poll.length ) {
                return showError("You must indicate how you will be learning for chidon.");
            }

            // make sure shabbaton button was checked as well
            if ( !$("#shabbaton").is(":checked") ) {
                return showError("You must indicate your acknowledgment of the Shabbaton fee.");
            }

            // make sure we have student id if recruited by is checked off
            if ( $(".recruit").eq(0).is(":checked") ) {
                if ( parseInt( $("#user").val() ) == 0 ) {
                    return showError("You must choose who recruited you.");
                }
            }
        } else if ( selected_charges.chayolei === true || selected_charges.chayolei_lite === true ) {
            // make sure non th school field is not empty
            if ( [ 269, 61 ].includes( selected_user.school.school_id ) ) {
                var non_th_school = $("#non_th_school").val().trim();
                if ( non_th_school.length < 3 ) return showError("You must enter the name of the school that you are attending.");
            }
            // make sure they chose an amount to pay
            if (!parseInt( $("select#chayolei-fee").val() )) {
                return showError("You must choose an amount to pay for chayolei tzivos hashem.");
            }
        }  

        // validate that they have accepted to be used in media campaigns
        if ( $(event.target).find( "#media" )[0].checked ){
            $(event.target).find( "#media" )[0].checked = false;
        } else {
            return showError( "You must indicate your acceptance of participation in Tzivos Hashem Media." )
        }
        // remove the user if we had him before...
        state.cart = state.cart.filter( function(item) { return item.meta.user_id != selected_user.user_id } );
        // and re-add him to the cart
        if ( selected_charges.chayolei ) {
            selected_user.registrationRates.chayolei = parseInt( $("select#chayolei-fee").val() );
            if (isNaN(selected_user.registrationRates.chayolei)) {
                return showError('You have not chosen how much you will be paying for Chayolei Tzivos Hashem Registration');
            }
            state.cart.push({
                description: 'Tzivos Hashem Registration for ' + selected_user.first,
                price: selected_user.registrationRates.chayolei,
                meta: {
                    type: 'registration',
                    user_id: selected_user.user_id,
                    registration_type: 'chayolei',
                    paid: selected_user.registrationRates.chayolei
                }
            });
        } 
        if ( selected_charges.chayolei_lite ) {
            selected_user.registrationRates.chayolei = 0;
            state.cart.push({
                description: 'Tzivos Hashem Registration for ' + selected_user.first,
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
                description: 'CKids Registration for ' + selected_user.first,
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
            if ( [ 269, 61 ].includes( selected_user.school.school_id ) ) showClasses = 1; 
            state.cart.push({
                description: 'Chidon Registration ' + selected_user.first + ( anash ? ' (includes coordinator and study guide)' : ''),
                price: selected_user.registrationRates.chidon,
                meta: {
                    type: 'registration',
                    user_id: selected_user.user_id,
                    registration_type: 'chidon',
                    paid: selected_user.registrationRates.chidon,
                    size: $("select#chidon-sweater-size").val(), 
                    book: $("select#chidon-book").val(), 
                    purchased: $(".book-bought:checked").val(),
                    purchasedWhere: $(".book-purchase:checked").val(), 
                    store: {
                        store_name: $("#store-name").val(), 
                        store_city: $("#store-city").val()
                    }, 
                    recruited: $(".recruit:checked").val(), 
                    recruitedBy: $("#user").val(), 
                    poll: poll 
                }
            });
        }
        if ( selected_charges.yahadus ) {
            var shipping_included = selected_user.school.shipping_method !== 'pickup';
            let d1 = new Date()
            let d2 = new Date(2020, 08, 10)
            if ( d1 < d2 && selected_user.parentAccount.admin_country.toUpperCase() === 'USA' ) shipping_included = false; // for usa shipping is free if before sept 10, 2020
            let shipping_charge = fees.regular.shipping.USA;
            if ( selected_user.parentAccount.admin_country.toUpperCase() !== 'USA' ) shipping_charge = fees.regular.shipping.Canada
            if ( [ 269, 61 ].includes( selected_user.school.school_id ) ) {
                shipping_included = true; // override for anash kinder to make sure shipping is being charged
                // if ( selected_user.parentAccount.admin_country.toUpperCase() == 'USA' ) shipping_charge = fees.special.shipping;
                shipping_charge = fees.special.shipping;
            }
            // // don't add to cart if anash kinder / myshliach and not in USA
            // if ( 
            //     ! [ 269, 61 ].includes( selected_user.school.school_id ) || 
            //     ( [ 269, 61 ].includes( selected_user.school.school_id ) && selected_user.parentAccount.admin_country.toUpperCase() == 'USA' ) 
            // ) {
                state.cart.push({
                    description: 'Yahadus Book for ' + selected_user.first + ( shipping_included ? ' (Shipping Included)' : '' ),
                    price: shipping_included ? (40 + shipping_charge) : 40,
                    meta: {
                        type: 'registration',
                        user_id: selected_user.user_id,
                        registration_type: 'yahadus',
                        paid: shipping_included ? (40 + shipping_charge) : 40
                    }
                });
            //}
        }

        current_index += 1;
        if ( state.selected_users.length <= current_index ){
            step3();
        } else {
            templates.showUser( state.selected_users[ current_index ], current_index );
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
                    if ( user.registrationStatus.chayolei === false || user.registrationStatus.chidon === false )
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
                    showError( "There was an error while updating your child. Please try again later." );
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
        }).then( function() { 
            if ( showClasses ) {
                $("#successModal #success").append("<p>To Register for MyShliach's online weekly classes please click <a href='https://merkos302.formstack.com/forms/chidon_shiurim_registration'>here</a></p>");
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
        $("#needs_new_pic").val(0);
        
        $.post("/api/core/users?id=" + user_id, { mobile_pic: data.filename }, function( response ){
            if ( !response.success ){
                showError( "Could not update Profile Picture. We will try again when pressing 'Confirm'.");
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
                        '<p class="name">' + child.first + " " + child.last + '</p>' +
                        ( child.registrationStatus.chayolei === false ? 
                            child.school.inst_id === 10 ? ( '<p class="reg_cost">CKids Registration: $0</p>' ) : 
                            ( '<p class="reg_cost">Tzivos Hashem: $' + child.registrationRates.chayolei ) : '' ) +
                            // ( '<p class="reg_cost">Tzivos Hashem: $' + child.registrationRates.chayolei + '<br />($0 Free Lite Edition)</p>' ) : '' ) +
                        ( child.registrationStatus.chidon === false ? 
                            ( '<p class="reg_cost">Chidon: $' + child.registrationRates.chidon + '</p>' ) : '' ) +
                    '</div><div class="col-2">' +
                        '<input type="checkbox" data-user_id="' + child.user_id + '" />' +
                        '<span class="checkbox"></span>' +
                    '</div>' +
                '</div>' +
            '</label></div>';
        },
        showUser: function( user, index ){
            if( index > 0 ) window.location.hash = 'step-2-' + index;
            $( '#step-2 form #user_id' ).val( user.user_id );
            $( '#step-2 form #needs_new_pic' ).val( user.newPic );
            $( '#step-2 form #mobile_pic' ).val( user.mobile_pic );
            $( '#step-2 form #mobile_pic + img' ).attr( 'src', user.profilePicture );
            $( '#step-2 form .gender[value=\'' + user.gender + '\']')[0].checked = true;
            $( '#step-2 form #school_name' ).val( user.school.school_name );
            $( '#step-2 form #lang_id' ).val( user.lang_id );
            $( '#step-2 form #non_th_school' ).val( user.non_th_school );
            // add the dropdown for naftali
            var class_select = $( '#step-2 form #class_name select' );
            class_select.html('');
            if ( [ 269, 61 ].includes( user.school.school_id ) ) {
                // get the class list and update the dropdown
                $.get( "api/classes.php", { 'school_id': user.school.school_id }, function( response ) {
                    class_select.html('');
                    response.data.forEach( function( option ) {
                        class_select.append("<option value='" + option.id + "'>" + option.name + "</option>");
                    });
                    class_select.val( user.class_id );
                    $( '#step-2 form #class_name' ).show(); // make sure it is visiable
                });
            } else {
                $( '#step-2 form #class_name' ).hide(); // hide it
                $( '#step-2 form #non_th_school' ).hide();
            }
            // setup the index state
            $( '#step-2 form #current_index' ).val( index );
            // fill out the input feilds
            $( '#step-2 form #first' ).val( user.first );       $( '#step-2 form #last' ).val( user.last );
            $( '#step-2 form #first_he' ).val( user.first_he ); $( '#step-2 form #last_he' ).val( user.last_he );
            $( '#step-2 form #lang_id' ).val( user.lang_id );   $( '#step-2 form #dob' ).val( user.dob );
            // if ( [ 269, 61 ].includes( user.school.school_id ) ) {
            //     if ( user.parentAccount.admin_country.toUpperCase() == 'USA' ) $("#yahadus-shipping").html("There is an extra shipping charge of <b>$15.</b>");
            //     else $("#yahadus-shipping").html("There is an extra shipping charge of <b>$30.</b><br />");
            // }

            $("#step-2 form select#yahadus-poll option").prop('selected', false);

            $("#step-2 form .chidon-reg").hide();

            // reset chayolei lite reg
            // $( '#step-2 form #chayolei-lite-registration input' )[0].checked = false;

            // reset ckids reg
            $( '#step-2 form #ckids-registration input' )[0].checked = false;
            // hide ckids unless its a ckids child
            if (user.school_inst_id === 10) {
                $('#step-2 form #chayolei-registration').hide();
                // $('#step-2 form #chayolei-lite-registration').hide();
                $('#step-2 form #ckids-registration').show();
                $("#step-2 form #broadcast").hide();
            } else {
                $('#step-2 form #chayolei-registration').show();
                // $('#step-2 form #chayolei-lite-registration').show();
                $('#step-2 form #ckids-registration').hide();
                $("#step-2 form #broadcast").show();
            }

            // setup the payment options - chayolei
            templates.toggleRates( user, 'chayolei' );
            templates.toggleRates( user, 'chidon' );
            
            // reset the book field
            $("#step-2 form #chidon-book").val(0);
            // reset book bought info
            $("#step-2 form input.book-bought").prop('checked', false);
            $("#step-2 form #book-purchase").hide();
            // yahadus
            $( '#step-2 form #yahadus-registration input' )[0].checked = false;
            $( '#step-2 form #yahadus-registration-no input' )[0].checked = false;
            // $( '#step-2 form #yahadus-book-number' ).text( user.class_grade - 4 );
            // $( '#step-2 form #yahadus-cost' ).text(  ? 45 : 50 );
            $( '#step-2 form #yahadus-registration').hide();
            $( '#step-2 form #yahadus-registration-no').hide();
            $("#step-2 form input#shabbaton")[0].checked = false;
            $("#step-2 form input.recruit")[0].checked = false;
            $("#step-2 form input.recruit")[1].checked = false;
            $("#step-2 form #recruited-by").hide();
            $("#school").empty();
            $("#school").html("<option value='0'>Select School</option>");
            $("#grade").empty();
            $("#grade").html("<option value='0'>Select Grade</option>");
            $("#user").empty();
            $("#user").html("<option value='0'>Select Student</option>");
            // if ( user.school.shipping_method === 'pickup' ) {
            //     $( '#step-2 form #yahadus-cost' ).text( '$55' );
            //     $( '#step-2 form #yahadus-real-cost' ).text( 40 )
            //     $( '#step-2 form #yahadus-text').text( '' );
            // } else { 
            //     $( '#step-2 form #yahadus-cost' ).text( '$60' );
            //     $( '#step-2 form #yahadus-real-cost' ).text( 45 )
            //     $( '#step-2 form #yahadus-text').text( '. Price includes shipping cost.' );
            // }
            // reset sweater
            $("#chidon-sweater-size").val('')
            // reset shipping
            $("#book-info").empty()
            // build html for book fees and shipping fees
            let html
            if ( [ 269, 61 ].includes( user.school.school_id ) ) {
                $("#non-th-school").show()
                if ( user.school.school_id == 269 ) $("#shabbaton-cost").text( '$' + fees.anash.shabbaton )
                else $("#shabbaton-cost").text( '$' + fees.regular.shabbaton )
                html = `
                    Book Sale: Book price is normally $55, but for Chidon Members it is <b>ON SALE for $${fees.special.book}</b> (limited time only!).<br />
                    Shipping:<br />
                    <blockquote>USA: $${fees.special.shipping}.</blockquote>
                `
            } else {
                $("#non-th-school").hide()
                $("#shabbaton-cost").text( '$' + fees.regular.shabbaton )
                html = `
                    Book Sale: Book price is normally $55, but for Chidon Members it is <b>ON SALE for $${fees.regular.book}</b> (limited time only!).<br />
                    Shipping:<br />
                    <blockquote>
                        USA: Free shipping to your school till Chof Elul (September 9). After that there is a $${fees.regular.shipping.USA} charge.<br />
                        Canada: $${fees.regular.shipping.Canada} flat fee charge.
                    </blockquote>
                `
            }
            $("#book-info").append( html )
            console.log( user );
        },
        toggleRates: function( user, rateType ){
            $( '#step-2 form #' + rateType + '-registration input' )[0].checked = false;
            if( user.registrationStatus[ rateType ] === false ){
                $( '#step-2 form #' + rateType + '-registration' ).show(); 
                $( '#step-2 form #' + rateType + '-cost' ).text( user.registrationRates[ rateType ] );
                if ( rateType === 'chayolei') {
                    // setup chayolei fee dropdown
                    var htmlFee = '';
                    if ( user.registrationRates[ rateType ] == 0 ) {
                        // just make dropdown show 0
                        htmlFee += "<option value='0'>0</option>";
                    } else {
                        htmlFee += "<option value=''>Please Choose</option>";
                        var rates = [ 100, 75, 60, 55, 50, 45, 40 ];
                        if ( user.registrationRates[ rateType ] < rates[ rates.length - 1 ] ) rates.push( user.registrationRates[ rateType ] );
                        for ( var n of rates ) {
                            if ( n < user.registrationRates[ rateType ] ) break;
                            htmlFee += "<option value=" + n + ">$" + n + "</option>";
                        }
                    }
                    $( '#step-2 form #chayolei-fee' ).empty();
                    $( '#step-2 form #chayolei-fee' ).append( htmlFee );
                }
            } else {
                console.log( $('#step-2 form #' + rateType + '-registration') )
                $('#step-2 form #' + rateType + '-registration').hide();
            }
        },
        renderCheckout: function( cart ){
            var total = cart.reduce( function( total, item ) { return parseInt(total) + parseInt(item.price) }, 0 );
            $("#charges").html('');
            // add each item
            cart.forEach( function( item ){
                $("#charges").append( '<div class="row">' +
                    '<div class="col-10">' + item.description + '</div>' +
                    '<div class="col-2 reg_cost">$' + item.price + '</div>'
                + "</div>" );
            });
            // add the total row
            $("#charges").append( '<div class="row total-row">' +
                '<div class="col-9 col-md-10"><strong>Total Balance:</strong></div>' +
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
                html += 
                '<div class="payment-option cc-number identified ' + cc.cardType.toLowerCase() + '">' +
                    '<label class="radio-label">' +
                        '<input type="radio" id="payment_profile" name="payment_profile" value="' + 
                            payment.customerPaymentProfileId + '"' + 
                            ( index === 0 ? "checked" : "" ) + '/>' +
                        '<span class="radio"></span>' +
                    '</label>&nbsp;' +
                    '<span>' + cc.cardType + ' ending in ' + cc.cardNumber.slice( 4 ) + '</span>' +
                '</div>';
            });
            html +=
            '<div class="payment-option">' + 
                '<label class="radio-label">' + 
                    '<input type="radio" id="payment_profile" name="payment_profile" value=""/>' +
                    '<span class="radio"></span>' + 
                '</label>&nbsp;' + 
                '<span>New Card</span>' +
            '</div>';

            $("#card-on-file").html( html );
            $("input#payment_profile").change( function( event ){
                templates.toggleNewCard( !event.target.value );
            });
        }
    }
}();