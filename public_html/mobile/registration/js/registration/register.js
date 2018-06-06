// Array.prototype.find polyfill
Array.prototype.find=Array.prototype.find||function(r){if(null===this)throw new TypeError("Array.prototype.find called on null or undefined");if("function"!=typeof r)throw new TypeError("callback must be a function");for(var n=Object(this),t=n.length>>>0,o=arguments[1],e=0;e<t;e++){var f=n[e];if(r.call(o,f,e,n))return f}};
/**
 * JS file for /mobile/registration/
 */
// page setup
if ( !checkDateInput() ) { $('#dob, input[type="date"]').datepicker({ format: "yyyy-mm-dd" }); }
$("#successModal").on('hidden.bs.modal', function( event ) { window.location = "/mobile/reg/parent_detail.html" } );
$('[data-toggle="popover"]').popover();
hebrew_keyboard.attach( "#first_he, #last_he" ); // use hebrew in the right places

var registrationApp = function() {
    var api_url = '/api/registration/user_registration.php'; // API endpoint for this page
    var state = {
        users: [], // the users we are registering
        selected_users: [], // users selected in step-1
        cart: [], // items that the user is paying for
        shipping_type: 1 // 1 or 2
    }
    var registration_year = 5779;
    // navigation buttons
    $(".start-step-1").click( step1 );
    $(".start-step-2").click( step2 );

    // form handlers
    $("#step-2 form").submit( confirmUser );
    $("#step-3 form").submit( confirmShipping );
    $("#step-4 form").submit( registerUsers );
    $("#step-4 #cc-number").keyup( validateCardInput );
    image_upload( {}, onImageUploaded );
    // run the first step
    step1();

    /*********************** CODE TO GUIDE USER THROUGH STEPS ***********************/
    // select users
    function step1() {
        showSection( "step-1" );
        toggleLoading( "step-1", true );
        state.users = [];

        getUsers().then( function( users ){ 
            if( users.length === 1 ) return step2();

            var html = '';
            state.users.forEach( function( user ) {
                html += templates.child( user );
            });
            $("#children").html( html );

            toggleLoading( "step-1", false );
        });
    }

    // confirm users
    function step2() {
        state.selected_users = [];
        // make sure that we have at least one user
        if ( state.users.length > 1 && $('#step-1 #children input:checked').length === 0 ) {
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
        // show the page
        templates.showUser( state.selected_users[0], 0 );
        showSection('step-2');
    }

    function step3() {
        toggleLoading( 'step-3', true );
        showSection('step-3');

        state.shipping_type = 1;
        getShipping(
            state.selected_users.map( function( user ) { return user.school.school_id } )
        ).then( function( response ){
            if( !response ) return step4(); // skip straight to step 4
            response.forEach( function( rate ) { 
                $("#shipping-type-" + rate.type).text("$" + rate.rate) 
            });
            toggleLoading( 'step-3', false );
        });
    }
    
    function step4() {
        showSection("step-4");
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
        $( event.target ).serializeArray().forEach( function( item ) {
            if ( selected_user[ item.name ] !== undefined && 
                selected_user[ item.name ] != item.value 
            ) {
                user_changed = true;
                postData[ item.name ] = item.value;
            }
        });
        // if the user changed update him in the background...
        if( user_changed ){
            updateUser( selected_user.user_id, postData );
        }
        // Detect and validate the charges accepted.
        selected_charges = {
            chayolei: $('#chayolei-registration input')[0].checked,
            chidon: $('#chidon-registration input')[0].checked
        }
        if ( selected_charges.chayolei === false && selected_charges.chidon === false ){
            return showError(
                'You must indicate your acceptance of at least one of the registration charges.'
            );
        } 
        if ( selected_charges.chayolei ) {
            state.cart.push({
                description: 'Tzivos Hashem '+registration_year+' Registration for ' + selected_user.first,
                price: selected_user.registrationRates.chayolei,
                meta: {
                    type: 'registration',
                    user_id: selected_user.user_id,
                    registration_type: 'chayolei'
                }
            });
        } 
        if ( selected_charges.chidon ) {
            state.cart.push({
                description: 'Chidon '+registration_year+' Registration and Book for ' + selected_user.first,
                price: selected_user.registrationRates.chidon,
                meta: {
                    type: 'registration',
                    user_id: selected_user.user_id,
                    registration_type: 'chidon'
                }
            });
        }
        
        // validate that they have accepted to be used in media campaigns
        if ( $(event.target).find( "#media" )[0].checked ){
            $(event.target).find( "#media" )[0].checked = false;
        } else {
            return showError( "You must indicate your acceptance of participation in Tzivos Hashem Media." )
        }

        current_index += 1;
        if ( state.selected_users.length <= current_index ){
            step3();
        } else {
            templates.showUser( state.selected_users[ current_index ], current_index );
        }
    }

    function confirmShipping( event ){
        event.preventDefault();

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

    function registerUsers( event ){
        event.preventDefault();
        var postData = {};
        // show loading
        $("#payment-button").html('<i class="fas fa-circle-notch fa-spin fa-2x"></i>');
        postData.payments = formToJSON( event.target );
        // sanitize input
        postData.payments["cc-number"] = postData.payments["cc-number"].replace(/ /g, '');
        postData.payments["cc-exp"] = postData.payments["cc-exp"].replace(/ /g, '');
        postData.payments["x_card_code"] = postData.payments["x_card_code"].replace(/ /g, '');
        // validate form 
        if ( postData.payments["cc-number"] ) {
            event.target.checkValidity();
            $( event.target ).addClass('was-validated');
        }
        postData.registrations = state.cart;
        registerUsers( postData ).then( function( response ){
            console.log( state );
            debugger;
        })
    }

    /*********************** HELPER FUNCTIONS ***********************/
    // navigate to a specific section
    function showSection( id ){
        $( "#pages section" ).hide();
        $( "#pages section#" + id ).show();
    }
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
                response.users.forEach( function( user ) {
                    user.dob = user.dob.split("T")[0];
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
            $.post("api/users.php?user_id=" + user_id, postData, function( response ) {
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
            APIRequest( 'GET', api_url + '?action=registerUsers', postData, resolve);
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
        
        $.post("api/users.php?user_id=" + user_id, { mobile_pic: data.filename }, function( response ){
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
                            ( '<p class="reg_cost">Tzivos Hashem: $' + child.registrationRates.chayolei + '</p>' ) : '' ) +
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
            $( '#step-2 form #user_id' ).val( user.user_id );
            $( '#step-2 form #mobile_pic' ).val( user.mobile_pic );
            $( '#step-2 form #mobile_pic + img' ).attr( 'src', user.profilePicture );
            $( '#step-2 form #gender[value=\'' + user.gender + '\']')[0].checked = true;
            $( '#step-2 form #school_name' ).val( user.school.school_name );
            $( '#step-2 form #class_name' ).val( user.platton.class_grade + ' ' + user.platton.class_sub );
            // setup the index state
            $( '#step-2 form #current_index' ).val( index );
            // fill out the input feilds
            $( '#step-2 form #first' ).val( user.first );       $( '#step-2 form #last' ).val( user.last );
            $( '#step-2 form #first_he' ).val( user.first_he ); $( '#step-2 form #last_he' ).val( user.last_he );
            $( '#step-2 form #lang_id' ).val( user.lang_id );   $( '#step-2 form #dob' ).val( user.dob );
            // setup the payment options - chayolei
            templates.toggleRates( user, 'chayolei' );
            templates.toggleRates( user, 'chidon' );
        },
        toggleRates: function( user, rateType ){
            $( '#step-2 form #' + rateType + '-registration input' )[0].checked = false;
            if( user.registrationStatus[ rateType ] === false ){
                $( '#step-2 form #' + rateType + '-registration' ).show(); 
                $( '#step-2 form #' + rateType + '-cost' ).text( user.registrationRates[ rateType ] );
            } else {
                $( '#step-2 form #' + rateType + '-registration').hide();
            }
        },
        renderCheckout: function( cart ){
            var total = cart.reduce( function( total, item ) { return total + item.price }, 0 );
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