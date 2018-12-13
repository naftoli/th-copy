var gtag; // make sure gtag is a defined variable

var toggle =  function(){
    var on = false;

    return function() {
        if ( on ) {
            $('#social-dropdown-link').css({ width: 0, opacity: 0 });
            $('#browser-share').css({ width: '36px', opacity: 1 });
        } else {
            $('#social-dropdown-link').css({ width: '36px', opacity: 1 });
            $('#browser-share').css({ width: 0, opacity: 0 });
        }
        
        on = !on;
    }
}();

// on page load
$( document ).ready( function(){
    loadTable( false );
    setInterval( loadTable, 300000); // refresh the table every 3 seconds
    // reload the table when the link at the top of the page changes
    $( window ).on( 'hashchange', function() {
        gtag('config', 'UA-116474327-1', {'page_path': '/' + window.location.hash });
        loadTable();
    }); // refresh the table when the hash changes
    $("select#grade_dropdown").change( refreshTable );
    // clear the table on the page
    $("button#refresh-table").click( refreshTable );
    // log a share event in google anylytics
    $("#social-dropdown-link").click( function(){
        gtag('event', 'share');
    });

    // if the share function is supported and we are on an SSL connection
    if ( navigator.share && location.protocol === 'https:' ) {
        $('#browser-share').css( 'max-width', '500px' );
        $('#browser-share i').css({ width: '36px', opacity: 1, transform: 'scale(1)' });
        // use navigator.share when the click
        $('#browser-share').click( function() {
            navigator.share({
                title: 'Our Birthday Gift',
                text: 'See the amazing gift the Chayolim gave the Rebbe for his 117th birthday at ourBirthdayGift.com',
                url: 'https://ourbirthdaygift.com',
            }).then( console.log )
            .catch( console.error );
        });
    } else {
        $('#social-dropdown-link').css( 'max-width', '500px' );
        $('#social-dropdown-link i').css({ width: '36px', opacity: 1, transform: 'scale(1)' });
    }

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

    function getColor( total ) {
        if (total <= 0 ) {
            return ""; // no color
        } else if ( total <= 29 ) {
            return "red";
        } else if ( total <= 49 ) {
            return "bronze";
        } else if ( total <= 99) {
            return "silver";
        } else {
            return "gold";
        }
    }

    // load and render the table from the server
    function loadTable( event ) {
        $("button#refresh-table .fa-sync").addClass( "fa-spin" );
        var hash_params = window.location.hash.replace("#", "").split("-");
        var postData = {
            level: 1
        };

        if ( hash_params.length === 1 ) {
            postData.grade = hash_params[0] ? hash_params[0] : $("select#grade_dropdown").val();
            $("select#grade_dropdown").val( postData.grade );
        } else if ( hash_params.length === 2 ) {
            postData.level  = parseInt ( hash_params[0] );
            postData.id     = hash_params[1];
        }

        $.post("api/getLines.php", postData, function( response ) {
            response = JSON.parse( response );
            // setup the table head
            var html = '<table id="report-table" class="table table-striped table-bordered table-hover sortable style="width:100%"">';
            html += '<thead><tr><th></th>';
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
                html +=     '<td class="first">';
                if ( postData.level == 1 ){
                    html += "<img class='school_logo' alt='school_logo' src='//mashpia.com/schoolLogos/" + 
                        ( row.logo ? row.logo : "_logo.png" ) + "'/>";
                }
                html += '<span class="number"></span>';
                html += '</td><td>';

                if ( postData.level < 3 && !row.lastLevel ) {
                    html +=         '<a class="id_link" href="#' + nextLevel + '-' + row.id  + '" >';
                    html +=             '<span class="school_name">' + row.name + '</span>';
                    html +=         '</a>';
                } else {
                    html += '<span class="school_name">' + row.name + '</span>';
                }
                // get the averages
                var tanya_avg = row.campaigns.tanya.avg;
                var mishna_avg = row.campaigns.mishna.avg;

                html +=     '</td>';
                html +=     '<td class="total">' + row.campaigns.tanya.learned + '</td>';
                html +=     tanya_avg !== undefined ? '<td class="total ' + getColor( tanya_avg ) + '">' + tanya_avg + '</td>' : "";
                html +=     '<td class="total">' + row.campaigns.mishna.learned + '</td>';
                html +=     mishna_avg !== undefined ? '<td class="total ' + getColor( mishna_avg ) + '">' + mishna_avg + '</td>' : "";
                // if the level is not 3, calculate and render the total average for both tanya and mishna combined
                if ( postData.level !== 3 ) {
                    html += '<td class="total">';
                    html += Math.floor( 
                        ( row.campaigns.tanya.learned + row.campaigns.mishna.learned ) / row.child_count 
                    );
                    html += '</td>';
                }
                html += "</tr>";
            }
            // close the table
            html += '</tbody>';
            html += '<tfoot><tr>';
            html += '<th>Total:</th>';
            html += '<th></th><th></th><th></th>';
            if ( postData.level !== 3 ) html += '<th></th><th></th><th></th>';
            html += '</tfoot></table>';
            // stop the spinner
            $("button#refresh-table .fa-sync").removeClass( "fa-spin" );
            // add the html to the page
            $("#report").html( html );
            // setup the datatable
            var reportTable = $('#report-table').DataTable({
                "order": [[ ( postData.level !== 3 ? 3 : 2 ), "desc" ]],
                "language": { "decimal": "," },
                "lengthMenu": [ [-1, 10, 25, 50, 100], ["All", 10, 25, 50, 100] ],
                "footerCallback": totalRow
            } );
            // show index column as per docs here: https://datatables.net/examples/api/counter_columns.html
            reportTable.on( 'order.dt search.dt', function () {
                reportTable.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                    $( cell ).find( ".number" ).html( i + 1 );
                } );
            } ).draw();
        });
    }

    // calculate the totals for all the rows and update the footer.
    // use this function to update main numbers as well if we decide to do so
    function totalRow( row, data, start, end, display ) {
        mishna_index = $( row ).find("th").length == 4 ? 3 : 4; // get the correct column for the mishna

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
        updateTotals(total);
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