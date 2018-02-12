/************** UNIT TESTS FOR MONEY_FORMAT() FUNCTION ************************
*       
*       Runs on /tests/QUnit/admin_register_students.html.
*       Tests js used in /admin_register_students.php
*
*       Please note that these tests take a long time to run. average of 20ms each on dual core i7.
*
*       Depends on jQuery and /js/utils/money_format.js
*
*       Written by Menachem Hornbacher on 9/28/2017
*       Function was pre-written and remains unmodified
*
***********************************************************************/

QUnit.module( "money_format" );

QUnit.test( "money_format test", function( assert ) {
    assert.equal(money_format(1), "1.00", "Add two decimal places when provied with an integer");
    assert.equal(money_format(1.3), "1.30", "Add one decimal place when provied only one decimal position");
    assert.equal(money_format(1.845), "1.85", "Round up to the nearest second decimal place");
    assert.equal(money_format(-1), "-1.00", "Add - before number when provided with a negative number");
});

QUnit.test( "calculate_student_total test", function( assert ) {
    assert.equal(calculate_student_total(5, 6, "add"), "11.00", "Returns the first two numbers (money_format() called first) added if 'add' is passed in as the third paramater");
    assert.equal(calculate_student_total(5, 6, "test"), "-1.00", "Returns the first two numbers (money_format() called first) subtracted if anything else is passed in as the third paramater");
});