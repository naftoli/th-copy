
function loadTable( caller_id ) {
    var caller_id = $("#caller_id").val();
    $("#donor-table").html('<div class="loader"></div>');
    
    // wrap post in timer function
    setInterval( function() {
        $.post( "ajax/caller_table.php", { caller_id: caller_id }, function( response ){
            $("#donor-table").html( response );
            // use datatables
            $('#donor-table table').DataTable({
                "lengthMenu": [ [-1, 10, 25, 50, 100], ["All", 10, 25, 50, 100] ]
            });

            $("tbody tr").click( clickRow );
        });
    }, 60000);
}

function clickRow( event ) {
    var checkbox;
    // get the correct scope
    if ( $( event.target ).prop( "tagName" ) === "TD" ) {
        checkbox = $( event.target ).parent().find( "input[type='checkbox']" )[0];
    } else if ( $( event.target ).prop( "tagName" ) === "TD" ) {
        checkbox = $( event.target ).find( "input[type='checkbox']" )[0];
    }
    // and update the checkbox
    if ( checkbox ) {
        checkbox.checked = !checkbox.checked;
    }  
};

// assign a caller
$("#assign").click( function() {

    var caller_id = $("#caller_id").val();

    if ( !caller_id || caller_id === "-1" ){
        $("#invalid-caller").modal('show')
        return false;
    }

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

// print the caller letters
$("#print_caller_letters").click( function() {
    var print_caller_id = $("#print_caller_id").val();

    if ( print_caller_id ) {
        event.target.href = "caller_letters.php" + "?id=" + print_caller_id;
    } else {
        event.target.href = "caller_letters.php"
    }
});

$("#load-table").click( function( event ) {
    loadTable();
})