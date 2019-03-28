// CC validate functions are loaded in HTML file
QUnit.module( "cc_validate" );
QUnit.test( "cc_validate.setError test", function( assert ) {
  var error_message = $("#qunit-fixture #test_errors");
  var event = {target: {id: "test"}};
  
  assert.equal(error_message.text(), "", "Error message is empty");
  
  cc_validate.setError(event, "error message");
  assert.equal(error_message.text(), "error message", "Error message is filled once function is called");
});

QUnit.test( "cc_validate.clearError test", function( assert ) {
  var error_message = $("#qunit-fixture #test_errors");
  error_message.text("error message");
  var event = {target: {id: "test"}};
  
  assert.equal(error_message.text(), "error message", "Error message is full");
  
  cc_validate.clearError(event);
  assert.equal(error_message.text(), "", "Error message is clear after calling clearError");
});

QUnit.test( "cc_validate.basicValidation test", function( assert ) {
  var error_message = $("#qunit-fixture #test_errors");
  var event = {target: {id: "test", value: "ABC"}}; // create a fake event
  // start with an error
  error_message.text("error message");
  // set up some valid and invalid regex
  var validRegex = new RegExp("[A-Z]");
  var invalidRegex = new RegExp("[0-9]");
  // make sure it starts with an error
  assert.equal(error_message.text(), "error message", "Error message is prepopulated");
  
  assert.equal(cc_validate.basicValidation(event, [validRegex], "error message"), true, "Returns true if regex matches");
  assert.equal(error_message.text(), "", "Clears error message if regex is valid");
  
  assert.equal(cc_validate.basicValidation(event, [invalidRegex], "error message"), false, "Returns false if regex is invalid");
  assert.equal(error_message.text(), "error message", "Displays error message if regex is invalid");
  
  assert.equal(cc_validate.basicValidation(event, [invalidRegex, validRegex], "error message"), true, "Returns true if one of the regex items match");
  assert.equal(error_message.text(), "", "Clears error message if one of the regex items match");
  
});


QUnit.test( "cc_validate.setUpModalValidaitons #cc_number test", function( assert ) {
  cc_validate.setUpModalValidaitons() // call the subject

  // and now we will test the resutls
  var num = $("#qunit-fixture #cc_number");
  var num_errors = $("#qunit-fixture #cc_number_errors");
  
  // first lets make sure that all the errors start off empty
  assert.equal(num_errors.text(), "", "#cc_number_errors starts off blank");
  
  // test CC validations
  num.val("1234").trigger('change');;
  assert.notEqual(num_errors.text(), "", "Shows #cc_number_error if card number is two short");
  
  num.val("12345678901234567").trigger('change');;
  assert.notEqual(num_errors.text(), "", "Shows #cc_number_error if card number is two long");
  
  num.val("4111 1111 1111 1111").trigger('change');;
  assert.notEqual(num_errors.text(), "", "Shows #cc_number_error if card number has spaces");
  
  num.val("41111111A1111111").trigger('change');;
  assert.notEqual(num_errors.text(), "", "Shows #cc_number_error if card number Contains letters");
  
  num.val("411111111111111").trigger('change');;
  assert.equal(num_errors.text(), "", "Clears #cc_number_error if card number is 15 characters long (AMEX)");
  
  num.val("4111111111111111").trigger('change');;
  assert.equal(num_errors.text(), "", "Clears #cc_number_error if card number is 16 characters long (Visa/MC)");
    
});

QUnit.test( "cc_validate.setUpModalValidaitons #cc_exp test", function( assert ) {
  cc_validate.setUpModalValidaitons() // call the subject
  var exp = $("#qunit-fixture #cc_exp");
  var exp_errors = $("#qunit-fixture #cc_exp_errors");
  
  assert.equal(exp_errors.text(), "", "#cc_exp_errors starts off blank");
  // exp validation
  exp.val("123").trigger('change');
  assert.notEqual(exp_errors.text(), "", "Shows #cc_exp_error if exp is too short");
  
  exp.val("12345").trigger('change');
  assert.notEqual(exp_errors.text(), "", "Shows #cc_exp_error if exp is too long");
  
  exp.val("1234").trigger('change');
  assert.equal(exp_errors.text(), "", "Clears #cc_exp_error if exp is 4 digits");
  
  exp.val("1A34").trigger('change');
  assert.notEqual(exp_errors.text(), "", "Shows #cc_exp_error if exp is contains a non-number");
  
  exp.val("1/234").trigger('change');
  assert.notEqual(exp_errors.text(), "", "Shows #cc_exp_error if exp contains / in the wrong location");
  
  exp.val("12/34").trigger('change');
  assert.equal(exp_errors.text(), "", "Clears #cc_exp_error if format follows XX/XX");
  
});

QUnit.test( "cc_validate.setUpModalValidaitons #cc_cvv test", function( assert ) {
  cc_validate.setUpModalValidaitons() // call the subject
  var cvv = $("#qunit-fixture #cc_cvv");
  var cvv_errors = $("#qunit-fixture #cc_cvv_errors");
  
  assert.equal(cvv_errors.text(), "", "#cc_cvv_errors starts off blank");
  // test cvv validations
  cvv.val("12").trigger('change');
  assert.notEqual(cvv_errors.text(), "", "Shows #cc_cvv_error if cvv is too short");
  
  cvv.val("12345").trigger('change');
  assert.notEqual(cvv_errors.text(), "", "Shows #cc_cvv_error if cvv is too long");
  
  cvv.val("1A2").trigger('change');
  assert.notEqual(cvv_errors.text(), "", "Shows #cc_cvv_error if cvv contains a non-number");
  
  cvv.val("123").trigger('change');
  assert.equal(cvv_errors.text(), "", "Clears #cc_cvv_error if cvv is 3 digits long (MC/Visa)");
  
  cvv.val("1234").trigger('change');
  assert.equal(cvv_errors.text(), "", "Clears #cc_cvv_error if cvv is 4 digits long (AMEX)");

});

QUnit.test( "cc_validate.setUpModalValidaitons #billing_address test", function( assert ) {
  cc_validate.setUpModalValidaitons() // call the subject
  var address = $("#qunit-fixture #billing_address");
  var address_errors = $("#qunit-fixture #billing_address_errors");
  
  assert.equal(address_errors.text(), "", "#billing_address_errors starts off blank");
  // test address validations
  address.val("15$ Someplace | Drive").trigger('change');
  assert.notEqual(address_errors.text(), "", "Shows #billing_address_error if address contains odd characters");
  
  address.val("15 Someplace Drive").trigger('change');
  assert.equal(address_errors.text(), "", "Shows #billing_address_error if address is just numbers and letters");
  
  address.val("15' Someplace, Drive.").trigger('change');
  assert.equal(address_errors.text(), "", "Shows #billing_address_error if address only contains one of , . and ' ");
  
  // test zip validators
});

QUnit.test( "cc_validate.setUpModalValidaitons #billing_postal USA test", function( assert ) {
  cc_validate.setUpModalValidaitons() // call the subject
  var postal = $("#qunit-fixture #billing_postal");
  var postal_errors = $("#qunit-fixture #billing_postal_errors");
  
  assert.equal(postal_errors.text(), "", "#billing_postal_errors starts off blank");
  // United States
  postal.val("1121").trigger('change');
  assert.notEqual(postal_errors.text(), "", "US: Shows #billing_postal_error if zip is too short");
  
  postal.val("112134").trigger('change');
  assert.notEqual(postal_errors.text(), "", "US: Shows #billing_postal_error if zip is too long");
  
  postal.val("11213-145").trigger('change');
  assert.notEqual(postal_errors.text(), "", "US: Shows #billing_postal_error if zip extension is too short");
  
  postal.val("11213-12345").trigger('change');
  assert.notEqual(postal_errors.text(), "", "US: Shows #billing_postal_error if zip extension is too long");
  
  postal.val("11213-1234").trigger('change');
  assert.equal(postal_errors.text(), "", "US: Clears #billing_postal_error if zip is 5 digits with a - and 4 digit extension");
  
  postal.val("11213").trigger('change');
  assert.equal(postal_errors.text(), "", "US: Clears #billing_postal_error if zip is 5 digits without extension");
});

QUnit.test( "cc_validate.setUpModalValidaitons #billing_postal Canada test", function( assert ) {
  cc_validate.setUpModalValidaitons() // call the subject
  var postal = $("#qunit-fixture #billing_postal");
  var postal_errors = $("#qunit-fixture #billing_postal_errors");
  
  assert.equal(postal_errors.text(), "", "#billing_postal_errors starts off blank");
  // Canada
  postal.val("1A").trigger('change');
  assert.notEqual(postal_errors.text(), "", "CA: Shows #billing_postal_error if postal is too short");
  
  postal.val("1A1 ").trigger('change');
  assert.notEqual(postal_errors.text(), "", "CA: Shows #billing_postal_error if postal is too long (even with white space)");
  
  postal.val("A11").trigger('change');
  assert.notEqual(postal_errors.text(), "", "CA: Shows #billing_postal_error if postal is not X0X");
  
  postal.val("A1A-C1C").trigger('change');
  assert.notEqual(postal_errors.text(), "", "CA: Shows #billing_postal_error if postal is not X0X-0X0");
  
  postal.val("A1A 1C1").trigger('change');
  assert.equal(postal_errors.text(), "", "CA: Clears #billing_postal_error if postal is X0X 0X0");
  
  postal.val("A1A-1C1").trigger('change');
  assert.equal(postal_errors.text(), "", "CA: Clears #billing_postal_error if postal is X0X-0X0");
  
  postal.val("A1A").trigger('change');
  assert.equal(postal_errors.text(), "", "CA: Clears #billing_postal_error if postal is X0X");
});

