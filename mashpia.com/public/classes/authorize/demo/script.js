// use some metaprogramming to hit the correct page with the data and fill the correct result with the HTML result

function profileIdSubmit(event){
    event.preventDefault();
    var url = event.target.action;
    var data = $(this).serialize();
    var target = event.target.id + "Result"
    $.post(url, data, function(data) {
        $("#"+target).html(data);
    });
}


$( document ).ready( function() {
    console.log("Ready");
    
    $('form#getProfileId').on('submit', profileIdSubmit);
    $('form#editProfileId').on('submit', profileIdSubmit);
    $('form#createProfileId').on('submit', profileIdSubmit);
    $('form#chargeProfileId').on('submit', profileIdSubmit);
    $('form#createPaymentId').on('submit', profileIdSubmit);
    $('form#editPaymentId').on('submit', profileIdSubmit);
    
});