// polyfill for [].find
Array.prototype.find=Array.prototype.find||function(r){if(null===this)throw new TypeError("Array.prototype.find called on null or undefined");if("function"!=typeof r)throw new TypeError("callback must be a function");for(var n=Object(this),t=n.length>>>0,o=arguments[1],e=0;e<t;e++){var f=n[e];if(r.call(o,f,e,n))return f}};

var registration_info = function(){

    var state = { schools: [] };

    function loadTable(){
        $('#report').html('<div class="loader"></div>');
        var postData = {
            year: $("#year").val() || 5779
        }
        $.get( '/api/registration/school_configuration.php', postData, function( response ){
            state.schools = response.data;
            renderTable();
        });
    }

    loadTable();
    $('#year').change(loadTable);

    function renderTable(){
        var html = '<table><tbody>';
        html += '<tr><th>Base Name</th><th>Type</th><th>Base Fee</th><th>Balance</th><th>Soldier Fee</th><th>Early Bird / Deadline</th><th>Live</th></tr>'
        state.schools.forEach( function( school ) {
            var reg_info = school.reg_info;
            reg_info.school_registration_id = reg_info.school_registration_id || '';
            // render the row
            html += '<tr data-school_registration_id="' + reg_info.school_registration_id + '" data-school_id="' + school.school_id + '">'
            html += '<td>' + school.school_name + '</td>';

            html += '<td>' + formatType(reg_info.type) + '</td>';
            html += '<td>' + formatNumber(reg_info.fee, 'fee') + '</td>';
            html += '<td>' + formatNumber(reg_info.balance, 'balance') + '</td>';
            html += '<td>' + formatNumber(reg_info.child_fee, 'child_fee') + '</td>';
            html += '<td>' + formatDate(reg_info.early_bird, 'early_bird') + '</td>';

            html += '<td class="saved">' + ( reg_info.default ? "No" : "Yes" ) + '</td>';

            html += '<td><button class="button save-row">Save / Make Live</button></td>';

            html += '</tr>';
        });

        html += '</tbody></table>';
        $("#report").html( html );
        $("select[name='type']").change( toggleSaved );
        $("#report input").change( toggleSaved );
        $("#report .save-row").click( saveRow );
    }

    function formatDate( date, name, disabled ){
        date = date ? date : '';
        return '<input type="date" name="' + name + '" value="' + date.split('T')[0] + '" ' +  
            ( disabled ? "disabled='true'" : "") + '"/>';
    }

    function formatNumber( number, name ){
        number = number ? number : 0;
        return '<input type="number" name="' + name + '" value="' + number + '"/>';
    }

    function formatType( type ) {
        type = type ? type : 0;
        var html = '<select name="type">';
        html += '<option value="0" ' + ( type == 0 ? 'selected' : '') + ' disabled>N/A</option>';
        html += '<option value="1" ' + ( type == 1 ? 'selected' : '') + '>In Tuiton</option>';
        html += '<option value="2" ' + ( type == 2 ? 'selected' : '') + '>Guaranteed</option>';
        html += '<option value="3" ' + ( type == 3 ? 'selected' : '') + '>By Parent</option>';
        html += '</select>';
        return html;
    }

    function toggleSaved( event ){
        $(event.target).parent().parent().find( 'td.saved' ).text("No");
    }

    function saveRow( event ){
        $(event.target).text( "Saving..." );
        var row = $(event.target).parent().parent();
        var id = row[0].dataset.school_registration_id;
        var year = $("#year").val() || 5779;

        var postData = {
            year: year,
            school_id: row[0].dataset.school_id,
            type:   row.find( 'select[name="type"]' ).val(),
            fee:   row.find( 'input[name="fee"]' ).val(),
            balance:   row.find( 'input[name="balance"]' ).val(),
            child_fee:   row.find( 'input[name="child_fee"]' ).val(),
            early_bird:   row.find( 'input[name="early_bird"]' ).val(),
        }

        var url = "/api/registration/school_configuration.php";
        url = id == '0' ? url : url + '?id=' + id;

        function handleResponse( response ){
            $(event.target).text( "Save / Make Live" );
            if( !response.success ) {
                return alert( response.error + '\n\n' + response.data.join('\n') )
            }
            row.find( 'td.saved' ).text("Yes");
            row[0].dataset.school_registration_id = response.data.school_registration_id;
        }

        $.ajax({
            url: url, 
            type: 'post',
            data: postData, 
            success: handleResponse,
            error: function( xhr ) { handleResponse( JSON.parse( xhr.response ) ) }
        });
    }

    return {
        getState: () => { return state }
    }
}();

