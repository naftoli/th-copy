/************** UNIT TESTS FOR CC_MODAL OBJECT ************************
*       
*       Runs on /tests/QUnit/admin_register_students.html.
*       Tests js used in /admin_register_students.php
*
*       Please note that these tests take a long time to run. average of 20ms each on dual core i7.
*
*       Depends on jQuery and /js/admin/components/cc_modal.js
*
*       Written by Menachem Hornbacher on 9/28/2017
*
***********************************************************************/

QUnit.module( "cc_modal" );

QUnit.test( "cc_modal.show test", function( assert ) {
    // some small set up
    $("#authorize_bill_to").val(JSON.stringify({
        address: "770 Eastern Parkway", city: "Brooklyn", state: "New York", zip: "11213"
    }));
    
    //document.getElementById("authorize_cc_num").value = "XXXX5678";
    //document.getElementById("authorize_cc_num").value = "XXXX5678";
    $("#authorize_cc_num").val("XXXX5678");
    $("#authorize_cc_exp").val("XXXX");
    
    cc_modal.show(); // run the subject function

    assert.equal( $("#qunit-fixture #cc_modal").css("opacity"), "1", "sets #cc_modal opacity to '1' for fade effect");
    assert.equal( $("#qunit-fixture #cc_modal").css("visibility"), "visible", "sets #cc_modal visibility to 'visible' to display full screen modal");
    
    // test that it sets the correct data in the modal
    assert.equal( $("#qunit-fixture input[name='cc_number']").val(), "XXXX5678", "sets input[name='cc_number'] with the value of input#authorize_cc_num");
    assert.equal( $("#qunit-fixture input#cc_exp").val(), "XXXX", "sets input#cc_exp with the value of input#authorize_cc_exp");
    assert.equal( $("#qunit-fixture input#billing_address").val(), "770 Eastern Parkway", "sets input#billing_address with the .address value of input#authorize_bill_to encoded in json");
    assert.equal( $("#qunit-fixture input#billing_city").val(), "Brooklyn", "sets input#billing_city with the .city value of input#authorize_bill_to encoded in json");
    assert.equal( $("#qunit-fixture input#billing_state").val(), "New York", "sets input#billing_state with the .state value of input#authorize_bill_to encoded in json");
    assert.equal( $("#qunit-fixture input#billing_postal").val(), "11213", "sets input#billing_postal with the .zip value of input#authorize_bill_to encoded in json");
});

QUnit.test( "cc_modal.hide test", function( assert ) {
    var done = assert.async();
    cc_modal.hide(); // call the subject
    
    assert.equal($("#qunit-fixture #cc_modal_error").text(), "", "clears the error message in #cc_modal_error");
    assert.equal( $("#qunit-fixture #cc_modal").css("opacity"), "0", "sets #cc_modal opacity to '0' for fade effect");
    
    setTimeout(function() {
        assert.equal( $("#qunit-fixture #cc_modal").css("visibility"), "hidden", "sets #cc_modal visibility to 'hidden' after 100ms (.1 seconds)");
        done();
    }, 100);
    
});

QUnit.test( "cc_modal.setError test", function( assert ) {
    var error_box = $("#qunit-fixture #cc_modal_error");

    cc_modal.setError("message");
    assert.equal(error_box.text(), "message", "It sets the error message in #cc_modal_error");
    
});

/* Submit Event Mock object */
// Mock out the event that would be passed in
var event = {
    preventDefault: function(){event.defaultPrevented = true;},
    target: {action: "/update_cc.php"}
};

QUnit.test( "cc_modal.submit => success test", function( assert ) {
    // set up async
    var done = assert.async();
    // Set up AJAX mocked responses
    var mockjax_id = $.mockjax({
        url: "/update_cc.php",  responseTime: 0,
        responseText: JSON.stringify({success: true,response: "Are you a mock turtle?"})      
    });
    // show the modal
    cc_modal.show();
    // run the submit function
    cc_modal.submit(event);
    // assert that the error message is clear
    setTimeout(function(){
        assert.equal($("#qunit-fixture #cc_modal_error").text(), "", "clears the error message in #cc_modal_error");
        assert.equal( $("#qunit-fixture #cc_modal").css("opacity"), "0", "sets #cc_modal opacity to '0' for fade effect");
        done();
    }, 5);
    
    
    $.mockjax.clear(mockjax_id);
});

QUnit.test( "cc_modal.submit => failure test", function( assert ) {
    // set up async
    var done = assert.async();

    // Set up AJAX mocked responses
    var mockjax_id = $.mockjax({
        url: "/update_cc.php",  responseTime: 0,
        responseText: JSON.stringify({success: false,response: "Are you a mock turtle?"})
    });
    // sumbit the event
    cc_modal.submit(event);
    // 5 millisecond delay to ensure that the response made it and the field was updated
    setTimeout(function(){
        assert.equal($("#qunit-fixture #cc_modal_error").text(), "Are you a mock turtle?", "updates the error message in #cc_modal_error");
        done();
    }, 5);
    
    $.mockjax.clear(mockjax_id);
});

QUnit.test( "cc_modal.submit => 404 test", function( assert ) {
    // set up async
    var done = assert.async();
    // Set up AJAX mocked responses
    var mockjax_id = $.mockjax({
        url: "/update_cc.php", status: 404,
        responseText: "Not Found", responseTime: 0
    });
    // sumbit the event
    cc_modal.submit(event);
    // 5 millisecond delay to ensure that the response made it and the field was updated
    setTimeout(function(){
        assert.equal($("#qunit-fixture #cc_modal_error").text(), "Error (404): Not Found", "Formatts http errors correctly");
        done();
    }, 5);
    // clear the api mock
    $.mockjax.clear(mockjax_id);
});

QUnit.test( "cc_modal.submit => invalid validations", function( assert ) {
    // set some error in one of the error fields
    $("#cc_number_errors").text("some Error");
    // Set up AJAX mocked responses
    var mockjax_id = $.mockjax({
        url: "/update_cc.php",  responseTime: 0,
        responseText: JSON.stringify({success: false,response: "Are you a mock turtle?"})
    });
    // run submit for the event
    cc_modal.submit(event);
    // chech that it has set up a error message
    assert.notEqual($("#qunit-fixture #cc_modal_error").text(), "", "Displays an error message if one of the validations have faild");
    // check that no network calls where made
    assert.equal($.mockjax.mockedAjaxCalls().length, 0, 'no network calls are made');
    // clear the api mock
    $.mockjax.clear(mockjax_id);
});