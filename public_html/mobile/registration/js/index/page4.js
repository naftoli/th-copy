var page4 = function(){

    $("#test").click( function() {
        $("#cc-number").val( "4111 1111 1111 1111" );
        $("#cc-exp").val("12 / 15");
        $("#x_card_code").val("535");
    })

    function render( state ){
        $("#charges").html("");

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

    return {
        render: render
    }
}();