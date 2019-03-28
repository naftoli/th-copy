/**
 * Polyfill for toBlob: https://github.com/eligrey/canvas-toBlob.js/blob/master/canvas-toBlob.js
 * ( makes uploading the form work in IE ¯\_(ツ)_/¯ )
 */
!function(t){"use strict";var o,e=t.Uint8Array,n=t.HTMLCanvasElement,s=n&&n.prototype,i=/\s*;\s*base64\s*(?:;|$)/i,a="toDataURL";e&&(o=new e([62,-1,-1,-1,63,52,53,54,55,56,57,58,59,60,61,-1,-1,-1,0,-1,-1,-1,0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,-1,-1,-1,-1,-1,-1,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51])),!n||s.toBlob&&s.toBlobHD||(s.toBlob||(s.toBlob=function(t,n){if(n||(n="image/png"),this.mozGetAsFile)t(this.mozGetAsFile("canvas",n));else if(this.msToBlob&&/^\s*image\/png\s*(?:$|;)/i.test(n))t(this.msToBlob());else{var s,l=Array.prototype.slice.call(arguments,1),b=this[a].apply(this,l),r=b.indexOf(","),f=b.substring(r+1),B=i.test(b.substring(0,r));Blob.fake?((s=new Blob).encoding=B?"base64":"URI",s.data=f,s.size=f.length):e&&(s=B?new Blob([function(t){for(var n,s,i=t.length,a=new e(i/4*3|0),l=0,b=0,r=[0,0],f=0,B=0;i--;)s=t.charCodeAt(l++),255!==(n=o[s-43])&&void 0!==n&&(r[1]=r[0],r[0]=s,B=B<<6|n,4==++f&&(a[b++]=B>>>16,61!==r[1]&&(a[b++]=B>>>8),61!==r[0]&&(a[b++]=B),f=0));return a}(f)],{type:n}):new Blob([decodeURIComponent(f)],{type:n})),t(s)}}),!s.toBlobHD&&s.toDataURLHD?s.toBlobHD=function(){a="toDataURLHD";var t=this.toBlob();return a="toDataURL",t}:s.toBlobHD=s.toBlob)}("undefined"!=typeof self&&self||"undefined"!=typeof window&&window||this.content||this);

// make sure showError is a function
var showError;
if ( !showError ) showError = function(){}
/**
 * image_upload Handles uploading images using Cropper in a boostrap modal.
 */
var image_upload = function( options, uploadCallback ) {
    // set the defauls to the state
    var state = {
        file_name: 'unknown',
        file_input: options && options.file_input ? options.file_input : "#profile",
        modal:      options && options.modal  ? options.modal   : "#cropperModal",
        image:      options && options.image  ? options.image   : "#cropper-image",
        submit:     options && options.submit ? options.submit  : "#submit-image",
        uploadCallback: uploadCallback
    }

    $( state.file_input ).change( onFileSelected );
    $( state.modal ).on( 'hidden.bs.modal', resetModal );
    $( state.submit ).click( uploadImage );

    // setup event handlers for the buttons
    $( state.modal + " #cropper-rotate-right").click( rotateRight ); 
    $( state.modal + " #cropper-rotate-left").click( rotateLeft );
    $( state.modal + " #cropper-zoom-in").click( zoomIn );    
    $( state.modal + " #cropper-zoom-out").click( zoomOut );
    $( state.modal + " #cropper-scale-x" ).click( scaleX );   
    $( state.modal + " #cropper-scale-y" ).click( scaleY );

    // when a file is selected
    function onFileSelected( event ){
        var files = event.target.files;
        // check if the browser supports this
        if ( FileReader && files && files.length ) {
            var fr = new FileReader();
            // set the image when one is selected
            fr.onload = function(){
                $( state.image ).attr("src", fr.result);
                // toggle the UI
                toggleCropper();

                event.target.value = null;
            }
            state.file_name = files[0].name;
            fr.readAsDataURL( files[0] );
        } else { // this is not supported
            window.alert('Your browser does not support uploading images. Please upgrade to a newer version.');
        }
    }

    // toggle the cropper div
    function toggleCropper(){
        $( state.modal + " #cropper-image-upload").hide();
        $( state.modal + " #cropper-image-container, " + state.submit ).show();
        // enable Cropper for the image
        $( state.image ).cropper({
            aspectRatio: 1 / 1, // force the square shape we want
            dragMode: 'move', viewMode: 1, // do not allow the user to add alpha to the image.
            cropBoxMovable: false, cropBoxResizable: false // do not allow the crop box to be moved
        });
    }

    // upload the image when "done"
    function uploadImage(){
        $( state.image ).cropper( 'getCroppedCanvas', { width: 500, height: 500 } ).toBlob( function( blob ){
            var formData = new FormData();
            formData.append('profile', blob, state.file_name );

            $( state.modal + " #cropper-image-container " ).hide();
            $( state.modal + " #cropper-image-loader " ).show();
            
            $.ajax('api/tasks/uploadProfilePicture.php', {
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function ( response ) {
                    if ( response.success ) {
                        $( state.modal ).modal('hide');
                        state.uploadCallback( response.data );
                    } else {
                        closeAndShowError( response.message );
                    }
                },
                error: function ( error ) {
                    closeAndShowError( "Upload Error" );
                }
            });
        })
    }

    /**
     * closeAndShowError
     * 
     * close the cropper modal and call showError
     * 
     * @param {string} error 
     */
    function closeAndShowError( error ){
        $( state.modal ).modal('hide');
        showError( error );
    }

    /**
     * resetModal
     * 
     * Reset the state of the modal when closing it 
     */
    function resetModal(){
        $("#cropper-image-upload").show();
        $("#cropper-image-loader, #cropper-image-container, " + state.submit ).hide();
        $( state.image ).cropper("destroy")
    }

    // functions for the buttons
    function rotateLeft(){
        $( state.image ).cropper( "rotate", -90 );
    }
    function rotateRight(){
        $( state.image ).cropper( "rotate",  90 );
    }
    function zoomIn() {
        $( state.image ).cropper( "zoom",  0.1 );
    }
    function zoomOut() {
        $( state.image ).cropper( "zoom", -0.1 );
    }

    function scaleX() {
        var data = $( state.image ).cropper( "getData" );
        if ( ( data.rotate >= 0 && data.rotate < 90 ) || ( data.rotate >= 180 && data.rotate < 270 ) ){
            var scale = data.scaleX > 0 ? -1 : 1;
            $( state.image ).cropper( "scaleX", scale );
        } else {
            var scale = data.scaleY > 0 ? -1 : 1;
            $( state.image ).cropper( "scaleY", scale );
        } 
    }
    
    function scaleY() {
        var data = $( state.image ).cropper( "getData" );
        if ( ( data.rotate >= 0 && data.rotate < 90 ) || ( data.rotate >= 180 && data.rotate < 270 ) ){
            var scale = data.scaleY > 0 ? -1 : 1;
            $( state.image ).cropper( "scaleY", scale );
        } else {
            var scale = data.scaleX > 0 ? -1 : 1;
            $( state.image ).cropper( "scaleX", scale );
        }
    }

}; // execute the funciton to run code above