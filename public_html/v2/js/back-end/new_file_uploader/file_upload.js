/**************** JQUERY SINGLE ASYNC FILE UPLOAD USING JQUERY 1.7 AND XHR *******************/

// DOES NOT SUPPORT IE 7-9

/*
 * Expected options
 *
 * upload_url => the url that we are uploading to
 * formId => the id of the form that is being used
 * fileId => the id of the file field that is being uploaded
 * buttonId => the id of the button that is used to upload the image as a string
 * progressId => the id of the loading bar that will be set to visible and updated with the status of the upload
 * max_file_size => the max file size that can be uploaded (in bytes)
 * success => function to call once the file upload is compleate with the data returned by the ajax request
 */

console.log("loaded works");

// create the basic object
var file_upload = function(options){
    
    this.options = options; // set the internal options object
    this.fileValid = false; // there is no file so it is invalid;
    this.error_message = "You need to select a valid file";
    
    $(this.options.fileId).change(this.validateFile.bind(this));
    $(this.options.buttonId).click(this.uploadFile.bind(this));
};
//
// handler to upload the file
file_upload.prototype.validateFile = function(event){
    
    // this set to file_upload object with .bind() in call back setup (constructor)
    var file = event.target.files[0]; // get the file
    this.fileValid = true; // lets assume the file is good and change that with the checks
    
    // check the file size
    if (file.size > this.options.max_file_size) {
        this.error_message = 'File size is greater then ' + this.bytesToSize(this.options.max_file_size);
        this.fileValid = false; // the file is too large
        
        alert(this.error_message);
    }
    
    if (file.type !== "image/jpeg") {
        this.fileValid = false; // the file is too large
        this.error_message = "File Type must be JPEG";
        
        alert(this.error_message); // show the error message
    }
    
};

file_upload.prototype.uploadFile = function(event){
    if (!this.fileValid) {
        alert(this.error_message);
        return false;
    }
    
    
    var progressId = this.options.progressId;
    $(progressId).css('visibility', 'visible');
    
    $.ajax({
        url: this.options.upload_url,
        type: 'POST',
        data: new FormData($(this.options.formId)[0]),
        
        // Tell jquery not to worry about content type
        cache: false,
        contentType: false,
        processData: false,
        
        xhr: function() {
            var myXhr = $.ajaxSettings.xhr();
            if (myXhr.upload) {
                // For handling the progress of the upload
                myXhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        $(progressId).attr({
                            value: e.loaded,
                            max: e.total,
                        });
                    }
                } , false);
            }
            return myXhr;
        },
        
        success: this.options.success
    });
    
};

// convert bytes to human readable format
file_upload.prototype.bytesToSize = function(bytes) {
   var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
   if (bytes == 0) return '0 Byte';
   var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
   return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
};