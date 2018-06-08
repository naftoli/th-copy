// polyfill for [].find
Array.prototype.find=Array.prototype.find||function(r){if(null===this)throw new TypeError("Array.prototype.find called on null or undefined");if("function"!=typeof r)throw new TypeError("callback must be a function");for(var n=Object(this),t=n.length>>>0,o=arguments[1],e=0;e<t;e++){var f=n[e];if(r.call(o,f,e,n))return f}};

var registration_info = function(){

    var state = { schools: [] };

    function loadTable(){
        $('#report').html('<div class="loader"></div>');
        $.get( '/api/registration/school_configuration.php', function( response ){
            state.schools = response.data;
            renderTable();
        });
    }

    loadTable();
    $('#year').change(loadTable);

    function renderTable(){
        var year = $("#year").val() || 5779;
        
        var html = '<table><tbody>';
        html += '<tr><th>Base Name</th><th>Type</th><th>Fee</th><th>Balance</th><th>Deadline</th><th>Early Bird</th><th>Saved</th></tr>'
        state.schools.forEach( function( school ) {
            // if we do not have one, set the defaults
            var reg_info = 
                school.school_reg_infos.find( function(info) { return info.year == year } ) || 
                { school_reg_info_id: 0, type: 0, fee: 770, balance: 0, 
                    reg_deadline: '', early_bird: '2018-09-07', defaults: true };
            // render the row
            html += '<tr data-school_reg_info_id="' + reg_info.school_reg_info_id + '" data-school_id="' + school.school_id + '">'
            html += '<td>' + school.school_name + '</td>';

            html += '<td>' + formatType(reg_info.type) + '</td>';
            html += '<td>' + formatNumber(reg_info.fee, 'fee') + '</td>';
            html += '<td>' + formatNumber(reg_info.balance, 'balance') + '</td>';
            html += '<td>' + formatDate(reg_info.reg_deadline, 'reg_deadline', reg_info.type != 2) + '</td>';
            html += '<td>' + formatDate(reg_info.early_bird, 'early_bird') + '</td>';

            html += '<td class="saved">' + ( reg_info.defaults ? "No" : "Yes" ) + '</td>';

            html += '<td><button class="button save-row">Save</button></td>';

            html += '</tr>';
        });

        html += '</tbody></table>';
        $("#report").html( html );
        $("select[name='type']").change( toggleDeadline );
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
        var html = '<select name="type"">';
        html += '<option value="0" ' + ( type == 0 ? 'selected' : '') + ' disabled>N/A</option>';
        html += '<option value="1" ' + ( type == 1 ? 'selected' : '') + '>In Tuiton</option>';
        html += '<option value="2" ' + ( type == 2 ? 'selected' : '') + '>Guaranteed</option>';
        html += '<option value="3" ' + ( type == 3 ? 'selected' : '') + '>By Parent</option>';
        html += '</select>';
        return html;
    }

    function toggleDeadline( event ){
        $(event.target).parent().parent().find(
            'input[name="reg_deadline"]"'
        )[0].disabled = event.target.value != '2';
        $(event.target).parent().parent().find( 'td.saved' ).text("No");
    }

    function toggleSaved( event ){
        $(event.target).parent().parent().find( 'td.saved' ).text("No");
    }

    function saveRow( event ){
        $(event.target).text( "Saving..." );
        var row = $(event.target).parent().parent();
        var id = row[0].dataset.school_reg_info_id;
        var year = $("#year").val() || 5779;

        var postData = {
            year: year,
            school_id: row[0].dataset.school_id,
            type:   row.find( 'select[name="type"]' ).val(),
            fee:   row.find( 'input[name="fee"]' ).val(),
            balance:   row.find( 'input[name="balance"]' ).val(),
            reg_deadline:   row.find( 'input[name="reg_deadline"]' ).val(),
            early_bird:   row.find( 'input[name="early_bird"]' ).val(),
        }

        var url = "/api/registration/school_configuration.php";
        url = id == '0' ? url : url + '?id=' + id;

        function handleResponse( response ){
            $(event.target).text( "Save" );
            if( !response.success ) {
                return alert( response.msg + '\n\n' + response.data.join('\n') )
            }
            row.find( 'td.saved' ).text("Yes");
            row[0].dataset.school_reg_info_id = response.data.school_reg_info_id;
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

