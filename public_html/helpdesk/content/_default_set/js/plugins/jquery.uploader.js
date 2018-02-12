/*!
 * JQUERY UPLOAD FILE PLUGIN
 * version: 3.1.0
 * Copyright (c) 2013 Ravishanker Kusuma
 * http://hayageek.com/
 *
 * Modified and adapted for Maian Support by David Ian Bennett
 * 25/7/15 - Added bootstrap progress bar
 * 25/7/15 - Fixed html5 validation errors
 *
 * 11/8/15 - Modified and adapted for Maian Support by David Ian Bennett
 */
(function ($) {
    var feature = {};
    feature.fileapi = $("<input type='file'>").get(0).files !== undefined;
    feature.formdata = window.FormData !== undefined;

    $.fn.uploadFile = function (options) {
        // This is the easiest way to have default options.
        var s = $.extend({
            // These are the defaults.
            url: "",
            method: "POST",
            enctype: "multipart/form-data",
            formData: null,
            returnType: null,
            allowedTypes: "*",
            fileName: "file",
            formData: {},
            dynamicFormData: function () {
                return {};
            },
            maxFileSize: -1,
            multiple: true,
            dragDrop: false,
            autoSubmit: true,
            showCancel: true,
            showAbort: true,
            showDone: true,
            showDelete:false,
            showError: true,
            showStatusAfterSuccess: true,
            showStatusAfterError: true,
            showFileCounter:false,
            fileCounterStyle:"). ",
            showProgress: true,
            onSelect:function(files){ return true;},
            onSubmit: function (files, xhr) {},
            onSuccess: function (files, response, xhr) {},
            onError: function (files, status, message) {},
            deleteCallback: false,
            afterUploadAll: false,
            uploadButtonClass: "ajax-file-upload",
            dragDropStr: "<span><b>Drag &amp; Drop Files</b></span>",
            abortStr: "Abort",
            cancelStr: "Cancel",
            dropzoneDiv: "",
            deletelStr: "Delete",
            doneStr: "Done",
            multiDragErrorStr: "Multiple File Drag &amp; Drop is not allowed.",
            extErrorStr: "is not allowed. Allowed extensions: ",
            sizeErrorStr: "is not allowed. Allowed Max size: ",
            uploadErrorStr: "Upload is not allowed"
        }, options);

        this.fileCounter = 1;
        this.fCounter = 0; //failed uploads
        this.sCounter = 0; //success uploads
        this.tCounter = 0; //total uploads
        var formGroup = "ajax-file-upload-" + (new Date().getTime());
        this.formGroup = formGroup;
        this.hide();
        this.errorLog = $("<div></div>"); //Writing errors
        this.after(this.errorLog);
        this.responses = [];
        if (!feature.formdata) //check drag drop enabled.
        {
            s.dragDrop = false;
        }


        var obj = this;

        var uploadLabel = $('<div>' + $(this).html() + '</div>');
        $(uploadLabel).addClass(s.uploadButtonClass);

        //wait form ajax Form plugin and initialize
        (function checkAjaxFormLoaded() {
            if ($.fn.ajaxForm) {

                if (s.dragDrop) {
                    var dragDrop = $('<div class="ajax-upload-dragdrop" style="vertical-align:top;"></div>');
                    $(obj).before(dragDrop);
                    $(dragDrop).append(uploadLabel);
                    $(dragDrop).append($(s.dragDropStr));
                    setDragDropHandlers(obj, s, dragDrop);

                } else {
                    $(obj).before(uploadLabel);
                }

                createCutomInputFile(obj, formGroup, s, uploadLabel);

            } else window.setTimeout(checkAjaxFormLoaded, 10);
        })();

        this.startUpload = function () {
            $("." + this.formGroup).each(function (i, items) {
                if ($(this).is('form')) $(this).submit();
            });
        }
        this.stopUpload = function () {
            $(".ajax-file-upload-red").each(function (i, items) {
                if ($(this).hasClass(obj.formGroup)) $(this).click();
            });
        }

        this.getResponses = function () {
            return this.responses;
        }
        var checking = false;

        function checkPendingUploads() {
            if (s.afterUploadAll && !checking) {
                checking = true;
                (function checkPending() {
                    if (obj.sCounter != 0 && (obj.sCounter + obj.fCounter == obj.tCounter)) {
                        s.afterUploadAll(obj);
                        checking = false;
                    } else window.setTimeout(checkPending, 100);
                })();
            }

        }

        function setDragDropHandlers(obj, s, ddObj) {
            ddObj.on('dragenter', function (e) {
                e.stopPropagation();
                e.preventDefault();
                $(this).css('border', '2px solid #A5A5C7');
            });
            ddObj.on('dragover', function (e) {
                e.stopPropagation();
                e.preventDefault();
            });
            ddObj.on('drop', function (e) {
                $(this).css('border', '2px dotted #A5A5C7');
                e.preventDefault();
                obj.errorLog.html("");
                var files = e.originalEvent.dataTransfer.files;
                if (!s.multiple && files.length > 1) {
                    if (s.showError) $("<div style='color:red;'>" + s.multiDragErrorStr + "</div>").appendTo(obj.errorLog);
                    return;
                }
                if(s.onSelect(files) == false)
                	return;
                serializeAndUploadFiles(s, obj, files);
            });

            $(document).on('dragenter', function (e) {
                e.stopPropagation();
                e.preventDefault();
            });
            $(document).on('dragover', function (e) {
                e.stopPropagation();
                e.preventDefault();
                ddObj.css('border', '2px dotted #A5A5C7');
            });
            $(document).on('drop', function (e) {
                e.stopPropagation();
                e.preventDefault();
                ddObj.css('border', '2px dotted #A5A5C7');
            });

        }

        function getSizeStr(size) {
            var sizeStr = "";
            var sizeKB = size / 1024;
            if (parseInt(sizeKB) > 1024) {
                var sizeMB = sizeKB / 1024;
                sizeStr = sizeMB.toFixed(2) + " MB";
            } else {
                sizeStr = sizeKB.toFixed(2) + " KB";
            }
            return sizeStr;
        }

        function serializeData(extraData) {
            var serialized = [];
            if (jQuery.type(extraData) == "string") {
                serialized = extraData.split('&');
            } else {
                serialized = $.param(extraData).split('&');
            }
            var len = serialized.length;
            var result = [];
            var i, part;
            for (i = 0; i < len; i++) {
                serialized[i] = serialized[i].replace(/\+/g, ' ');
                part = serialized[i].split('=');
                result.push([decodeURIComponent(part[0]), decodeURIComponent(part[1])]);
            }
            return result;
        }

        function serializeAndUploadFiles(s, obj, files, fileUploadId) {
            var howmanyfiles = (s.maxFileCount > 0 && files.length > s.maxFileCount ? s.maxFileCount : files.length);
            for (var i = 0; i < howmanyfiles; i++) {
                if (!isFileTypeAllowed(obj, s, files[i].name)) {
                    if (s.showError) $("<div style='color:red;'><b>" + files[i].name + "</b> " + s.extErrorStr + s.allowedTypes + "</div>").appendTo(obj.errorLog);
                    continue;
                }
                if (s.maxFileSize != -1 && files[i].size > s.maxFileSize) {
                    if (s.showError) $("<div style='color:red;'><b>" + files[i].name + "</b> " + s.sizeErrorStr + getSizeStr(s.maxFileSize) + "</div>").appendTo(obj.errorLog);
                    continue;
                }
                var ts = s;
                var fd = new FormData();
                var fileName = s.fileName.replace("[]", "");
                fd.append(fileName, files[i]);
                var extraData = s.formData;
                if (extraData) {
                    var sData = serializeData(extraData);
                    for (var j = 0; j < sData.length; j++) {
                        if (sData[j]) {
                            fd.append(sData[j][0], sData[j][1]);
                        }
                    }
                }
                ts.fileData = fd;

                var pd = new createProgressDiv(obj, s);
                var fileNameStr="";
                var rstring = randString(50);
            	if(s.showFileCounter)
            		fileNameStr = obj.fileCounter + s.fileCounterStyle + files[i].name
            	else
            		fileNameStr = '<div class="file-upload-file" id="mswfile-' + rstring + '"><i class="fa fa-check fa-fw"></i> ' + files[i].name + '</div>';

                pd.filename.html(fileNameStr);
            }
        }

        function randString(n) {
            if(!n) {
                n = 5;
            }
            var text = '';
            var possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            for(var i=0; i < n; i++) {
                text += possible.charAt(Math.floor(Math.random() * possible.length));
            }
            return text;
        }

        function isFileTypeAllowed(obj, s, fileName) {
            var fileExtensions = s.allowedTypes.toLowerCase().split(",");
            var ext = fileName.split('.').pop().toLowerCase();
            if (s.allowedTypes != "*" && jQuery.inArray(ext, fileExtensions) < 0) {
                return false;
            }
            return true;
        }


        function createCutomInputFile(obj, group, s, uploadLabel) {

            var fileUploadId = "ajax-upload-id-" + (new Date().getTime());

            var form = $('<div class="mswfup-' + fileUploadId + '"></div>');
            var fileInputStr = "<input type='file' id='" + fileUploadId + "' name='" + s.fileName + "'>";
            if (s.multiple) {
                if (s.fileName.indexOf("[]") != s.fileName.length - 2) // if it does not endwith
                {
                    s.fileName += "[]";
                }
                fileInputStr = "<input type='file' id='" + fileUploadId + "' name='" + s.fileName + "' multiple='multiple'>";
            }
            var fileInput = $(fileInputStr).appendTo(form);

            fileInput.change(function () {

                obj.errorLog.html("");
                var fileExtensions = s.allowedTypes.toLowerCase().split(",");
                var fileArray = [];
                if (this.files) //support reading files
                {
                    var howmanyfiles = (s.maxFileCount > 0 && this.files.length > s.maxFileCount ? s.maxFileCount : this.files.length);
                    for (i = 0; i < howmanyfiles; i++)
                    {
                        fileArray.push(this.files[i].name);
                    }

                    if(s.onSelect(this.files) == false)
	                	return;
                } else {
                    var filenameStr = $(this).val();
                    var flist = [];
                    fileArray.push(filenameStr);
                    if (!isFileTypeAllowed(obj, s, filenameStr)) {
                        if (s.showError) $("<div style='color:red;'><b>" + filenameStr + "</b> " + s.extErrorStr + s.allowedTypes + "</div>").appendTo(obj.errorLog);
                        return;
                    }
                    //fallback for browser without FileAPI
                    flist.push({name:filenameStr,size:'NA'});
                    if(s.onSelect(flist) == false)
	                	return;

                }
                uploadLabel.unbind("click");
                form.hide();
                createCutomInputFile(obj, group, s, uploadLabel);

                form.addClass(group);
                if (feature.fileapi && feature.formdata) //use HTML5 support and split file submission
                {
                    form.removeClass(group); //Stop Submitting when.
                    var files = this.files;
                    serializeAndUploadFiles(s, obj, files, fileUploadId);
                } else {
                    var fileList = "";
                    for (var i = 0; i < fileArray.length; i++) {
		            	if(s.showFileCounter)
        		    		fileList += obj.fileCounter + s.fileCounterStyle + fileArray[i]+"<br>";
            			else
		            		fileList += fileArray[i]+"<br>";;
                        obj.fileCounter++;
                    }
                    var pd = new createProgressDiv(obj, s);
                    pd.filename.html(fileList);
                    ajaxFormSubmit(form, s, pd, fileArray, obj);
                }
                if ($('.file-to-upload').length > 0 && !$('div[class="removereset"]').html()) {
                  mswconfig    = [];
                  mswconfig[0] = s.url;
                  mswconfig[1] = s.maxFileCount;
                  mswconfig[2] = s.maxFileSize;
                  mswconfig[3] = s.dragDrop;
                  mswconfig[4] = s.multiple;
                  mswconfig[5] = s.allowedTypes;
                  mswconfig[6] = s.dragDropStr;
                  $('#' + s.dropzoneDiv).append('<div class="removereset"><a href="#" onclick="mswDropZoneReload(\'' + mswconfig[0] + '\',\'' + mswconfig[1] + '\',\'' + mswconfig[2] + '\',\'' + mswconfig[3] + '\',\'' + mswconfig[4] + '\',\'' + mswconfig[5] + '\',\'' + mswconfig[6] + '\',\'' + s.dropzoneDiv + '\');return false"><i class="fa fa-times fa-fw"></i> ' + s.dragDropStr + '</a></div>');
                }


            });

	         form.css({'margin':0,'padding':0});
            var uwidth=$(uploadLabel).width()+10;
            if(uwidth == 10)
            	uwidth =120;

            var uheight=uploadLabel.height()+10;
            if(uheight == 10)
            	uheight = 35;

            uploadLabel.css({position: 'relative',overflow:'hidden',cursor:'default'});
            fileInput.css({position: 'absolute','cursor':'pointer',
							'top': '0px',
							'width': '1114px',
							'height': '153px',
							'left': '0px',
							'z-index': '100',
							'opacity': '0.0',
							'filter':'alpha(opacity=0)',
							'-ms-filter':"alpha(opacity=0)",
							'-khtml-opacity':'0.0',
							'-moz-opacity':'0.0'
							});
	         form.appendTo(uploadLabel);

        }

        function createProgressDiv(obj, s) {
            this.statusbar = $("<div class='file-to-upload'></div>");
            this.filename = $("<div></div>").appendTo(this.statusbar);
            /*this.progressDiv = $("<div class='ajax-file-upload-progress progress'>").appendTo(this.statusbar).hide();
            this.progressbar = $("<div class='ajax-file-upload-bar " + obj.formGroup + " progress-bar progress-bar-info progress-bar-striped' role='progressbar' aria-valuenow='1' aria-valuemin='1' aria-valuemax='100' style='min-width: 2em; width: 1%'>1%</div>").appendTo(this.progressDiv);
            this.abort = $("<div class='ajax-file-upload-red " + obj.formGroup + "'><i class='fa fa-times fa-fw'></i> " + s.abortStr + "</div>").appendTo(this.statusbar).hide();
            this.cancel = $("<div class='ajax-file-upload-red'>" + s.cancelStr + "</div>").appendTo(this.statusbar).hide();
            this.done = $("<div class='ajax-file-upload-green'>" + s.doneStr + "</div>").appendTo(this.statusbar).hide();
            this.del = $("<div class='ajax-file-upload-red'>" + s.deletelStr + "</div>").appendTo(this.statusbar).hide();
            */
            obj.errorLog.after(this.statusbar);
            return this;
        }

        function ajaxFormSubmit(form, s, pd, fileArray, obj) {

            var currentXHR = null;
            var options = {
                cache: false,
                contentType: false,
                processData: false,
                forceSync: false,
                data: s.formData,
                formData: s.fileData,
                dataType: s.returnType,
                beforeSubmit: function (formData, $form, options) {
                    if (s.onSubmit.call(this, fileArray) != false) {
                        var dynData = s.dynamicFormData();
                        if (dynData) {
                            var sData = serializeData(dynData);
                            if (sData) {
                                for (var j = 0; j < sData.length; j++) {
                                    if (sData[j]) {
                                        if (s.fileData != undefined) options.formData.append(sData[j][0], sData[j][1]);
                                        else options.data[sData[j][0]] = sData[j][1];
                                    }
                                }
                            }
                        }
                        obj.tCounter += fileArray.length;
                        //window.setTimeout(checkPendingUploads, 1000); //not so critical
                        checkPendingUploads();
                        return true;
                    }
                    pd.statusbar.append("<div style='color:red;'>" + s.uploadErrorStr + "</div>");
                    pd.cancel.show()
                    form.remove();
                    pd.cancel.click(function () {
                        pd.statusbar.remove();
                    });
                    return false;
                },
                beforeSend: function (xhr, o) {

                    pd.progressDiv.show();
                    pd.cancel.hide();
                    pd.done.hide();
                    if (s.showAbort) {
                        pd.abort.show();
                        pd.abort.click(function () {
                            xhr.abort();
                            mswDropZoneReload('single');
                        });
                    }
                    if (!feature.formdata) //For iframe based push
                    {
                        pd.progressbar.width('5%');
                    } else pd.progressbar.width('1%'); //Fix for small files
                },
                uploadProgress: function (event, position, total, percentComplete) {
		            //Fix for smaller file uploads in MAC
                	if(percentComplete > 98) percentComplete =98;

                    var percentVal = percentComplete + '%';
                    if (percentComplete > 1) pd.progressbar.width(percentVal)
                    if(s.showProgress)
                    {
                    	pd.progressbar.html(percentVal);
                    	//pd.progressbar.css('text-align', 'center');
                    }

                },
                success: function (data, message, xhr) {
                    obj.responses.push(data);
                    pd.progressbar.width('100%')
                    if(s.showProgress)
                    {
                    	pd.progressbar.html('100%');
                    	//pd.progressbar.css('text-align', 'center');
                    }

                    pd.abort.slideUp();
                    s.onSuccess.call(this, fileArray, data, xhr);
                    if (s.showStatusAfterSuccess) {
                        if (s.showDone) {
                            pd.done.show();
                            pd.done.click(function () {
                                pd.statusbar.hide("slow");
                                pd.statusbar.remove();
                            });
                        } else {
                            pd.done.hide();
                        }
                        if(s.showDelete)
                        {
                        	pd.del.show();
                        	 pd.del.click(function () {
                        		if(s.deleteCallback) s.deleteCallback.call(this, data,pd);
                            });
                        }
                        else
                        {
	                        pd.del.hide();
	                    }
                    } else {
                        pd.statusbar.hide("slow");
                        pd.statusbar.remove();

                    }
                    form.remove();
                    obj.sCounter += fileArray.length;
                },
                error: function (xhr, status, errMsg) {
                    pd.abort.slideUp();
                    if (xhr.statusText == "abort") //we aborted it
                    {
                        pd.statusbar.hide("slow");
                    } else {
                        s.onError.call(this, fileArray, status, errMsg);
                        if (s.showStatusAfterError) {
                            pd.progressDiv.hide();
                            pd.statusbar.append("<span style='color:red;'>ERROR: " + errMsg + "</span>");
                        } else {
                            pd.statusbar.hide();
                            pd.statusbar.remove();
                        }
                    }

                    form.remove();
                    obj.fCounter += fileArray.length;

                }
            };
            if (s.autoSubmit) {
                form.ajaxSubmit(options);
            } else {
                if (s.showCancel) {
                    pd.cancel.show();
                    pd.cancel.click(function () {
                        form.remove();
                        pd.statusbar.remove();
                    });
                }
                form.ajaxForm(options);

            }

        }
        return this;

    }


}(jQuery));