var campaigns; // defined in page via PHP
/**
 * calculate the totals for the school at the top
 * 
 * school: the school id
 * type: the campaign type
 */ 
function calcTotal( school, type ) {
    var total = 0; // store the total
    var table = $( "#" + school ); // get the table
    var type_letter = type == 'tanya' ? "t" : "m";

    $( table ).find( '.' + type_letter + 'learn' ).each( function() {
        var val = parseInt( $( this ).val() );
        if ( val > 0 ) total += parseInt( $( this ).val() );
    });
    $( "#" + type_letter + "learned" ).text( total );
}

function updateLines( target, campaign_type ) {
    // get the info
    var id      = $( target ).parent().parent().find( '.userID'   ).val();
    var grade   = $( target ).parent().parent().find( '.classID'  ).val();
    var school  = $( target ).parent().parent().find( '.schoolID' ).val();

    // clean the number
    var num = $( target ).val().trim().replace(/\,/g,'');
    // and make sure it is a number
    if ( isNaN( num ) ) {
        alert("You must enter a number.");
        return false;
    }

    calcTotal('lines_learned', campaign_type);
    // get the campaigns ID from the global object
    var campaign_id = campaign_type == "tanya" ? campaigns.tanyaCampaign : campaigns.mishnaCampaign;
    // setup the data to post to the server
    var postData = {
        id      : campaign_id, 
        val     : num, 
        user    : id, 
        grade   : grade,
        table   : 'lines_learned',
        school  : school 
    }
    // update the campaign
    $.post('ajax/updateBalPehCampaign.php', postData, function( data ) {
        if (data == 0) {
            alert("Error updating.");
        }
    });
}

// setup event listeners on page load
$( function() {
    loadMishnaCalculator();
    // get the campaigns from the global object setup via PHP on page
    var tanyaCampaign = campaigns.tanyaCampaign;
    var mishnaCampaign = campaigns.mishnaCampaign;

    var current_mishna_input;
    
    // confirm all the items
    $( ".confirmAll" ).click( function() {
        var schoolID = $( this ).parent().find( '.schoolID' ).val(); // get the school ID
        var postData = { 
            school : schoolID, 
            campaigns : [ tanyaCampaign, mishnaCampaign ] 
        };
        // confirm the vinfo
        $.post( 'ajax/updateBpSchoolSummary.php', postData, function( error ) {
            if ( parseInt( error ) === 1 ) {
                alert( 'Error Updating.' );
            } else {
                alert( 'Updated.' );
            }
        });
    });
    
    // when someone wants to sync the users info
    $( ".sync" ).click( function() {
        var id = $( this ).parent().parent().find( '.userID' ).val();
        var postData = {
            user : id, 
            campaigns : [ tanyaCampaign, mishnaCampaign ]
        }

        $.post('ajax/updateBpUserSummary.php', postData, function( error ) {
            if ( parseInt(error) === 0 ) {
                alert('updated.');
                location.href = '/editSoldierLines2.php';
            }
        });
    });
    
    // when a key is pressed on the tanya field
    $( ".tanya" ).keyup( function( event ) {
        if ( event.keyCode == 9 ) { return false } // do not run if the key is a TAB
        updateLines( this, 'tanya');
    }); // end tanya keyup
        
    $( ".mishna" ).keyup( function( event ) {
        if ( event.keyCode == 9 ) { return false } // do not run if the key is a TAB
        updateLines( this, 'mishna');
    });
    // listener for "Copy Parent Entered Amount to Tanya Learned"
    $(".copy").click( function() {
        var confirm = window.confirm("Are you sure you want to copy all numbers (this will overwrite what has been entered by you until now)?");
        if (confirm) {
            var id = $( this ).parent().parent().find( 'table' ).attr( 'id' );
            $("#" + id).find( "tr" ).each( function() {
                var num = $( this ).find( '.parentEntered' ).text();
                if ( parseInt( num ) > 0) {
                    $( this ).find( '.tlearn' ).val( num );
                    $( this ).find( '.tanya' ).trigger( 'keyup' );
                }
            });
        }
    });
    // allow BC's to calcuate mishna lines by selecting mesechtos
    $( ".by_mishna" ).click( function( event ) {
        modal( $("#mishna-selector"), true ).show(); // show the modal...
        current_mishna_input = $( event.target ).parent().parent().find("input.mlearn")
    });
    // when they close the modal clear all the checkboxes
    $( "#mishna-selector .close" ).click( function() {
        $("#mishna_total").val(0);
        $.each( $( "#mishna-selector input[type='checkbox']" ), function( index, checkbox ) {
            checkbox.checked = false;
        });
    });

    $( "#set_mishna_total" ).click( function( event ){
        var line_total = $("#mishna_total").val();
        $( "#mishna-selector .close" ).click();
        current_mishna_input.val( line_total );
        current_mishna_input.trigger( "keyup" );
    });
});

// load the mishna calculator
function loadMishnaCalculator() {
    $.get("/ajax/tanya/getMishnaDropdown.php", function( response ) {
        $("#mishna_selector").html( render_mishnos( JSON.parse( response ) ) );
        // setup the dropdowns
        $("#mishna_selector span.title.dropdown ").click( function( event ) {
            if ( event.target.classList[1] === "dropdown" ) {
                $( event.target ).parent().css({ "height": "auto" });
                $( event.target ).parent().siblings().css({ "height": "26px" });
            }
        });

        function checkAll( selector ){
            return function( event ) {
                $.each( $( event.target ).parent().parent().find( selector ), function( index, item ) {
                    item.checked = event.target.checked;
                    $(item).change();
                });
            }
        }

        $("#mishna_selector input.mesechto_lines").change( checkAll( "input.perek_lines" ) );

        $("#mishna_selector input.perek_lines").change( checkAll( "input.mishna_lines" ) );

        $("#mishna_selector input.mishna_lines").change( function( event ) {
            var amount = parseInt(event.target.value);
            var current_total = parseInt( $( "#mishna_total" ).val() );
            // prevent NaN errors from the input field
            if ( isNaN( current_total ) ) {
                current_total = 0;
            }
            // add or subtract the value from the total in order to calculate the lines
            $( "#mishna_total" ).val( event.target.checked ? current_total + amount : current_total - amount );
        });
    });
}

function render_mishnos( seforim ) {
    html = "";
    // go through each safer
    for ( var safer_index = 0; safer_index < seforim.length; safer_index++ ) {
        var safer = seforim[ safer_index ];

        html += '<div class="sader">';
        html +=     '<span class="title dropdown">' + safer.name + '</span>';
        // each mesechto in each safer
        for ( var mesechto_index = 0; mesechto_index < safer.mesechtos.length; mesechto_index++ ) {
            var mesechto = safer.mesechtos[ mesechto_index ];
            
            html += '<div class="mesechto">';
            html +=     '<span class="title dropdown">';
            html +=         '<input type="checkbox" class="mesechto_lines"/>'
            html +=         mesechto.name 
            html +=     '</span>';
            // each perek in each meschto
            for ( var perek_index = 0; perek_index < mesechto.perokim.length; perek_index++) {
                var perek = mesechto.perokim[ perek_index ];

                html += '<div class="perek">';
                html +=     '<span class="title">';
                html +=         '<input type="checkbox" class="perek_lines"/>'
                html +=         perek.name 
                html +=     '</span>';
                // and each mishna in each perek
                for ( var mishna_index = 0; mishna_index < perek.mishnos.length; mishna_index++ ) {
                    var mishna = perek.mishnos[ mishna_index ];

                    html += '<div class="mishno">';
                    html +=     '<input type="checkbox" value="' + mishna.lines + '" class="mishna_lines"/>'
                    html +=     '<span> ' + mishna.name + ' </span>';
                    html +=     '<span> (' + mishna.lines + ' lines) </span>';
                    html += '</div>';
                }

                html += '</div>'
            }

            html += '</div>';
        }

        html += '</div>';
    } // end foreach safer
    return html;
} // end render_mishnos function