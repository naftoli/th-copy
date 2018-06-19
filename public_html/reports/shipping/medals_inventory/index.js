$('.show-details').click( showDetailsModal );

$('.subtract-inventory').click( subtractInventory );
$('.add-inventory').click( addInventory );

$('.modal_exit').click( hideDetailModal );

function showDetailsModal(){
    $('#details-modal').css({visibility: 'visible', opacity: 1});
    getInventoryDetails( event.target.dataset.id );
}

function hideDetailModal() {
    $('#details-modal').css({opacity: 0});
    setTimeout( function() {
        $('#details-modal').css({visibility: 'hidden'});
    }, 250 );
}

function getInventoryDetails( inventory_id ) {
    $('#inventory-details').html( '<div class="loader"></div>' );
    $.post('ajax/inventory_details.php', { id: inventory_id }, function( response ){
        $('#inventory-details').html( response );
    });
}

function subtractInventory( event ){
    var postData = {
        number: getEnteredNumber( event.target ),
        action: 'subtract',
        id: event.target.dataset.id
    };
    updateInventory( postData );
}

function addInventory( event ) {
    var postData = {
        number: getEnteredNumber( event.target ),
        action: 'add',
        id: event.target.dataset.id
    };
    updateInventory( postData );
}

function updateInventory( postData ){
    $.post( 'ajax/updateInventory.php', postData, function( response ) {
        if ( !response.success ) { 
            return alert( response.error ) 
        }
        if ( postData.action == 'add' ){
            $('#stock-' + postData.id ).text(
                parseInt( $('#stock-' + postData.id ).text() ) + parseInt( postData.number )
            );
        } else {
            $('#stock-' + postData.id ).text(
                parseInt( $('#stock-' + postData.id ).text() ) - parseInt( postData.number )
            );
        }
    });
}

function getEnteredNumber( target ){
    var number = $(target).parent().find('input');
    var response = number.val();
    number.val(0);
    return response;
}