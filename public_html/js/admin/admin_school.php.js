function setupAutofill() {// only run once page is loaded
    $("#copy_shipping_address").click(function(event) { // when the copy_shipping address button is clicked
        event.preventDefault(); // do not submit the form
        // for each of the 4 address feilds, set the value to be the same as the one in the shipping section.
        $("input[name='billing_address'").val(
            $("input[name='shipping_address1'").val()
        );
        $("input[name='billing_city'").val(
            $("input[name='shipping_city'").val()
        );
        $("input[name='billing_state'").val(
            $("input[name='shipping_state'").val()
        );
        $("input[name='billing_postal'").val(
            $("input[name='shipping_postal'").val()
        );
    });
    
     $("#copy_main_address").click(function(event) { // when the copy_shipping address button is clicked
        event.preventDefault(); // do not submit the form
        // for each of the 4 address feilds, set the value to be the same as the one in the shipping section.
        $("input[name='billing_address'").val(
            $("input[name='address1'").val()
        );
        $("input[name='billing_city'").val(
            $("input[name='city'").val()
        );
        $("input[name='billing_state'").val(
            $("input[name='state'").val()
        );
        $("input[name='billing_postal'").val(
            $("input[name='postal'").val()
        );
    });
     if(typeof cc_validate !== "undefined"){
        cc_validate.setUpModalValidaitons();
     }
}

$(document).ready(setupAutofill);