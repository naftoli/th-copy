function mswShowImapFolders(fld) {
  var inputbox  = jQuery('#' + fld +' input[type="text"]').attr('name');
  jQuery('input[name="' + inputbox + '"]').css('background', 'url(templates/images/spinner.gif) no-repeat 99% 50%');
  jQuery(document).ready(function() {
    jQuery.post('index.php?ajax=imfolders', {
        host  : jQuery('input[name="im_host"]').val(),
        user  : jQuery('input[name="im_user"]').val(),
        pass  : jQuery('input[name="im_pass"]').val(),
        port  : jQuery('input[name="im_port"]').val(),
        flags : jQuery('input[name="im_flags"]').val()
      },
      function(data) {
        jQuery('input[name="' + inputbox + '"]').css('background-image', 'none');
        switch (data['msg']) {
          case 'ok':
            jQuery('#' + fld +' select').html(data['html']);
            jQuery('input[name="' + inputbox + '"]').hide();
            jQuery('#' + fld +' span').hide();
            jQuery('#' + fld +' select').show();
            break;
          default:
            mswAlert(data['info'], data['sys'], 'err');
            break;
        }
      }, 'json');
  });
  return false
}

function mswSelectAccount(value, field) {
  var selname  = (field == 'name' ? 'accntn' : 'accnte');
  var chopdata = value.split('###');
  jQuery('.' + selname).hide();
  switch(field) {
    case 'dest_email':
      jQuery('input[name="' + field + '"]').val(chopdata[1]);
      break;
    default:
      jQuery('input[name="name"]').val(chopdata[0]);
      jQuery('input[name="email"]').val(chopdata[1]);
      break;
  }
}

function mswSearchAccounts(field, id) {
  if (jQuery('input[name="' + field + '"]').val() == '') {
    jQuery('input[name="' + field + '"]').focus();
    return false;
  }
  jQuery('input[name="' + field + '"]').css('background', 'url(templates/images/spinner.gif) no-repeat 98% 50%');
  jQuery(document).ready(function() {
    jQuery.post('index.php?ajax=search-accounts', {
      ffld : field,
      fval : jQuery('input[name="' + field + '"]').val(),
      emal : (jQuery('input[name="email"]') ? jQuery('input[name="email"]').val() : '')
    },
    function(data) {
      jQuery('input[name="' + field + '"]').css('background-image', 'none');
      switch(data['msg']) {
        case 'ok':
          var html = '';
          for (var i = 0; i<data['accounts'].length; i++) {
            html += '<option value="' + data['accounts'][i]['name'] + '###' + data['accounts'][i]['email'] + '">' + data['accounts'][i]['name'] + ' (' + data['accounts'][i]['email'] + ')</option>';
          }
          var selname = (field == 'name' ? 'accntn' : 'accnte');
          jQuery('select[name="' + selname + '"]').html('<option value="0">- - - - - -</option>' + html);
          jQuery('.' + selname).show();
          break;
        default:
          mswAlert(data['info'], data['sys'], 'err');
          break;
      }
    },'json');
  });
  return false;
}

function mswNotes(ticket) {
  jQuery('textarea[name="notes"]').removeClass('updated').addClass('updating');
  jQuery(document).ready(function() {
    jQuery.post('index.php?ajax=ticknotes&id=' + ticket, {
      notes : jQuery('textarea[name="notes"]').val()
    },
    function(data) {
      jQuery('textarea[name="notes"]').removeClass('updating').addClass('updated');
    }, 'json');
  });
  return false;
}

function mswRemoveHistory(id, ticket, txt) {
  var confirmSub = confirm(txt);
  if (confirmSub) {
    mswShowSpinner();
    jQuery(document).ready(function() {
      jQuery.ajax({
        url: 'index.php',
        data: 'ajax=tickdelhis&id=' + id + '&t=' + ticket,
        dataType: 'json',
        success: function(data) {
          switch(data['msg']) {
            case 'ok':
              switch(id) {
                case 'all':
                  mswProcessOK();
                  jQuery('.historyarea').html('');
                  break;
                default:
                  mswProcessOK();
                  jQuery('#hdata_' + id).remove();
                  break;
              }
              if (jQuery('.historyarea tr').length == 0) {
                jQuery('.history-panel div[class="btn-group"]').remove();
                jQuery('.history-panel .panel-heading span').before('&nbsp;');
                jQuery('.history-panel .panel-heading span').removeClass('margin_top_7');
                jQuery('.historyarea').html('<div class="nothing_to_see">' + data['html'] + '</div>');
              }
              break;
            default:
              mswProcessOK();
              mswAlert(data['info'], data['sys'], 'err');
              break;
          }
        }
      });
    });
    return false;
  } else {
    return false;
  }
}

function mswDeptLoader(tab, page, replyid, area) {
  if (jQuery('select[name="dept"]').val() == '0') {
    return false;
  }
  var tickID = '0';
  if (page == 'ticket') {
    var tickID = jQuery('input[name="id"]').val();
  }
  jQuery(document).ready(function() {
    mswShowSpinner();
    jQuery.ajax({
      url: 'index.php',
      data: 'ajax=tickdept&dp=' + jQuery('select[name="dept"]').val() + '&id=' + tickID + '&ar=' + area,
      dataType: 'json',
      success: function(data) {
        mswProcessOK();
        if (data['fields']) {
          jQuery('#' + tab).html(data['fields']);
          if (jQuery('#licus').html()) {
            jQuery('#licus').show();
          }
        } else {
          jQuery('#' + tab).html('');
          if (jQuery('#licus').html()) {
            jQuery('#licus').hide();
          }
        }
        switch(data['assign']) {
          case 'yes':
            if (jQuery('#liusr').html()) {
              jQuery('#liusr').show();
            }
            break;
          case 'no':
            if (jQuery('#liusr').html()) {
              jQuery('#liusr').hide();
            }
            break;
        }
        if (data['subject'] && jQuery('input[name="subject"]').val() == '') {
          jQuery('input[name="subject"]').val(data['subject']);
        }
        if (data['comments'] && jQuery('textarea[name="comments"]').val() == '') {
          jQuery('textarea[name="comments"]').val(data['comments']);
        }
      }
    });
  });
  return false;
}

function mswProcessMultiPart() {
  jQuery(document).ready(function() {
    mswShowSpinner();
    var options = {
      dataType: 'json',
      success: mswProcessMultiPartCallBack
    };
    jQuery('#mswform').submit(function() {
      jQuery(this).ajaxSubmit(options);
      jQuery(this).unbind('submit');
      return false;
    });
  });
}

// post-submit callback
function mswProcessMultiPartCallBack(responseText, statusText, xhr, $form)  {
  switch (responseText['msg']) {
    case 'ok':
      switch(responseText['field']) {
        case 'redirect':
          window.location = responseText['redirect'];
          break;
        default:
          mswProcessOK();
          break;
      }
      break;
    case 'reload':
      mswProcessOKWait();
      setTimeout(function() {
        window.location.reload();
      }, 500);
      break;
    case 'err':
       mswProcessOK();
      mswAlert(responseText['info'], responseText['sys'], 'err');
      break;
    default:
       mswProcessOK();
      break;
  }
}

function mswProcessOKWait() {
  jQuery('body').css({'opacity' : '1.0'});
  jQuery('div[class="overlaySpinner"]').hide();
}

function mswProcessOK() {
  jQuery('body').css({'opacity' : '1.0'});
  jQuery('div[class="overlaySpinner"]').hide();
}

function mswProcess(page, par) {
  jQuery(document).ready(function() {
    mswShowSpinner();
    setTimeout(function() {
      jQuery.ajax({
        type: 'POST',
        url: 'index.php?ajax=' + page + (par != undefined ? '&param=' + par : ''),
        data: jQuery('#mscontainer > form').serialize(),
        cache: false,
        dataType: 'json',
        success: function(data) {
          switch (data['msg']) {
            case 'ok':
              switch(page) {
                case 'faqimport':
                case 'logclr':
                case 'mbread':
                case 'mbunread':
                case 'mbclear':
                case 'mbmove':
                case 'mbfolders':
                case 'mbreply':
                case 'tickdispusers':
                  mswProcessOKWait();
                  setTimeout(function() {
                    window.location.reload();
                  }, 500);
                  break;
                case 'tickrepdel':
                  jQuery('#datarp_' + par).slideUp();
                  mswProcessOK();
                  break;
                case 'tickattdel':
                  jQuery('#datatrat_' + par).slideUp();
                  mswProcessOK();
                  if (data['cnt'] == '0') {
                    jQuery('.attachlink').remove();
                    jQuery('.mswatt').hide();
                  } else {
                    jQuery('.attachcount').html(data['cnt']);
                  }
                  break;
                case 'tickassign':
                  for (var i=0; i<data['accepted'].length; i++) {
                    jQuery('#datatr_' + data['accepted'][i]).remove();
                  }
                  if (jQuery('tbody input[name="del[]"]').length == 0) {
                    window.location = 'index.php?p=assign';
                  } else {
                    mswProcessOK();
                  }
                  break;
                default:
                  if (data['delconfirm'] > 0) {
                    jQuery('tbody input[name="del[]"]:checked').each(function() {
                      jQuery('#datatr_' + jQuery(this).attr('value')).remove();
                    });
                    if (jQuery('tbody input[name="del[]"]').length == 0) {
                      switch(page) {
                        case 'mbdel':
                          window.location = 'index.php?p=mailbox&f=bin';
                          break;
                        default:
                          window.location.reload();
                          break;
                      }
                    } else {
                      mswProcessOK();
                    }
                  } else {
                    switch(page) {
                      case 'mbcompose':
                        jQuery('input[name="subject"]').val('');
                        jQuery('textarea[name="message"]').val('');
                        jQuery('div[class="mailStaff"] input[type="checkbox"]').prop('checked', false);
                        mswCheckCount('mailbox','sendbutton','mswCVal');
                        mswProcessOK();
                        break;
                      default:
                        mswProcessOK();
                        break;
                    }
                  }
                  break;
              }
              break;
            case 'ok-tools':
              mswProcessOK();
              mswAlert(data['report'], data['sys'], 'ok');
              break;
            case 'ok-dl':
              window.location = 'index.php?ajax=fdl&infp=' + data['file'] + '&infpt=' + data['type'];
              mswProcessOK();
              break;
            case 'err':
              mswProcessOK();
              mswAlert(data['info'], data['sys'], 'err');
              break;
            default:
              mswProcessOK();
              break;
          }
        }
      });
    }, 1500);
  });
  return false;
}

function mswLogin() {
  if (jQuery('input[name="user"]').val() == '' ||
      jQuery('input[name="pass"]').val() == '') {
    if (jQuery('input[name="user"]').val() == '') {
      jQuery('input[name="user"]').focus();
    } else {
      jQuery('input[name="pass"]').focus();
    }
  } else {
    jQuery('input[name="user"]').css('background', 'url(templates/images/spinner.gif) no-repeat 99% 50%');
    jQuery(document).ready(function() {
      jQuery.ajax({
        type: 'POST',
        url: 'index.php?ajax=login',
        data: jQuery('#mscontainer > form').serialize(),
        cache: false,
        dataType: 'json',
        success: function (data) {
          jQuery('input[name="user"]').css('background-image', 'none');
          switch(data['msg']) {
            case 'ok':
              window.location = data['redirect'];
              break;
            default:
              jQuery('div[class="alert alert-warning"] span').html('<i class="fa fa-warning fa-fw"></i> ' + data['info']);
              jQuery('div[class="alert alert-warning"]').slideDown();
              break;
          }
        }
      });
    });
    return false;
  }
}

function mswAlert(msg, txt, type) {
  jQuery('div[class="modal-backdrop fade in"]').remove();
  jQuery('#bootlogbox').remove();
  switch (type) {
    case 'err':
      BootstrapDialog.show({
        title: '<i class="fa fa-warning fa-fw"></i> ' + txt,
        message: msg,
        type: BootstrapDialog.TYPE_DANGER,
        id: 'bootlogbox',
        draggable: true,
        autodestroy: true
      });
      break;
    default:
      BootstrapDialog.show({
        title: txt,
        message: msg,
        type: BootstrapDialog.TYPE_PRIMARY,
        id: 'bootlogbox',
        draggable: true,
        autodestroy: true
      });
      break;
  }
}

function mswShowSpinner() {
  jQuery('body').css({'opacity' : '0.7'});
  jQuery('.overlaySpinner').css({
    'left' : '50%',
    'top' : '50%',
    'position' : 'fixed',
    'margin-left' : -jQuery('.overlaySpinner').outerWidth()/2,
    'margin-top' : -jQuery('.overlaySpinner').outerHeight()/2
  });
  jQuery('div[class="overlaySpinner"]').show();
}

function mswUnreadFlag() {
  jQuery('span[class="mailboxcount"]').html(jQuery('span[class="mailboxcount"]').html());
  jQuery.ajax({
    url: 'index.php',
    data: 'ajax=unread-mailbox',
    dataType: 'json',
    success: function (data) {
      if (data['cnt'] > 0) {
        jQuery('span[class="mailboxcount"]').html('<span class="unread">' + data['cnt'] + '</span>');
      } else {
        jQuery('span[class="mailboxcount"]').html('<span class="read">0</span>');
      }
    }
  });
  return false;
}