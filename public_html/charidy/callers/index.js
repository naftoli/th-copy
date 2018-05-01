$('table').DataTable({
    "lengthMenu": [ [-1, 10, 25, 50, 100], ["All", 10, 25, 50, 100] ]
});

$("tbody tr").click( function( event ) {
    var checkbox;

    if ( $( event.target ).prop( "tagName" ) === "TD" ) {
        checkbox = $( event.target ).parent().find( "input[type='checkbox']" )[0];
    } else if ( $( event.target ).prop( "tagName" ) === "TD" ) {
        checkbox = $( event.target ).find( "input[type='checkbox']" )[0];
    }

    if ( checkbox ) {
        checkbox.checked = !checkbox.checked;
    }
    
});

$("#assign").click( function() {

    var caller_id = $("#caller_id").val();
    var caller_name = $("#caller_id option[value='"+ caller_id + "']").text().trim();

    var donor_checkboxes = $("input.donor-select:checked");
    var donors = [];

    $.each( donor_checkboxes, function( index, input ) {
        donors.push( input.dataset.donor_id );
    });

    var postData = { 
        caller_id: caller_id,
        donor_ids: donors
    }
    
    $.post( "ajax/assignCaller.php", postData, function( response ){
        $.each( donor_checkboxes, function( index, input ) {
            input.checked = false;
            $("#donor-caller-" + input.dataset.donor_id ).text( caller_name );
        });
    });

});

$("#print_caller_letters").click( function() {
    var print_caller_id = $("#print_caller_id").val();

    if ( print_caller_id ) {
        event.target.href = "caller_letters.php" + "?id=" + print_caller_id;
    } else {
        event.target.href = "caller_letters.php"
    }
})