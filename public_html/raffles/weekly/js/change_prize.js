/**
 * Event Hander for a#change_prize with a data-raffle_id and data-prize_id props assigned to it.
 * 
 * Does the following things:
 * 
 * 1. Creates modal to pick a prize from
 * 2. Hits an endpoint that updates all the winners of said raffle with the current prize id to the new one.
 * 3. Reloads the current page.
 */
$("#change_prize").click( function( event ) {
    // check if the raffle modal exists
    if( $("#raffle_modal")[0] ) {
        modal( $("#raffle_modal"), false ).show();
        return false;
    }

    var raffle_id = event.target.dataset.raffle_id;
    var prize_id = event.target.dataset.prize_id;
    // make sure that the props are set
    if( !raffle_id || !prize_id )
        return false;
    
    $( event.target ).text( "Loading Prizes..." );
    // get the info for and create the modal
    $.get("/raffles/weekly/ajax/getPrizes.php", function( response ){
        renderModal( JSON.parse( response ), raffle_id );
        $( event.target ).text( "Change Prize" );
    });

    // render the modal for them to pick a prize
    function renderModal( options, raffle_id ) {
        // create modal html
        var modalHTML = "<div class='modal' id='raffle_modal'>";
        modalHTML +=    "<div class='modal-content'>";
        // modal title
        modalHTML +=    "<h1>Change Prize <span class='close' id='exit'>X</span></h1>"; 
        
        // render all the options
        for ( var index = 0; index < options.length; index++ ) {
            if ( options[index].prize_id != prize_id ) {
                modalHTML += render_option( options[index], raffle_id ); // render the option
            }
        }

        // close modal html
        modalHTML +=    "</div>"; // end .modal-content
        modalHTML +=    "</div>"; // end .modal
        
        $("body").append( modalHTML ); // add the modal to the page....
        var raffle_modal = modal( $("#raffle_modal"), true ); // return a new instance of the modal
        raffle_modal.show();

        $("#raffle_modal .change_prize_item").click( change_prize )
    }

    function render_option( option, raffle_id ) {
        var optionHTML = "<div class='modal-raffle-option'>";
        optionHTML += "<img src='" + option.thumbnail + "' alt='thumb'/>";
        optionHTML += "<span>" + option.name + " (#" + option.prize_id + ")</span>"
        optionHTML += "<a class='button change_prize_item' data-prize_id='" + option.prize_id + "' data-raffle_id='" + raffle_id + "'>Use This Prize</a>";
        optionHTML += "<div id='clearfix'></div>";
        optionHTML += "</div>";
        // return the generated html
        return optionHTML;
    }

    function change_prize( event ) {
        $(event.target).text("Changing prize...");
        // get the post data
        var postData = Object.assign( {}, event.target.dataset, { old_prize_id: prize_id } );
        
        $.post("/raffles/weekly/ajax/changePrize.php", postData, function( response ) {
            response = JSON.parse( response );

            if ( response.success ) {
                location.reload(); // refresh the page
            } else {
                modal( $("#raffle_modal"), false ).hide();
                alert( response.error );
            }
        });
    }
})