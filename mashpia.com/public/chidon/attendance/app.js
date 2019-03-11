// scope app so that we control it's internal contents.....
var debug = false;
var app = function() {
    
    var state = {
        login: false,
        user: {} // user that is logged in....
    };
    
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
                $("#user-name").text(state.user.name);
            }
        });
    }
    
    function setupApp() {
        $.post( "api/getMarkingOptions.php", { login: state.login }, function( response ) {
            try { response = JSON.parse(response); }
            catch (e) { console.error(e); }
            
            if (!response.success) {
                alert(response.error); return false;
            }
            
            state.times = response.times;
            renderDropdown( state.times );
            updateChildren( state.times[0].key );
            // refresh every 5 seconds....
            // setInterval(function(){
            //     if (!debug) {
            //         updateChildren( $("select#timeDropdown").val() );
            //     }
            // }, 5000);
        });
    }
    
    function renderDropdown( options ) {
        rendered_options = "";
        for( var i = 0; i < options.length; i++ ) {
            var option = options[i];
            rendered_options += "<option value='" + option.key + "' data-type='" + option.type + "'>" + option.description + "</option>";
        }
        
        $("select#timeDropdown").html( rendered_options );
        $("select#timeDropdown").change( function(event) {
            updateChildren(event.target.value);
        } );
    }
    
    function updateChildren( time_id ){
        $.post("api/getMarkingDetails.php", { login: state.login, time_id: time_id }, function (response) {
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
        html +=         'Address: <strong>' + this.props.host_address1 + " " + this.props.host_address2 + " <br class='break-sm'/>";
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