var page4 = function(){

    function render( state ){
        $("#charges").html("");
        
        loadPaymentOptions();

        var total = 0;
        // add all the users
        state.selected_users.forEach( user => {
            $("#charges").append( '<div class="row">' +
                '<div class="col-10">' + user.first + " " + user.last + ' Registration</div>' +
                '<div class="col-2 reg_cost">$' + user.registration_fee + '</div>'
            + "</div>" );
            total += user.registration_fee;
        });
        if ( state.shipping_charges > 0 ){
            // add the shipping information
            $("#charges").append( '<div class="row">' +
            '<div class="col-10">Prepaid Shipping</div>' +
            '<div class="col-2 reg_cost">$' + state.shipping_charges + '</div>'
            + "</div>" );
            total += state.shipping_charges;
        }

        $("#charges").append( '<div class="row total-row">' +
            '<div class="col-10"><strong>Total</strong></div>' +
            '<div class="col-2 reg_cost">$' + total + '</div>'
        + "</div>" );

        $("#total").val( total );
    }

    function validateCardInput( event ) {
        debugger;
    }

    function updateNewCard( event ){
        toggleNewCard( !event.target.value );
    }

    function toggleNewCard( requried ){
        if ( requried ) {
            $("#new-card").show();
        } else {
            $("#new-card").hide();
        }

        $.each( $("#new-card input"), function( index, input ){
            input.required = requried;
        });
    }

    function loadPaymentOptions(){
        $("#card-on-file").html( "" );
        $("#payment .content").hide();
        $("#payment .spinner").show();

        $.get( "api/payment-profiles.php", function( response ){
            if ( !response.success ) return showError( response.error );
            if ( !response.data || response.data.length === 0 ) {
                toggleNewCard( true );
                $("#card-on-file").hide();
            } else {
                $("#card-on-file").show();
                toggleNewCard( false );

                response.data.forEach( function( payment, index ) {
                    var cc = payment.payment.creditCard;
                    var html = '<div class="payment-option cc-number identified ' + cc.cardType.toLowerCase() + '">'
                        html += '<label class="radio-label">';
                        html +=     '<input type="radio" id="payment_profile" name="payment_profile" value="' + 
                                        payment.customerPaymentProfileId + '"' + 
                                        ( index === 0 ? "checked" : "" ) + '/>';
                        html +=     '<span class="radio"></span>';
                        html += '</label>&nbsp;';
                        html += '<span>' + cc.cardType + ' ending in ' + cc.cardNumber.slice( 4 ) + '</span>';
                        html += '</div>';
                    
                    $("#card-on-file").append( html );
                });

                var html = '<div class="payment-option">'
                    html += '<label class="radio-label">';
                    html +=     '<input type="radio" id="payment_profile" name="payment_profile" value=""/>';
                    html +=     '<span class="radio"></span>';
                    html += '</label>&nbsp;';
                    html += '<span>New Card</span>'
                    html += '</div>';

                $("#card-on-file").append( html );

                $("input#payment_profile").change( updateNewCard );
            }

            $("#payment .content").show();
            $("#payment .spinner").hide();
        });
    }

    return {
        render: render,
        validateCardInput: validateCardInput
    }
}();