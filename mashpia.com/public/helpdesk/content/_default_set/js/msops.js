/* JS Ops */

function mswProcessOKWait() {
  jQuery('body').css({'opacity' : '1.0'});
  jQuery('div[class="overlaySpinner"]').hide();
}

function mswProcessOK() {
  jQuery('body').css({'opacity' : '1.0'});
  jQuery('div[class="overlaySpinner"]').hide();
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
  switch (responseText['status']) {
    case 'ok':
      switch(responseText['field']) {
        case 'redirect':
          window.location = responseText['msg'];
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
      mswAlert(responseText['msg'], responseText['sys'], 'err');
      break;
    default:
      mswProcessOK();
      break;
  }
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
          switch (data['status']) {
            case 'ok':
              switch(data['field']) {
                case 'redirect':
                  window.location = data['msg'];
                  break;
                default:
                  mswProcessOK();
                  break;
              }
              break;
            case 'ok-dialog':
              mswProcessOK();
              mswNewPass();
              mswAlert(data['msg'], data['sys']);
              break;
            case 'err':
              mswProcessOK();
              mswAlert(data['msg'], data['sys'], 'err');
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

function mswVote(obj, id) {
  switch(jQuery(obj).attr('class')) {
    case 'fa fa-thumbs-up fa-fw cursor_pointer':
      var vote = 'yes';
      jQuery(obj).attr('class', 'fa fa-spinner fa-spin fa-fw');
      break;
    default:
      var vote = 'no';
      jQuery(obj).attr('class', 'fa fa-spinner fa-spin fa-fw');
      break;
  }
  jQuery(document).ready(function() {
    jQuery.ajax({
      url: 'index.php',
      data: 'ajax=voting&id=' + id + '&vote=' + vote,
      dataType: 'json',
      cache: false,
      success: function (data) {
        jQuery('div[class="row votefont"] div:first i').attr('class', 'fa fa-thumbs-up fa-fw cursor_pointer');
        jQuery('div[class="row votefont"] div:nth-child(2) i').attr('class', 'fa fa-thumbs-down fa-fw cursor_pointer');
        switch(data['status']) {
          case 'ok':
            jQuery('div[class="row votefont"] div:first span').html(data['yes']);
            jQuery('div[class="row votefont"] div:nth-child(2) span').html(data['no']);
            jQuery('span[class="votetotalarea"]').html(data['total']);
            break;
          case 'err':
            mswAlert(data['msg'], data['sys'], 'err');
            break;
        }
      }
    });
  });
  return false;
}

function mswLoadSubCatLinks(id) {
  jQuery.ajax({
    url: 'index.php',
    data: 'ajax=loadsubcats&id=' + id,
    dataType: 'json',
    cache: false,
    success: function (data) {
      if (data['parent'] > 0) {
         mswShowSubFAQ(data['parent'], 'show');
      }
    }
  });
  return false;
}

function mswDeptLoader() {
  var curt3 = jQuery('#three').html();
  if (!jQuery('#three').html()) {
    return false;
  }
  if (jQuery('select[name="dept"]').val() == '0') {
    return false;
  }
  jQuery(document).ready(function() {
    mswShowSpinner();
    jQuery.ajax({
      url: 'index.php',
      data: 'ajax=dept&dp=' + jQuery('select[name="dept"]').val(),
      dataType: 'json',
      cache: false,
      success: function(data) {
        mswProcessOK();
        if (data['fields']) {
          jQuery('#three').html((data['fields'] ? data['fields'] : curt3));
          jQuery('.nav-tabs li:nth-child(2)').show();
        } else {
          jQuery('#three').html(curt3);
          jQuery('.nav-tabs li:nth-child(2)').hide();
        }
        if (data['subject']) {
          jQuery('input[name="subject"]').val(data['subject']);
        }
        if (data['comments']) {
          jQuery('textarea[name="comments"]').val(data['comments']);
        }
      }
    });
  });
  return false;
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
        cssClass: 'mswdialog',
        draggable: true,
        onshown: function() {
        }
      });
      break;
    default:
      BootstrapDialog.show({
        title: txt,
        message: msg,
        type: BootstrapDialog.TYPE_PRIMARY,
        id: 'bootlogbox',
        cssClass: 'mswdialog',
        draggable: true,
        onshown: function() {
        }
      });
      break;
  }
}