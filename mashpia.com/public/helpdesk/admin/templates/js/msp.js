function mswDropZoneReloader(urlphp,maxc,maxf,dd,multi,allowed,dragstr,dddiv) {
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

function mswRowForDel(id, box) {
  jQuery('#' + id).remove();
  jQuery('<input type="hidden" name="' + box + '[]" value="' + id + '">').appendTo('form');
}

function mswDropZoneReload(opttype) {
  switch(opttype) {
    case 'after':
      mswShowSpinner();
      setTimeout(function() {
        window.location.reload();
      }, 1500);
      break;
    case 'single':
      setTimeout(function() {
        if (jQuery('div[class="ajax-file-upload"]').css('display') == 'none' &&
            jQuery('div[class="dropzone"]').css('display') == 'none' &&
            jQuery('div[class="ajax-file-upload-statusbar"]').css('display') == 'none') {
          mswShowSpinner();
          setTimeout(function() {
            window.location.reload();
          }, 1500);
        }
      }, 1000);
      break;
  }
}

function mswMBFolders(opr, id, max) {
  var boxes = jQuery('div[class="tab-content"] input[type="text"]').length;
  var blimit = (parseInt(max) > 0 ? parseInt(max) : 999999999);
  switch(opr) {
    case 'add':
      if (boxes < blimit) {
        jQuery('div[class="tab-content"] div[class="form-group"]').last().after(jQuery('div[class="tab-content"] div[class="form-group"]').last().clone());
        jQuery('div[class="tab-content"] input[type="text"]').last().val('');
        var boxid = mswRandString(30);
        jQuery('div[class="tab-content"] div[class="form-group"]').last().attr('id', 'fldr_' + boxid);
        jQuery('div[class="tab-content"] input[type="text"]').last().attr('id', boxid);
        jQuery('div[class="tab-content"] span[class="input-group-addon"] a').last().attr('onclick', 'mswMBFolders(\'remove\', \'' + boxid + '\', 0);return false');
        jQuery('div[class="tab-content"] input[type="text"]').last().attr('name','new[]');
      }
      if (parseInt(boxes + 1) >= blimit) {
        jQuery('div[class="panel-heading text-right"] button').prop('disabled', true);
      } else {
        jQuery('div[class="panel-heading text-right"] button').prop('disabled', false);
      }
      break;
    case 'remove':
      if (boxes > 1) {
        jQuery('#fldr_' + id).remove();
      } else {
        jQuery('#fldr_' + id + ' input[type="text"]').val('');
      }
      jQuery('div[class="panel-footer"] button[type="button"]').before('<input type="hidden" name="rem[]" value="' + id + '">');
      if (boxes < blimit) {
        jQuery('div[class="panel-heading text-right"] button').prop('disabled', false);
      }
      break;
  }
}

function mswMaxSize(value) {
  jQuery('input[name="maxsize"]').val(value);
  jQuery('#maxsizeinput').show();
  jQuery('#maxsizeoptions').hide();
}

function mswShowMaxSizeOptions() {
  jQuery('#maxsizeinput').hide();
  jQuery('#maxsizeoptions').show();
}

function mswCheckCount(area, button, spanid) {
  var cnt = 0;
  if (area.substring(0, 1) == '#') {
    var idval = area.substring(1);
    var area  = 'standard';
  }
  switch (area) {
    case 'assign':
      jQuery('.checkboxArea input[type="checkbox"]').each(function() {
        if (jQuery(this).prop('checked')) {
          ++cnt;
        }
      });
      break;
    case 'mailbox':
      jQuery('.mailStaff input[type="checkbox"]').each(function() {
        if (jQuery(this).prop('checked')) {
          ++cnt;
        }
      });
      break;
    case 'standard':
      jQuery('#' + idval + ' input[type="checkbox"]').each(function() {
        if (jQuery(this).prop('checked')) {
          ++cnt;
        }
      });
      break;
    default:
      jQuery('.' + area + ' td input[type="checkbox"]').each(function() {
        if (jQuery(this).prop('checked')) {
          ++cnt;
        }
      });
      break;
  }
  // Enable/disable button..
  jQuery('#' + button).prop('disabled', (cnt > 0 ? false : true));
  // Append count to button if applicable..
  if (spanid && jQuery('#' + spanid)) {
    jQuery('#' + spanid).html('(' + cnt + ')');
  }
  if (cnt == 0 && jQuery('.table-responsive thead').html()) {
    jQuery('.table-responsive thead input[type="checkbox"]').prop('checked', false);
  }
}

function mswMenuButton(act) {
  switch(act) {
    case 'open':
      jQuery('.slidepanelbuttonleft i').attr('class', 'fa fa-chevron-left fa-fw');
      break;
    default:
      jQuery('.slidepanelbuttonleft i').attr('class', 'fa fa-navicon fa-fw');
      break;
  }
}

function mswSearchReload(pg) {
 mswShowSpinner();
 setTimeout(function() {
    window.location = 'index.php?p=' + pg;
  }, 500);
}

function mswMailTest() {
  var em = jQuery('input[name="emails"]').val();
  if (em == '') {
    jQuery('input[name="emails"]').focus();
    return false
  }
  jQuery('#testbutton i').attr('class', 'fa fa-spinner fa-spin fa-fw');
  jQuery(document).ready(function() {
    jQuery.post('index.php?ajax=mailtest', {
       emails : jQuery('input[name="emails"]').val()
     },
     function(data) {
       jQuery('#testbutton i').attr('class', 'fa fa-check');
     },'json');
  });
  return false;
}

function mswEnableDisable(obj, page, id) {
  // Current state..
  var curState = jQuery(obj).attr('class');
  // Attach spinner..
  jQuery(obj).attr('class', 'fa fa-spinner fa-spin fa-fw');
  jQuery(document).ready(function() {
    jQuery.ajax({
      url: 'index.php',
      data: 'ajax=' + page + '&id=' + id + '&changeState=' + curState,
      dataType: 'json',
      success: function(data) {
        switch (curState) {
          case 'fa fa-flag fa-fw msw-green cursor_pointer':
            jQuery(obj).attr('class', 'fa fa-flag-o fa-fw cursor_pointer');
            break;
          default:
            jQuery(obj).attr('class', 'fa fa-flag fa-fw msw-green cursor_pointer');
            break;
        }
      }
    });
  });
  return false;
}

function mswKeyCode(e) {
  var unicode = (e.keyCode ? e.keyCode : e.charCode);
  return unicode;
}

function mswLoginClearErr() {
  if (jQuery('div[class="alert alert-warning"]').css('display') != 'none') {
    jQuery('div[class="alert alert-warning"]').html('<span></span>');
    jQuery('div[class="alert alert-warning"]').hide();
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

function mswInsertMailBox(fld) {
  var inputbox  = jQuery('#' + fld +' input[type="text"]').attr('name');
  jQuery('#' + fld +' select').hide();
  jQuery('input[name="' + inputbox + '"]').val(jQuery('#' + fld +' select').val());
  jQuery('input[name="' + inputbox + '"]').show();
  jQuery('#' + fld +' span').show();
}

function mswFolderCheck() {
  if (jQuery('input[name="im_host"]').val() == '') {
    jQuery('input[name="im_host"]').focus();
    return false;
  } else if (jQuery('input[name="im_user"]').val() == '') {
    jQuery('input[name="im_user"]').focus();
    return false;
  } else if (jQuery('input[name="im_pass"]').val() == '') {
    jQuery('input[name="im_pass"]').focus();
    return false;
  } else if (jQuery('input[name="im_port"]').val() == '') {
    jQuery('input[name="im_port"]').focus();
    return false;
  } else {
    return true;
  }
}

function mswGenerateAPIKey() {
  jQuery('input[name="apiKey"]').css('background', 'url(templates/images/spinner.gif) no-repeat 98% 50%');
  jQuery(document).ready(function() {
    jQuery.ajax({
      url: 'index.php',
      data: 'ajax=api-key',
      dataType: 'json',
      success: function(data) {
        jQuery('input[name="apiKey"]').css('background-image', 'none');
        jQuery('input[name="apiKey"]').val(data['key']);
      }
    });
  });
  return false;
}

// Auto pass..
function mswPassGenerator(label, field) {
  jQuery('input[name="' + field + '"]').css('background', 'url(templates/images/spinner.gif) no-repeat 98% 50%');
  jQuery(document).ready(function() {
    jQuery.ajax({
      url: 'index.php',
      data: 'ajax=passgen',
      dataType: 'json',
      success: function(data) {
        jQuery('input[name="' + field + '"]').css('background-image', 'none');
        jQuery('#' + label + ' span').remove();
        jQuery('#' + label).append('<span style="padding-left:20px" class="highlightPass">' + data['pass'] + '</span>');
        jQuery('input[name="' + field + '"]').val(data['pass']);
      }
    });
  });
  return false;
}

// Check questions..
function mswCheckBoxes(checked, field) {
  if (checked) {
    jQuery(field + ' input:checkbox').prop('checked', true);
  } else {
    jQuery(field + ' input:checkbox').prop('checked', false);
  }
}

// Search..
function mswDoSearch(url) {
  switch (url) {
    case 'graph':
      var sfrom = jQuery('input[name="from"]').val();
      var sto = jQuery('input[name="to"]').val();
      var shid = jQuery('input[type="hidden"]').val();
      window.location = 'index.php?p=' + url + '&id=' + shid + '&from=' + sfrom + '&to=' + sto;
      break;
    case 'accountman':
    case 'log':
      var skeys = jQuery('input[name="keys"]').val();
      var sfrom = jQuery('input[name="from"]').val();
      var sto = jQuery('input[name="to"]').val();
      window.location = 'index.php?p=' + url + '&keys=' + skeys + '&from=' + sfrom + '&to=' + sto;
      break;
    case 'reports':
      var sfrom = jQuery('input[name="from"]').val();
      var sto = jQuery('input[name="to"]').val();
      var sdept = jQuery('select[name="dept"]').val();
      var sview = jQuery('select[name="view"]').val();
      window.location = 'index.php?p=' + url + '&dept=' + sdept + '&view=' + sview + '&from=' + sfrom + '&to=' + sto;
      break;
    case 'responses':
      var skeys = jQuery('input[name="keys"]').val();
      var sfrom = jQuery('input[name="from"]').val();
      var sto = jQuery('input[name="to"]').val();
      var shid = jQuery('input[type="hidden"]').val();
      window.location = 'index.php?p=' + url + '&id=' + shid + '&keys=' + skeys + '&from=' + sfrom + '&to=' + sto;
      break;
    default:
      if (jQuery('input[name="keys"]').val() == '') {
        jQuery('input[name="keys"]').focus();
        return false;
      }
      window.location = 'index.php?p=' + url + '&keys=' + jQuery('input[name="keys"]').val();
      break;
  }
}

// Check/uncheck array of checkboxes..
function mswSelectAll(which, status) {
  jQuery("#" + which + " input:checkbox").each(function() {
    jQuery(this).prop('checked', (status == 'on' ? true : false));
  });
}

// Uncheck box..
function mswUncheck(box) {
  jQuery('#' + box).prop('checked', false);
}

// Uncheck range..
function mswCheckRange(action, chkclass) {
  jQuery('.' + chkclass + ' input[type="checkbox"]').each(function() {
    jQuery(this).prop('checked', (action ? true : false));
  });
}

// Jump to area and wait, then show something..
function mswJumpWait(divarea, showarea) {
  mswScrollToArea(divarea);
  setTimeout(function() {
    jQuery('#' + showarea).slideDown();
  }, 2000);
}

// Scroll to..
function mswScrollToArea(divArea, moffst, poffst) {
  jQuery('html, body').animate({
    scrollTop : jQuery('#' + divArea).offset().top - moffst + poffst
  }, 2000);
}

// Confirm message..simple link..
function mswConfirmButtonLink(txt, link) {
  var confirmSub = confirm(txt);
  if (confirmSub) {
    window.location = link;
  } else {
    return false;
  }
}

// Confirm message..
function mswConfirmButtonAction(txt, ajaxcall, par) {
  var confirmSub = confirm(txt);
  if (confirmSub) {
    mswProcess(ajaxcall, par);
  } else {
    return false;
  }
}

// Toggle..
function mswToggleButton(area) {
  switch (area) {
    case 'search':
      if (jQuery('div[class="form-group searchbox"]').css('display') == 'none') {
        jQuery('div[class="form-group searchbox"]').slideDown(function() {
          jQuery('input[name="keys"]').focus();
        });
      } else {
        jQuery('div[class="form-group searchbox"]').slideUp();
      }
      break;
    case 'dates':
      if (jQuery('div[class="form-group searchbox"]').css('display') == 'none') {
        jQuery('div[class="form-group searchbox"]').slideDown(function() {
          jQuery('input[name="from"]').focus();
        });
      } else {
        jQuery('div[class="form-group searchbox"]').slideUp();
      }
      break;
  }
}

// Version check..
function mswVersionCheck() {
  jQuery(document).ready(function() {
    jQuery.ajax({
      url: 'index.php',
      data: 'ajax=vc',
      dataType: 'json',
      success: function(data) {
        jQuery('div[class="panel-body vcheckarea"]').html(data['html']);
      }
    });
  });
  return false;
}

// ID show/hide..
function mswDivHideShow(show, hide) {
  jQuery('#' + show).show();
  jQuery('#' + hide).hide();
}

// Window location..
function mswWindowLoc(url) {
  switch (url) {
    case 'backwards':
      window.history.back();
      break;
    default:
      window.location = url;
      break;
  }
}

function mswFaqAttBoxes(act) {
  var cof = jQuery('#two div[class="form-group"]').length;
  switch(act) {
    case 'add':
      jQuery('#two').append(jQuery('#two div[class="form-group"]').last().clone());
      jQuery('#two div[class="form-group"] input[name="remote[]"]').last().val('');
      jQuery('#two div[class="form-group"] input[name="remote[]"]').last().attr('tabindex', parseInt(cof + 1));
      jQuery('#two div[class="form-group"] span[class="input-group-addon"]').last().html(parseInt(cof + 1));
      break;
    case 'minus':
      if (cof > 1) {
        jQuery('#two div[class="form-group"]').last().remove();
      }
      break;
  }
}

function mswRandString(n) {
  var text  = '';
  var chars = 'ABCDEFG34HI9JKLMN11OPQ0RSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  for(var i=0; i < n; i++) {
    text += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  return text;
}