// wrapper for all the cc modal functions

var cc_modal = function() {
    
    var show = function(){
        // attempts to pull data from hidden input fields and set them to the fields in the modal
        if($("#authorize_bill_to").val() !== "" && $("#authorize_bill_to").val() !== undefined){
            var billTo = JSON.parse($("#authorize_bill_to").val());
            $("input[name='cc_number']").val($("#authorize_cc_num").val());
            $("input[name='cc_exp']").val($("#authorize_cc_exp").val());
            if (billTo){
                $("input[name='billing_address']").val(billTo.address);
                $("input[name='billing_state']").val(billTo.state);
                $("input[name='billing_city']").val(billTo.city);
                $("input[name='billing_postal']").val(billTo.zip);
            }
        }
        $("#cc_modal").css({"visibility": "visible"});
        $("#cc_modal").css({"opacity": "1"});
    };
    
    var hide = function() {
        cc_modal.setError("");
        $("#cc_modal").css({"opacity": "0"});
        setTimeout(function() {
            $("#cc_modal").css({"visibility": "hidden"});
        }, 100);
        
    };
    
    var setError = function(error){
        $("#cc_modal_error").html(error); // set the error to the correct div
    };
    
    var submit = function (event) {
        event.preventDefault();
        // the id's of the error feilds to check
        var field_ids = ["cc_number", "cc_exp", "cc_cvv", "billing_address", "billing_postal"];
        // for each one of the ids
        for (var i = 0; i < field_ids.length; i++){
            if ($("#"+field_ids[i]+"_errors").text() !== ""){ // check if there is some text (an error)
                setError("Please fix all the errors below");
                return; // quit the loop
            }
        }
        var form = $(this); // get the form that was submitted
        var url = event.target.action;
        var info = {customer_profile_id: $("#authorize_customer_profile_id").val(), payment_profile_id: $("#authorize_payment_profile_id").val()};
        var data = $.param(info) + "&" + form.serialize(); // merge the data and serialize it
        // Make the post request
        $.ajax({
            type: "POST", // send a post request
            url: url, // to the url
            data: data, // with the serialized data
            success: function(data) { // if we get some data back
                data = JSON.parse(data); // attempt to parse the json
                if(data.success){ // if it is good
                    hide(); // hide the modal
                    //preform_authorize_validation(); // charge the new card info // Function not available
                } else {
                    setError(data.response); // show the error
                }
            },
            error: function(xhr) { // if the request failed
                setError("Error (" + xhr.status + "): " + xhr.responseText); // show that to the user
            }
        });
    };
    // public variables returned after the encapslated function call
    return {
        show: show,
        hide: hide,
        submit: submit,
        setError: setError
    }
}();