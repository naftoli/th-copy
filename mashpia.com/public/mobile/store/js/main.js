// on page load
setupScanner( "environment" );
$("#toggle-manual").click( toggleManual );
// detect the info on the scanner input
$("#manual-scanner #scanner").keyup( function( event ) {
    if ( event.target.value.length == 20 ) {
        checkNumber( event.target.value );
    }
});

$("#cardForm").submit( function( event ){
    event.preventDefault();
    checkNumber( $('#scanner').val() )
})

Quagga.onDetected( function( data ) {
    checkNumber( data.codeResult.code )
});

// check the number as a user posts it
async function checkNumber( number ) {
    const url = location.href.toString();
    const pos = url.indexOf('=');
    const user = url.substring(pos + 1);
    const res = await fetch('ajax/checkCard.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            user: user,
            card: number
        })
    })
    const data = await res.json()
    setTimeout( function() {
        alert(data.msg ? data.msg : "Card scanned successfully")
    }, 1000 );
    $("#scanner").val('')
    return true;
}

// setup the quaggajs scanner
function setupScanner( mode ){
    mode = mode ? mode : "environment"; // or user
    var config = {
        inputStream: {
            name : "Live",  type : "LiveStream",    target: "#barcode_scanner",
            constraints: {
                width: { min: 640 },
                height: { min: 480 },
                aspectRatio: { min: 1, max: 2 },
                facingMode: mode // or user
            }
        },
        locator: {
            patchSize: "medium",
            halfSample: true
        },
        numOfWorkers: navigator.hardwareConcurrency ? navigator.hardwareConcurrency : 2, // assume dual core
        decoder: {
            readers : ["code_128_reader"]
        },
        locate: true,
        multiple: true, 
    };

    var setup = function( error ){
        if ( error ) {
            // showError( "Sorry, it seems we cannot scan cards on your device. Please enter in the card number by hand." );
            $("#barcode_scanner").hide();
            $("#manual-scanner").show(); // show the manual scanner
            $('#manual-scanner #scanner').focus(); // focus for barcode readers
            // setup the listener
        } else {
            $("#toggle-manual").show();
            console.log( "Quagga JS initialized. Ready to start Scanning Cards" );
            Quagga.start();
        }
    }

    Quagga.init( config, setup );
}

function toggleManual( event ){
    if ( $("#manual-scanner").is(":visible") ){
        $("#toggle-manual img").attr("src", "/mobile/img_new/scanner-2-color-red-svg.svg" );
        $("#manual-scanner").hide();
        $("#barcode_scanner").show();
    } else {
        $("#toggle-manual img").attr("src", "/mobile/img_new/camera-color-red-svg.svg");
        $("#manual-scanner").show();
        $("#barcode_scanner").hide();
    }
}