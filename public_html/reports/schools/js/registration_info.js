// polyfill for [].find
Array.prototype.find=Array.prototype.find||function(r){if(null===this)throw new TypeError("Array.prototype.find called on null or undefined");if("function"!=typeof r)throw new TypeError("callback must be a function");for(var n=Object(this),t=n.length>>>0,o=arguments[1],e=0;e<t;e++){var f=n[e];if(r.call(o,f,e,n))return f}};

var registration_info = function(){

    var state = { schools: [] };

    $.get( '/api/registration/school_registration.php', function( response ){
        state.schools = response.data;
        renderSchoolsTable()
    });

    function renderSchoolsTable(){
        var year = $("#year").val() || 5779;
        
        var html = '<table><tbody>';
        html += '<tr><th>Base Name</th><th>Type</th><th>Fee</th><th>Balance</th><th>Deadline</th><th>Early Bird</th></tr>'
        state.schools.forEach( function( school ) {
            // if we do not have one, set the defaults
            var reg_info = 
                school.school_reg_infos.find( function(info) { return info.year == year } ) || 
                { school_reg_info_id: 0, type: 0, fee: 770, balance: 0, reg_deadline: '', early_bird: '' };
            // render the row
            html += '<tr data-school_reg_info_id="' + reg_info.school_reg_info_id + '" data-school_id="' + school.school_id + '">'
            html += '<td>' + school.school_name + '</td>';

            html += '<td>' + formatType(reg_info.type) + '</td>';
            html += '<td>' + formatNumber(reg_info.fee, 'fee') + '</td>';
            html += '<td>' + formatNumber(reg_info.balance, 'balance') + '</td>';
            html += '<td>' + formatDate(reg_info.reg_deadline, 'reg_deadline', reg_info.type != 2) + '</td>';
            html += '<td>' + formatDate(reg_info.early_bird, 'early_bird') + '</td>';

            html += '<td><a class="button save-row">Save</a></td>';

            html += '</tr>'
        });

        html += '</tbody></table>';
        $("#report").html( html );
        $("select[name='type']").change( toggleDeadline );
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
    }

    return {
        getState: () => { return state }
    }
}();

