// on page load
$( document ).ready( function(){
    loadTable( false );
    setInterval( loadTable, 300000); // refresh the table every 3 seconds
    // reload the table when the link at the top of the page changes
    $( window ).on( 'hashchange', loadTable ); // refresh the table when the hash changes
    $("select#grade_dropdown").change( refreshTable );
    // clear the table on the page
    $("button#refresh-table").click( refreshTable );

    function refreshTable() {
        // get the current grade
        var current_grade = $("select#grade_dropdown").val();
        // if we are on the current grade
        if ( window.location.hash.replace("#", "") == current_grade ) {
            loadTable(); // load the table
        } else { // otherwise, change the hash and trigger a reload
            window.location.hash = current_grade;
        }
    }

    // load and render the table from the server
    function loadTable( event ) {
        $("button#refresh-table .fa-sync").addClass( "fa-spin" )
        var hash_params = window.location.hash.replace("#", "").split("-");
        var postData = {
            level: 1
        }

        if ( hash_params.length === 1 ) {
            postData.grade = hash_params[0] ? hash_params[0] : $("select#grade_dropdown").val();
            $("select#grade_dropdown").val( postData.grade );
        } else if ( hash_params.length === 2 ) {
            postData.level  = parseInt ( hash_params[0] )
            postData.id     = hash_params[1]
        }

        $.post("api/getLines.php", postData, function( response ) {
            response = JSON.parse( response );
            // setup the table head
            var html = '<table id="report-table" class="table table-striped table-bordered table-hover sortable style="width:100%"">';
            html += '<thead><tr><th></th>'
            html += '<th class="school">Name</th>';
            html += '<th>Lines of<br />תניא בעל פה</th>';
            html += ( postData.level !== 3 ? '<th id="defaultSort">Avg תניא Lines</th>' : "" );
            html += '<th>Lines of<br />משניות בעל פה</th>';
            html += ( postData.level !== 3 ? '<th>Avg משניות Lines</th>' : "" );
            html += ( postData.level !== 3 ? '<th>Avg Total Lines</th>' : "" );
            html += '</tr></thead>';
            html += '<tbody>';
            // render each row
            for( var index = 0; index < response.length; index ++) {
                var row = response[index];
                var nextLevel =  postData.grade ? 3 : parseInt( postData.level ) + 1;

                html += "<tr>";
                html +=     '<th></td><td>'

                if ( postData.level < 3 && !row.lastLevel ) {
                    html +=         '<a class="id_link" href="#' + nextLevel + '-' + row.id  + '" >'
                    html +=             row.name
                    html +=         '</a>';
                } else {
                    html +=     row.name
                }
                
                html +=     '</td>';
                html +=     '<td>' + row.campaigns.tanya.learned + '</td>';
                html +=     row.campaigns.tanya.avg !== undefined ? '<td>' + row.campaigns.tanya.avg + '</td>' : "";
                html +=     '<td>' + row.campaigns.mishna.learned + '</td>';
                html +=     row.campaigns.mishna.avg !== undefined ? '<td>' + row.campaigns.mishna.avg + '</td>' : "";
                html +=     postData.level !== 3  ? '<td>' + 
                                Math.floor( 
                                    (row.campaigns.tanya.learned + row.campaigns.mishna.learned) / row.child_count 
                                ) +
                            '</td>' : "";
                html += "</tr>";
            }
            // close the table
            html += '</tbody>';
            html += '<tfoot><tr>';
            html += '<th>Total:</th>';
            html += '<th></th><th></th>';
            if ( postData.level !== 3 ) html += '<th></th><th></th>';
            html += '</tfoot></table>';
            // stop the spinner
            $("button#refresh-table .fa-sync").removeClass( "fa-spin" )
            // add the html to the page
            $("#report").html( html );
            // setup the datatable
            var reportTable = $('#report-table').DataTable({
                "order": [[ ( postData.level !== 3 ? 2 : 1 ), "desc" ]],
                "language": { "decimal": "," },
                "lengthMenu": [ [-1, 10, 25, 50, 100], ["All", 10, 25, 50, 100] ],
                "footerCallback": totalRow
            } );
            // show index column as per docs here: https://datatables.net/examples/api/counter_columns.html
            reportTable.on( 'order.dt search.dt', function () {
                reportTable.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                    cell.innerHTML = i + 1;
                } );
            } ).draw();
        });
    }

    // calculate the totals for all the rows and update the footer.
    // use this function to update main numbers as well if we decide to do so
    function totalRow( row, data, start, end, display ) {
        mishna_index = $( row ).find("th").length == 3 ? 3 : 4; // get the correct column for the mishna

        var api = this.api(), data;

        var intVal = function ( i ) {
            return typeof i === 'string' ?
                i.replace(/[\$,]/g, '')*1 :
                typeof i === 'number' ?
                    i : 0;
        };
        // objects to store the totals in
        total = {}; pageTotal = {};

        // calcuate the tanya totals
        total.tanya = api
            .column( 2 ).data()
            .reduce( function (a, b) {
                return intVal(a) + intVal(b);
            }, 0 );
        pageTotal.tanya = api
            .column( 2, { page: 'current'} ).data()
            .reduce( function (a, b) {
                return intVal(a) + intVal(b);
            }, 0 );

        // calcuate the mishna totals
        total.mishna = api
            .column( mishna_index ).data()
            .reduce( function (a, b) {
                return intVal(a) + intVal(b);
            }, 0 );
        pageTotal.mishna = api
            .column( mishna_index, { page: 'current'} ).data()
            .reduce( function (a, b) {
                return intVal(a) + intVal(b);
            }, 0 );

        // update the footer
        $( api.column( 2 ).footer() ).html(
            pageTotal.tanya + ( pageTotal.tanya !== total.tanya ? ' / ' + total.tanya : "" )
        );
        $( api.column( mishna_index ).footer() ).html(
            pageTotal.mishna + ( pageTotal.mishna !== total.mishna ? ' / ' + total.mishna : "" )
        );

        // update the totals on the top of the page
        total.total = total.tanya + total.mishna;
        updateTotals(total)
    }

    // load and render the totals
    function updateTotals( totals, duration ) {
        duration = duration ? parseInt( duration ) : 2; // set the default duration to 2

        // countup the grand total
        var grand_total = parseInt( $("#grand_total").text().replace(",", "") );
        new CountUp("grand_total",  grand_total,  totals.total,  0, duration).start();
        // countup the tanya total
        var tanya_total = parseInt( $("#tanya_total").text().replace(",", "") );
        new CountUp("tanya_total",  tanya_total,  totals.tanya,  0, duration).start();
        // countup the mishna total
        var mishna_total = parseInt( $("#mishna_total").text().replace(",", "") );
        new CountUp("mishna_total", mishna_total, totals.mishna, 0, duration).start();
    }
});