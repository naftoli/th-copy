var clipboard = new ClipboardJS('.btn');

clipboard.on('success', function(e) {
    alert( "Copied to clipboard!" );
    e.clearSelection();
});

clipboard.on('error', function(e) {
    console.error('Action:', e.action);
    console.error('Trigger:', e.trigger);
    alert( "Could not copy to clipboard" );
});

$("#options").submit( function( event ){
    event.preventDefault();
    
    $("#report").html( '<div class="loader"></div>' );
    
    var postData = {
        from: $("#from").val(),
        to: $("#to").val()
    };

    $.post( "ajax/rank_report.php", postData, function( response ){
        $("#report").html( response );
    })
} )