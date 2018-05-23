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
            downloadImages();
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
    $.each( $("#breakdown img.profile"), function( index, img ) {
        var name = img.dataset.name;
        // add the extension
        if ( img.src.match("mobile/reg/img") ) {
            name += "." + img.src.split(".").slice(-1)[0];
        } else {
            name += ".jpg"
        }
        urls.push( { src: img.src, name: name } );
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
            a.innerHTML = "Download Full Profile Images"
        });
    })
}