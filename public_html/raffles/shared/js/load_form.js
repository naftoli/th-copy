// once the page is loaded
$(document).ready(function(){
    // when the type is selected...
    $("select[name='type']").change(function(){ // whenever the user changes this
        load_form($(this).val()); // load the form for that type
    });
    // load the default form (weekly) on page load
    load_form($("select[name='type']").val());
    
});

function load_form(type) {
    $.get("/raffles/"+type+"/forms/raffle_form.php", function(data){// get the form for the type selected
        $("#detailed_inputs").html(data); // set the page to the data
        // when an option from the list is selected
        $("select.week_start").change(function(event){
            fill_name($("option[value="+event.target.value+"]").html()); // autofill the name field
        });
        // autofill the name when the page loads
        fill_name($($("select.week_start").children()[0]).text())
    });
}
// Handle the requested autofill function
function fill_name(text) {
    $("input[name='name']").val(text.replace(/57[0-9]{2} - /, ''));
}