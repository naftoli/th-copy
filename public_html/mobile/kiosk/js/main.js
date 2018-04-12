// check the number as a user posts it
function checkNumber( cardNumber ) {
    if ( cardNumber.length == 20 ) {
        $.post( 'api/checkID.php', { card : cardNumber }, login );
    } else {
        console.error( "Invalid Size: ", cardNumber );
    }
}

// login the user based on the response
function login( response ) {
    response = $.parseJSON( response );
    if ( !response.success ) showError( response.body );
    // log the user in
    localStorage.setItem( "login", "user" );
    localStorage.setItem( "id", response.user_id );
    localStorage.setItem( "kiosk", true );
    // and redirect to their profile page
    location.href = "/mobile/reg/medals/index.html?id=" + response.user_id;
}

// show errors to the user in a nice, async way
function showError( error ) {
    console.error( error );
};

// show a box when the user scans the code
function showScanningBox( result ) {
    var drawingCtx = Quagga.canvas.ctx.overlay,
        drawingCanvas = Quagga.canvas.dom.overlay;

    if ( result && result.box ) {
        Quagga.ImageDebug.drawPath(result.box, {x: 0, y: 1}, drawingCtx, {color: "#00F", lineWidth: 2});
    }

    if ( result && result.codeResult && result.codeResult.code) {
        Quagga.ImageDebug.drawPath(result.line, {x: 'x', y: 'y'}, drawingCtx, {color: 'red', lineWidth: 3});
    }
}

// initialize Quagga JS with the follwing settings and setup the event listeners
Quagga.init({
    inputStream: {
        name : "Live",
        type : "LiveStream",
        target: "#barcode_scanner",
        constraints: {
            width: {min: 640, ideal: 1280, max: 1920},
            height: {min: 480, ideal: 720, max: 1280},
            aspectRatio: {min: 1, max: 100},
            facingMode: "environment" // or user
        }
    },
    locator: {
        patchSize: "medium",
        halfSample: true
    },
    numOfWorkers: 2,
    decoder: {
        readers : ["code_128_reader"]
    },
    locate: true,
    multiple: true
}, function ( error ) {
    if ( error ) {
        showError( "Sorry, it seems we cannot scan cards on your device. Please enter in the card number by hand." );
        $("#barcode_scanner").hide();
        $("#manual_scanner").show(); // show the manual scanner
    } else {
        console.log( "Quagga JS initialized. Ready to start Scanning Cards" );
        Quagga.start();
    }
});

Quagga.onDetected( function( data ) {
    if ( !checkNumber( data.codeResult.code ) ) {
        showError( "Sorry, it seems we could not read the card properly. Please try another angle." )
    }
});

Quagga.onProcessed( showScanningBox );