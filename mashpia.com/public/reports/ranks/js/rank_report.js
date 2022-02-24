var clipboard = new ClipboardJS('.btn');

clipboard.on('success', function(e) {
    alert( "Copied to clipboard!" );
    e.clearSelection();
});

clipboard.on('error', function(e) {
    console.error('Action:', e.action);
    console.error('Trigger:', e.trigger);
    alert( "Could not copy to clipboard" );
});

$("#options").submit( function( event ){
    event.preventDefault();
    
    $("#report").html( '<div class="loader"></div>' );
    
    var postData = {
        from: $("#from").val(),
        to: $("#to").val()
    };

    $.post( "ajax/rank_report.php", postData, function( response ){
        $("#report").html( response );
        if(typeof Promise !== "undefined" && Promise.toString().indexOf("[native code]") !== -1){
            // downloadImages();
        } else {
            document.querySelector("#zip-images").innerHTML = "Unable to zip images. Please use Chrome or Firefox"
        }
    })
} );

function downloadImages() {
    var zip = new JSZip();
    var a = document.querySelector("#zip-images");
    // find all profile pictures on the page
    var urls = [];
    $.each( $("#breakdown a.profile"), function( index, profile ) {
        var img = profile.dataset.profile;
        var name = ( index + 1 ) + "_" + profile.innerText.split(" ").join("_");
        // add the extension
        if ( img.match("mobile/reg/img") ) {
            name += "." + img.split(".").slice(-1)[0];
        } else {
            name += ".jpg"
        }
        urls.push( { src: img, name: name } );
    });
    // load the url into the zip folder
    function request( url ){
        return new Promise( function( resolve ) {
            var httpRequest = new XMLHttpRequest();
            httpRequest.open( "GET", url.src );
            httpRequest.responseType = "blob"
            httpRequest.onload = function() {
                zip.file( url.name, this.response );
                resolve();
            }
            httpRequest.onerror = function() {
                resolve(); // resolve without adding the file
            }
            httpRequest.send();
        })
    }

    Promise.all(urls.map(function(url) {
        return request(url)
    }))
    .then(function() {
        zip.generateAsync({
            type: "blob"
        })
        .then(function(content) {

            var from = new Date( $( "#from" ).val() );
            from = ( from.getMonth() + 1 ) + "-" + ( from.getDate() + 1);
            var to   = new Date( $( "#to" ).val() );
            to = ( to.getMonth() + 1 ) + "-" + ( to.getDate() + 1);

            a.download = "general_profiles_" + from + "_to_" + to ;
            a.href = URL.createObjectURL(content);
            a.innerHTML = "Download General Profile Images (Zip)"
        });
    })
}

function downloadCSV() {
    let info = JSON.parse($("#data").text())
    let headers = ['Rank', 'Name']
    let rows = []
    for (rank in info) {
        for (name of info[rank]) {
            rows.push([rank, name])
        }
    }
    // generate the csv content
    const universalBOM = "\uFEFF";
    let csvContent = `${headers.join(',')}\n`;
    // Add each row to the CSV content and encode it for unicode in excel
    rows.forEach(row => {
        csvContent += `${row.join(',')}\n`
    });
    csvContent = encodeURIComponent(universalBOM + csvContent);
    // create and click the download link
    let link = document.createElement('a');
    link.href = `data:text/csv;charset=utf-8,${csvContent}`;
    console.log(link.href)
    link.download = `ranks.csv`;
    link.click();
}