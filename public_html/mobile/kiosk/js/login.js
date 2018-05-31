$(document).ready( function() {
    // get the URL of the page
    var url = location.href.toString();
    
    // check for valid card numbers
    function checkNumber( card ) {
        if (card.length == 20) {
            $.post('api/checkID.php', { card : card }, function( res ) {
                var response = $.parseJSON( res );
                if (response.success) {
                    // set the localstorage keys to note that it is a user and not an admin logged in
                    localStorage.setItem( "login", "user" );
                    localStorage.setItem( "id", response.user_id );
                    localStorage.setItem( "kiosk", true );
                    // redirect to the users profile page
                    location.href = "/mobile/reg/medals/index.html?id=" + response.user_id;
                } else {
                    alert(response.body);	
                }
            });
        } else {
            alert('Card must be 20 digits.');
        }
    }
    // submit the card number manually
    $("#cardForm").submit( function(e) {
        e.preventDefault();
        var card = $("#scanner").val();
        checkNumber( card );
        return false;
    });
    // click the card
    $("#scan").click( function() {
        var card = $("#scanner").val();
        checkNumber( card );
    });
    
    // start the scanner
    Quagga.init({
        inputStream: {
            name : "Live",
            type : "LiveStream",
            target: "#barcode_scanner",
            constraints: {
                width: {min: 640, ideal: 1280, max: 1920},
                height: {min: 480, ideal: 720, max: 1280},
                aspectRatio: {min: 1, max: 100},
                area: {
                    top: "0%",
                    right: "0%",
                    left: "0%",
                    bottom: "0%"
                },
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
    }, function(error) {
        if ( error ) {
            // TODO: allow the user to enter their barcode manually if we cannot scan it
            console.error( error );
            $("#barcode_scanner").hide();
            return;
        } else {
            $("#manual_scanner").hide();
            $("#barcode_scanner").show();
            $("#barcode_scanner").addClass("show");
            $(".container.body").addClass("shrink");
        }
        console.log( "Quagga JS initialized. Ready to start Scanning Cards" );
        Quagga.start();
    });

    // callback to run whenever a code is scanned (login?)
    var test = function(data){ 
        console.log( data.codeResult.code );
        checkNumber( data.codeResult.code ); // check the number
    }

    Quagga.onDetected(test);

    // show a box when scanning the barcode...
    Quagga.onProcessed(function(result) {
        var drawingCtx = Quagga.canvas.ctx.overlay,
            drawingCanvas = Quagga.canvas.dom.overlay;

        if (result) {
            if (result.boxes) {
                drawingCtx.clearRect(0, 0, parseInt(drawingCanvas.getAttribute("width")), parseInt(drawingCanvas.getAttribute("height")));
                result.boxes.filter(function (box) {
                    return box !== result.box;
                }).forEach(function (box) {
                    Quagga.ImageDebug.drawPath(box, {x: 0, y: 1}, drawingCtx, {color: "green", lineWidth: 2});
                });
            }

            if (result.box) {
                Quagga.ImageDebug.drawPath(result.box, {x: 0, y: 1}, drawingCtx, {color: "#00F", lineWidth: 2});
            }

            if (result.codeResult && result.codeResult.code) {
                Quagga.ImageDebug.drawPath(result.line, {x: 'x', y: 'y'}, drawingCtx, {color: 'red', lineWidth: 3});
            }
        }
    });
});