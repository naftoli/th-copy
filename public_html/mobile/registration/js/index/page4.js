var page4 = function(){

    function render( state ){
        $("#charges").html("");

        var total = 0;
        // add all the users
        state.selected_users.forEach( user => {
            $("#charges").append( '<div class="row">' +
                '<div class="col-10">' + user.first + " " + user.last + '</div>' +
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
            '<div class="col-10">Total</div>' +
            '<div class="col-2 reg_cost">$' + total + '</div>'
        + "</div>" );

        $("#total").val( total );
    }

    function submit( event ){
        event.preventDefault();
        // validate form 
        event.target.checkValidity();
        $( event.target ).addClass('was-validated');
        // show loading
        $("#payment-button").html('<i class="fas fa-circle-notch fa-spin fa-2x"></i>')
        // submit the payment info
        debugger;
        // update the button
        $("#payment-button").html('Pay And Register');
        $("#successModal").modal('show');
    }

    return {
        render: render,
        submit: submit
    }
}();