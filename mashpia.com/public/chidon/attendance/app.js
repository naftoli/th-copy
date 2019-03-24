// scope app so that we control it's internal contents.....
var debug = false;
var app = function() {
    
    var state = {
        login: false,
        user: {} // user that is logged in....
    };

    var super_admins = {
        'bunk': [
            'director', 
            'head counselor'
        ], 
        'walking_group': [
            
        ]
    }
    
    function logout() {
        localStorage.removeItem( 'attendance_login' );
        window.location = "index.html";
    }
    
    function login() {
        var login_key = localStorage.getItem( 'attendance_login' );
        // make sure that we even have a token....
        if (!login_key) return false;
        state.login = login_key;
        // get the info...
        $.post({
            url: "api/getAdmin.php",
            data: { login: login_key },
            async: false,
            success: function(response) {
                try { response = JSON.parse(response); }
                catch (e) { logout(); }
                // make sure that we can get the user data....
                if (!response.success) logout();
                // set the user in the state to the current user...
                state.user = response.user;
                state.info = JSON.parse( response.info );
                $("#user-name").text(state.user.name);
            }
        });
    }
    
    function setupApp() {
        setupGroupInfo();

        $("#buttons a").click( function() {
            $("#groups").hide();
            $("#buttons").hide();
            // get checked off groups
            let groupList = [];
            $(".group").each( function() {
                if ( $(this).is(":checked") ) {
                    groupList.push( $(this).val() );
                }
            });
            updateTimes( groupList );
            $("#times").show();
        });

        $("#timeDropdown").change( function() {
            let val = $(this).val();
            let type = $(this)[0].selectedOptions[0].dataset.type; 
            //alert( val + ',' + type );
            updateChildren( val, type );
        });
    }

    function setupGroupInfo() {
        let groups = state.info.assignments;
        console.log( groups );
        
        for ( group_type in groups ) {
            let html = '';
            for ( role in groups[group_type] ) {
                for ( group in groups[group_type][role] ) {
                    if ( groups[group_type][role][group] ) {
                        html += `
                            <div class='column is-one-fifth'>
                                <input type='checkbox' class='checkbox group' value='${groups[group_type][role][group]}' /> ${groups[group_type][role][group]}
                            </div>
                        `;
                    }
                } 
            }
            let type = "#" + group_type;
            let details = type + " .details";
            $( details ).append( html );
            $( type ).show();
        }
        $("#buttons").show();
    }

    function updateTimes( groupList ) {
        state.selectedGroups = groupList;
        $.post( "api/getMarkingOptions.php", { login: state.login, groups: groupList }, function( response ) {
            try { response = JSON.parse(response); }
            catch (e) { console.error(e); }
            
            if (!response.success) {
                alert(response.error); return false;
            }
            
            state.times = response.times;
            renderDropdown( state.times );
            updateChildren( state.times[0].key, state.times[0].type );
            // refresh every 5 seconds....
            setInterval(function(){
                if (!debug) {
                    updateChildren( $("select#timeDropdown").val() );
                }
            }, 5000);
            //setupQuagga();
        });
    }
    
    function renderDropdown( options ) {
        rendered_options = "";
        for( var i = 0; i < options.length; i++ ) {
            var option = options[i];
            rendered_options += "<option value='" + option.key + "' data-type='" + option.type + "'>" + option.type + '-' + option.description + "</option>";
        }
        
        $("select#timeDropdown").html( rendered_options );
        // $("select#timeDropdown").change( function(event) {
        //     updateChildren(event.target.value);
        // } );
    }
    
    function updateChildren( time_id, type ){
        $.post("api/getMarkingDetails.php", { login: state.login, time_id: time_id, type: type, groups: state.selectedGroups }, function (response) {
            console.log( response );
            response = JSON.parse(response);
            
            if (!response.success) {
                alert(response.error); return false;
            }
            
            renderChildren( response.marks, response.type );
        });
    }
    
    function renderChildren( children, type ) {
        rendered_html = "";
        for( var i = 0; i < children.length; i++ ) {
            var child = children[i];
            if (type == "chap") {
                rendered_html += new chap_item( child, type ).render();
            } else {
                rendered_html += new child_item( child, type ).render();
            }
        }
        
        $("#child-list").html( rendered_html );
        $("#app label input.mark").change( updateMark );
    }
    
    function updateMark( event ) {
        var data = {
            login:  state.login,
            time_id: $("select#timeDropdown").val(),
            chidon_id: event.target.dataset.th_chidon_id,
            checked: event.target.checked
        };
        
        $.post("api/markChild.php", data, function( response) {
            response = JSON.parse(response);
            if (!response.success) {
                event.target.checked = !event.target.checked;
            }
        });
    }
    
    return {
        login:      login,
        logout:     logout,
        setupApp:   setupApp
    };
}();

//********************** SINGLE CHILD COMPONENT ***********************//
var child_item = function( props, type ) {
    this.props = props;
    this.type = type;
};

child_item.prototype.render = function() {
    var html = '<label class="child">';
    html +=     '<div class="box">';
    html +=         '<div class="user-info">';
    html +=             '<input type="checkbox" ' + (this.props.marked ? "checked" : "") + ' data-th_chidon_id="' + this.props.th_chidon_id + '" class="mark"/>';
    html +=             '<span class="checkbox"></span>';
    html +=             '<span class="name"> ' + this.props.first + ' ' + this.props.last + ' </span><br/>';
    if (this.type === "door")
        html +=         'School: ' + this.props.school_name + "<br/>";
    html +=             'Chidon #' + this.props.th_chidon_id + "<br/>";
    html +=             'Serial #' + this.props.user_serial + "<br/>";
    if (this.type === "walk")
        html +=         'Walking Zone #' + this.props.walking_zone + "<br/>";
    html +=         '</div>';
    if (this.type === "walk") {
        html +=     '<div class="info">';
        html +=         'Host Family: ' + this.props.host + "<br/>";
        html +=         'Address: <strong>' + this.props.host_street_num + " " + this.props.host_street_num_suffix + " " + this.props.host_street + " " + this.props.host_street_apt + " <br class='break-sm'/>";
        html +=         'Between ' + this.props.between_streets1 + "</strong><br/>";
        html +=         'Host Phone Number: ' + this.props.host_number + "<br/>";
        html +=     '</div>';
    }
    if (this.type === "door") {
        html +=     '<div class="info">';
        html +=         'Host Family: ' + this.props.host + " - " + this.host_number + "<br/>";
        html +=         'Walking Chaperone: ' + this.props.chap_name + " - " + this.props.chap_phone + "<br/>";
        html +=     '</div>';
    }
    html +=         '<div class="clearfix"></div>';
    html +=     "</div>";
    html += "</label>";
    
    return html;
};

var chap_item = function( props ) {
    this.props = props;
};

chap_item.prototype.render = function() {
    var html = '<label class="chap">';
    html +=     '<div class="box">';
    html +=         '<div class="chap-info">';
    html +=             '<input type="checkbox" ' + (this.props.marked ? "checked" : "") + ' data-th_chidon_id="' + this.props.th_chidon_chap_id + '" class="mark"/>';
    html +=             '<span class="checkbox"></span>';
    html +=             '<span class="name"> ' + this.props.name + ' </span><br/>';
    html +=             'Chap ID #' + this.props.th_chidon_chap_id + "<br/>";
    html +=             'Walking Zone #' + this.props.walking_zone + "<br/>";
    html +=         '</div>';
    html +=         '<div class="info">';
    html +=             'School: ' + this.props.school_name + "<br/>";
    html +=             'Phone Number: ' + this.props.phone + "<br/>";
    html +=             'E-mail: ' + this.props.email + "<br/>";
    html +=         '</div>';
    html +=         '<div class="clearfix"></div>';
    html +=     "</div>";
    html += "</label>";
    
    return html;
};

function register_service_worker() {
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
          navigator.serviceWorker.register('/chidon/attendance/service_worker.js').then(function(registration) {
            // Registration was successful
            console.log('ServiceWorker registration successful with scope: ', registration.scope);
          }, function(err) {
            // registration failed :(
            console.log('ServiceWorker registration failed: ', err);
          });
        });
    }
}

function setupQuagga() {
    // start the scanner
    Quagga.init({
        inputStream: {
            name : "Live",
            type : "LiveStream",
            target: "#barcode_scanner",
            constraints: {
                // width: {min: 640, ideal: 1280, max: 1920},
                // height: {min: 480, ideal: 720, max: 1280},
                // aspectRatio: {min: 1, max: 100},
                width: 200, 
                height: 300, 
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
            console.log( error );
            $("#barcode_scanner").hide();
            return;
        } else {
            $("#manual_scanner").hide();
            $("#barcode_scanner").show();
            // $("#barcode_scanner").addClass("show");
            // $(".container.body").addClass("shrink");
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

    // on page load
    setupScanner( "environment" );
    //$("#toggle-manual").click( toggleManual );
    // detect the info on the scanner input
    $("#manual-scanner #scanner").keyup( function( event ) {
        if ( event.target.value.match(/^3{1}\d{19}$/) ) {
            checkNumber( event.target.value );
        } else if ( event.target.value.length === 20 ) {
            showError( "Please enter a valid barcode" );
        } else if ( event.target.value.length > 20) {
            event.target.value = event.target.value.slice(0, 20);
        }
    });

    $("#cardForm").submit( function( event ){
        event.preventDefault();
        checkNumber( $('#scanner').val() )
    });

    Quagga.onDetected( function( data ) {
        if ( !checkNumber( data.codeResult.code ) ) {
            setTimeout( function() {
                showError( "Sorry, it seems we could not read the card properly. Please try another angle." )
            }, 1000 );
        }
    });

    Quagga.onProcessed( showScanningBox );

    // check the number as a user posts it
    function checkNumber( cardNumber ) {
        if ( cardNumber.match(/^3{1}\d{19}$/) ) {
            //$.post( 'api/checkID.php', { card : cardNumber }, login );
            alert( cardNumber );
        } else {
            console.error( "Invalid. Detected: ", cardNumber );
        }
    }

    // show errors to the user in a nice, async way
    function showError( error ) {
        $('#errorModal .modal-body p').text( error );
        $('#errorModal').modal('show');
        return false;
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

    // setup the quaggajs scanner
    function setupScanner( mode ){
        mode = mode ? mode : "environment"; // or user
        var config = {
            inputStream: {
                name : "Live",  type : "LiveStream",    target: "#barcode_scanner",
                constraints: {
                    // width: {min: 640, ideal: 1280, max: 1920},
                    // height: {min: 480, ideal: 720, max: 1280},
                    // aspectRatio: {min: 1, max: 100},
                    width: 200,
                    height: 300,
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
            multiple: true
        };

        var setup = function( error ){
            if ( error ) {
                // showError( "Sorry, it seems we cannot scan cards on your device. Please enter in the card number by hand." );
                $("#barcode_scanner").hide();
                $("#manual-scanner").show(); // show the manual scanner
                $('#manual-scanner #scanner').focus(); // focus for barcode readers
                // setup the listener
            } else {
                //$("#toggle-manual").show();
                $("#manual-scanner").hide();
                $("#barcode_scanner").show();
                console.log( "Quagga JS initialized. Ready to start Scanning Cards" );
                Quagga.start();
            }
        }

        Quagga.init( config, setup );
    }

    // function toggleManual( event ){
    //     if ( $("#manual-scanner").is(":visible") ){
    //         $("#toggle-manual img").attr("src", "/mobile/img_new/scanner-2-color-red-svg.svg" );
    //         $("#manual-scanner").hide();
    //         $("#barcode_scanner").show();
    //     } else {
    //         $("#toggle-manual img").attr("src", "/mobile/img_new/camera-color-red-svg.svg");
    //         $("#manual-scanner").show();
    //         $("#barcode_scanner").hide();
    //     }
    // }
}