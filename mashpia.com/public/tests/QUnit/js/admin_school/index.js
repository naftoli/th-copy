/************** UNIT TESTS FOR JQUERY AUTOFILL ************************
*       
*       Runs on /tests/QUnit/admin_school.html.
*       Tests js used in /admin_school.php
*
*       Depends on jQuery and /js/admin/admin_school.php.js
*
*       Written by Menachem Hornbacher on 10/2/2017
*
***********************************************************************/

QUnit.module( "autofill" );
QUnit.test( "#copy_shipping_address", function( assert ) {
    setupAutofill();
    $("#copy_shipping_address").click();
    // assert that the values are updated
    assert.equal($("input[name='billing_address'").val(), $("input[name='shipping_address1'").val(), "copies the billing address from shipping_address1 input");
    assert.equal($("input[name='billing_city'").val(), $("input[name='shipping_city'").val(), "copies the billing address from shipping_city input");
    assert.equal($("input[name='billing_state'").val(), $("input[name='shipping_state'").val(), "copies the billing address from shipping_state input");
    assert.equal($("input[name='billing_postal'").val(), $("input[name='shipping_postal'").val(), "copies the billing address from shipping_postal input");
});

QUnit.test( "#copy_main_address", function( assert ) {
    setupAutofill();
    $("#copy_main_address").click();
    // assert that the values are updated
    assert.equal($("input[name='billing_address'").val(), $("input[name='address1'").val(), "copies the billing address from address1 input");
    assert.equal($("input[name='billing_city'").val(), $("input[name='city'").val(), "copies the billing address from city input");
    assert.equal($("input[name='billing_state'").val(), $("input[name='state'").val(), "copies the billing address from state input");
    assert.equal($("input[name='billing_postal'").val(), $("input[name='postal'").val(), "copies the billing address from postal input");
});