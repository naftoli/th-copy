// ******************* HELPER FUNCTIONS *********************

var cc_validate = function(){
    
    function setError(event, text){
        // get the item with the same id plus _errors and set the text to what was passed in
        $("#"+ event.target.id + "_errors").text(text);
    }
    
    function clearError(event){
        // get the item with the same id plus _errors and clear the text
        $("#"+ event.target.id + "_errors").text("");
    }
    
    function basicValidation(event, regexArray, message) {
        var data = event.target.value; // get the data from the event's target
        var valid = false; // by default it is not valid
        // test all the regex patterns
        var i = 0; // set i to 0
        while (i < regexArray.length && !valid){ // while we can iterate through the regexPatterns and we have not hit a valid test yet
            valid = regexArray[i].test(data); // check if it is valid
            i++; // go to the next one
        }
        if(!valid){ // if it is not valid
            setError(event, message); // display the error message
            return false;
        } else {
            clearError(event); // if it is valid clear the error message
            return true;
        }
    }
    
    //******************* Register CC validations **********************
    function setUpModalValidaitons(){
        $("#cc_number").change(function(event) {
            // check if it is an existing card or if it is valid
            var existing = new RegExp("^[X]{4}[0-9]{4}$"); // XXXX0000
            var valid = new RegExp("^[0-9]{15,16}$"); // 15 or 16 consecuative digits
    
            basicValidation(event, [existing, valid], "Credit Card number is Invalid");
            
            // if we want we can check the type of card
            // for the future this will check what card it is (https://www.regular-expressions.info/creditcard.html)
            //var Visa = new RegExp("^4[0-9]{12}(?:[0-9]{3})?$").test(cc_number);
            //var MasterCard = new RegExp("^(?:5[1-5][0-9]{2}|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)[0-9]{12}$").test(cc_number);
            //var AMEX = new RegExp("^3[47][0-9]{13}$").test(cc_number);
            //var Diners Club = new RegExp("^3(?:0[0-5]|[68][0-9])[0-9]{11}$").test(cc_number);
            //var Discover = new RegExp("^6(?:011|5[0-9]{2})[0-9]{12}$").test(cc_number);
            //var JCB = new RegExp("^(?:2131|1800|35\d{3})\d{11}$ ").test(cc_number);
        });
        
        $("#cc_exp").change(function(event){
            var existing = new RegExp("^[X]{4}$"); // XXXX
            var valid = new RegExp("^[0-9]{2}[/]?[0-9]{2}$"); // XX[/]XX
            // test the validations
            basicValidation(event, [existing, valid], "New exparation must be XX/XX");
        });
        
        $("#cc_cvv").change(function(event){
            // 3 or 4 digit number
            basicValidation(event, [new RegExp("^[0-9]{3,4}$")], "Please enter valid CVV");
        });
        
        $("#billing_address").change(function(event){
            // make sure that the address is only standard characters from beginning to end
            basicValidation(event, [new RegExp("^[A-Za-z0-9'\.\s\, ]+$")], "Please remove all special characters");
        });
        
        $("#billing_postal").change(function(event){
            // zip code validation from https://stackoverflow.com/questions/578406/what-is-the-ultimate-postal-code-and-zip-regex
            // actual validations. X = Letter, 0 = Number, [] means optional group
            var us = new RegExp("^[0-9]{5}([ \-]{1}[0-9]{4})?$"); // US zip code is 00000[-0000]
            var ca = new RegExp("^([A-Za-z][0-9][A-Za-z])([ -][0-9][A-Za-z][0-9])?$"); // CA zip code is X0X[ 0X0] or X0X[-0X0]
            // run the validation
            basicValidation(event, [us, ca], "Invalid US Zip or Canadian Postal Code");
        });
        
    }
    
    return {
        setError: setError,
        clearError: clearError,
        basicValidation: basicValidation,
        setUpModalValidaitons: setUpModalValidaitons
    }
}();


