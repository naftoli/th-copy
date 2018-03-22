// on page load
$( document ).ready( function(){
    loadTable( false, true );
    // event listeners
    $("select#grade_dropdown").change( loadTable );

    // load and render the table from the server
    function loadTable( event, setTotals ) {
        var postData = {
            level: 1
        }

        if ( event && event.target.dataset.level ) {
            postData.level      = event.target.dataset.level;
            postData.school_id  = event.target.dataset.school_id;
            postData.class_id   = event.target.dataset.class_id;
        } else if ( event && event.target.value ) {
            postData.grade = event.target.value;
        }

        $.post("api/getLines.php", postData, function( response ) {
            response = JSON.parse( response );
            // setup the table head
            var html = '<table id="report-table" class="table table-striped table-bordered table-hover sortable style="width:100%"">';
            html += '<thead><tr>'
            html += '<th class="school">Name</th>';
            html += '<th>Lines of<br />תניא בעל פה</th>';
            html += '<th id="defaultSort">Avg per Child</th>';
            html += '<th>Lines of<br />משניות בעל פה</th>';
            html += '<th>Avg per Child</th>';
            html += '</tr></thead>';
            html += '<tbody>';
            // render each row
            for( var index = 0; index < response.length; index ++) {
                var row = response[index];

                html += "<tr>";

                html += '<td>' + row.name + '</td>';
                html += '<td>' + row.campaigns.tanya.learned + '</td>';
                html += '<td>' + row.campaigns.tanya.avg + '</td>';
                html += '<td>' + row.campaigns.mishna.learned + '</td>';
                html += '<td>' + row.campaigns.mishna.avg + '</td>';

                html += "</tr>";
            }
            // close the table
            html += '</tbody>';
            html += '</table>';

            $("#report").html( html );
            $('#report-table').DataTable({
                "order": [[ 2, "desc" ]],
                "language": { "decimal": "," }
            } );
        });
    }
});