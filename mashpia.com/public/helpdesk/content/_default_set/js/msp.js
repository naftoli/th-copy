function mswDropZoneReload(urlphp,maxc,maxf,dd,multi,allowed,dragstr,dddiv) {
  mswShowSpinner();
  setTimeout(function() {
    jQuery('div[class="file-to-upload"]').remove();
    jQuery('div[class="removereset"]').remove();
    jQuery('div[class="ajax-file-upload"]').remove();
    jQuery('#dropzone').show();
    jQuery('#dropzone').uploadFile({
      url : urlphp,
      maxFileCount : maxc,
      maxFileSize: maxf,
      multiple : multi,
      allowedTypes : allowed,
      returnType : 'json',
      showCancel : false,
      autoSubmit : false,
      showDone : false,
      showError : false,
      showFileSize : true,
      dragDropStr: dragstr,
      dropzoneDiv: dddiv
    });
    mswProcessOK();
  }, 3000);
}

function mswToggleTicketData(id, area) {
  switch(area) {
    case 'field':
      if (jQuery('#' + id + ' .mswcf').css('display') == 'none') {
        jQuery('#' + id + ' .mswcf').slideDown();
      } else {
        jQuery('#' + id + ' .mswcf').slideUp();
      }
      break;
    case 'attach':
      if (jQuery('#' + id + ' .mswatt').css('display') == 'none') {
        jQuery('#' + id + ' .mswatt').slideDown();
      } else {
        jQuery('#' + id + ' .mswatt').slideUp();
      }
      break;
  }
}

function mswBBTags(type, box) {
  switch (type) {
    case 'bold':
      mswInsertAtCursor(box, '[b]..[/b]');
      break;
    case 'italic':
      mswInsertAtCursor(box, '[i]..[/i]');
      break;
    case 'underline':
      mswInsertAtCursor(box, '[u]..[/u]');
      break;
    case 'url':
      mswInsertAtCursor(box, '[url]http://www.example.com[/url]');
      break;
    case 'img':
      mswInsertAtCursor(box, '[img]http://www.example.com/picture.png[/img]');
      break;
    case 'email':
      mswInsertAtCursor(box, '[email]email@example.com[/email]');
      break;
    case 'youtube':
      mswInsertAtCursor(box, '[youtube]abc123[/youtube]');
      break;
    case 'vimeo':
      mswInsertAtCursor(box, '[vimeo]abc123[/vimeo]');
      break;
  }
}

// With thanks to Scott Klarr
// http://www.scottklarr.com
function mswInsertAtCursor(field, text) {
  var txtarea = document.getElementById(field);
  var scrollPos = txtarea.scrollTop;
  var strPos = 0;
  var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ? 'ff' : (document.selection ? 'ie' : false));
  if (br == 'ie') {
    txtarea.focus();
    var range = document.selection.createRange();
    range.moveStart('character', -txtarea.value.length);
    strPos = range.text.length;
  }
  if (br == 'ff') {
    strPos = txtarea.selectionStart;
  }
  var front = (txtarea.value).substring(0, strPos);
  var back = (txtarea.value).substring(strPos, txtarea.value.length);
  txtarea.value = front + text + back;
  strPos = strPos + text.length;
  if (br == 'ie') {
    txtarea.focus();
    var range = document.selection.createRange();
    range.moveStart('character', -txtarea.value.length);
    range.moveStart('character', strPos);
    range.moveEnd('character', 0);
    range.select();
  }
  if (br == 'ff') {
    txtarea.selectionStart = strPos;
    txtarea.selectionEnd = strPos;
    txtarea.focus();
  }
  txtarea.scrollTop = scrollPos;
}

//Search..
function mswSearchAction() {
  if (jQuery('input[name="q"]').val() == '') {
    jQuery('input[name="q"]').focus();
    return false;
  }
  jQuery('#sform').submit();
}

// Show / hide FAQ categories
function mswShowSubFAQ(id, openact) {
  if (jQuery('#mswfaqcatarea .cat' + id + ' i').attr('class') == 'fa fa-folder-open fa-fw cursor_pointer') {
    jQuery('#mswfaqcatarea .cat' + id + ' i').attr('class', 'fa fa-folder fa-fw cursor_pointer');
    jQuery('#mswfaqcatarea .sub' + id + ' div').slideUp();
  } else {
    jQuery('#mswfaqcatarea .mswfaqcatlink i').attr('class', 'fa fa-folder fa-fw cursor_pointer');
    jQuery('#mswfaqcatarea .mswfaqsublink div').slideUp();
    if (jQuery('#mswfaqcatarea .sub' + id + ' div').length > 0) {
      jQuery('#mswfaqcatarea .cat' + id + ' i').attr('class', 'fa fa-folder-open fa-fw cursor_pointer');
      switch(openact) {
        case 'show':
          jQuery('#mswfaqcatarea .sub' + id + ' div').show();
          break;
        default:
          jQuery('#mswfaqcatarea .sub' + id + ' div').slideDown();
          break;
      }
    }
  }
}

// Toggle search..
function mswToggleSearch() {
  if (jQuery('div[class="form-group searchbox"]').css('display') == 'none') {
    jQuery('div[class="form-group searchbox"]').slideDown();
	  if (jQuery('div[class="form-group searchbox"] input[type="text"]').val() == '') {
	    jQuery('div[class="form-group searchbox"] input[type="text"]').focus();
	  }
  } else {
    jQuery('div[class="form-group searchbox"]').slideUp();
	}
}

// Check search..
function mswDoSearch(url, field) {
  switch (url) {
    case 'history':
    case 'disputes':
      if (jQuery('input[name="' + field + '"]').val() == '') {
        jQuery('input[name="' + field + '"]').focus();
        return false;
      }
      window.location = 'index.php?p=' + url + '&' + field + '=' + jQuery('input[name="' + field + '"]').val();
      break;
    default:
      break;
  }
}

// Show/hide boxes for new password option..
function mswNewPass() {
  if (jQuery('#pw').css('display') == 'none') {
    jQuery('#pw').show();
    jQuery('#b1').show();
    jQuery('#b2').hide();
    jQuery('#b3').show();
    jQuery('#b4').hide();
    jQuery('input[name="email"]').attr('onkeypress','if(mswKeyCode(event)==13){mswProcess(\'login\')}');
    if (jQuery('input[name="email"]').val() == '') {
      jQuery('input[name="email"]').focus();
    } else {
      if (jQuery('input[name="pass"]').val() == '') {
        jQuery('input[name="pass"]').focus();
      }
    }
  } else {
    jQuery('#pw').slideUp();
    jQuery('#b1').hide();
    jQuery('#b2').show();
    jQuery('#b3').hide();
    jQuery('#b4').show();
    jQuery('input[name="email"]').attr('onkeypress','if(mswKeyCode(event)==13){mswProcess(\'newpass\')}');
    if (jQuery('input[name="email"]').val() == '') {
      jQuery('input[name="email"]').focus();
    }
  }
}

// Scroll to reply..
function mswScrollToArea(divArea, moffst, poffst) {
  jQuery('html, body').animate({
    scrollTop : jQuery('#' + divArea).offset().top - moffst + poffst
  }, 2000);
}

function mswKeyCode(e) {
  var unicode = (e.keyCode ? e.keyCode : e.charCode);
  return unicode;
}