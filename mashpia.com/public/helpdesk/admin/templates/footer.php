<?php if (!defined('PATH') || !isset($footerSlideMenu)) { exit; } ?>
    <footer>
    <?php
	  // Please don`t remove the footer unless you have purchased a licence..
	  // https://www.maiansupport.com/purchase.html
	  if (LICENCE_VER == 'unlocked' && $SETTINGS->adminFooter) {
	  echo mswCleanData($SETTINGS->adminFooter);
	  } else {
	  ?>
	  Powered by: <a href="https://www.maiansupport.com" onclick="window.open(this);return false" title="Maian Support">Maian Support</a><br>
    <a href="https://www.maianscriptworld.co.uk" title="Maian Script World" onclick="window.open(this);return false">&copy; 2005 - <?php echo date('Y'); ?> Maian Script World</a>
	  <?php
	  }
	  ?>
		</footer>

    <script src="templates/js/jquery.js"></script>
    <script src="templates/js/jquery-ui.js"></script>
    <script src="templates/js/bootstrap.js"></script>
    <script src="templates/js/plugins/bootstrap.dialog.js"></script>
    <?php
    if (isset($loadiBox)) {
    ?>
    <script src="templates/js/plugins/jquery.ibox.js"></script>
    <?php
    }
    ?>
    <script src="templates/js/plugins/jquery.mmenu.js"></script>
    <script src="templates/js/msops.js"></script>
    <script src="templates/js/msp.js"></script>

    <?php
    include(PATH . 'templates/js-loader/left-slide-panel.php');

    if (defined('LOAD_DATE_PICKERS')) {
      include(PATH . 'templates/js-loader/date-pickers.php');
    }

    if (isset($loadJQPlot)) {
    ?>
    <script src="templates/js/jqplot/jquery.jqplot.min.js"></script>
    <script src="templates/js/jqplot/jqplot.logAxisRenderer.min.js"></script>
    <script src="templates/js/jqplot/jqplot.canvasTextRenderer.min.js"></script>
    <script src="templates/js/jqplot/jqplot.canvasAxisLabelRenderer.min.js"></script>
    <script src="templates/js/jqplot/jqplot.canvasAxisTickRenderer.min.js"></script>
    <script src="templates/js/jqplot/jqplot.dateAxisRenderer.min.js"></script>
    <script src="templates/js/jqplot/jqplot.categoryAxisRenderer.min.js"></script>
    <script src="templates/js/jqplot/jqplot.barRenderer.min.js"></script>
    <script src="templates/js/jqplot/jqplot.highlighter.min.js"></script>
    <?php
    }

    if (defined('JS_LOADER')) {
      include(PATH . 'templates/js-loader/' . JS_LOADER);
    }

    // Load first file upload dropzone..
    if (isset($mswUploadDropzone2['ajax'])) {
    ?>
    <script src="templates/js/plugins/jquery.form.js"></script>
    <script src="templates/js/plugins/jquery.uploader2.js"></script>
    <script>
    //<![CDATA[
    jQuery(document).ready(function() {
      jQuery('#dropzone').uploadFile({
        url : 'index.php?ajax=<?php echo $mswUploadDropzone2['ajax']; ?>',
        maxFileCount : <?php echo $mswUploadDropzone2['max-files']; ?>,
        maxFileSize: '<?php echo $mswUploadDropzone2['max-size']; ?>',
        dragDrop: <?php echo $mswUploadDropzone2['drag']; ?>,
        multiple : <?php echo $mswUploadDropzone2['multiple']; ?>,
        allowedTypes : '*',
        returnType : 'json',
        showCancel : false,
        autoSubmit : false,
        showDone : false,
        showError : false,
        showFileSize : true,
        dragDropStr: '<?php echo str_replace("'", "\'", $msadminlang3_1uploads[5]); ?>',
        dropzoneDiv: '<?php echo $mswUploadDropzone2['div']; ?>'
      });
    });
    //]]>
    </script>
    <?php
    }

    if (isset($mswUploadDropzone['ajax'])) {
    ?>
    <script src="templates/js/plugins/jquery.form.js"></script>
    <script src="templates/js/plugins/jquery.uploader.js"></script>
    <script>
    //<![CDATA[
    jQuery(document).ready(function() {
      jQuery('#dropzone').uploadFile({
        url : 'index.php?ajax=<?php echo $mswUploadDropzone['ajax']; ?>',
        maxFileCount : <?php echo $mswUploadDropzone['max-files']; ?>,
        maxFileSize: '<?php echo $MSUPL->getMaxSize(); ?>',
        dragDrop: <?php echo $mswUploadDropzone['drag']; ?>,
        multiple : <?php echo $mswUploadDropzone['multiple']; ?>,
        returnType : 'json',
        showCancel : false,
        showAbort : true,
        showDone : false,
        showError : false,
        showDelete : false,
        showDownload : false,
        showFileSize : true,
        abortStr : '<?php echo str_replace("'", "\'", $msadminlang3_1uploads[1]); ?>',
        onSelect : function(files) {
          <?php
          switch($mswUploadDropzone['ajax']) {
            case 'faqimport-upload':
            case 'srimport-upload':
            case 'accimp-upload':
              ?>
              jQuery('div[class="ajax-file-upload"]').slideUp();
              <?php
              break;
          }
          ?>
        },
        onSuccess : function(files, data, xhr, pd) {
          switch(data['msg']) {
            case 'ok':
              <?php
              switch($mswUploadDropzone['ajax']) {
                case 'faqimport-upload':
                case 'srimport-upload':
                case 'accimp-upload':
                  ?>
                  jQuery('#upbutton').prop('disabled', false);
                  jQuery('#dropzonereload').show();
                  if (data['importrows'] > 0) {
                    jQuery('#upbutton').append(' <span id="improws">(' + data['importrows'] + ')</span> ');
                  }
                  var updata = jQuery('div[class="ajax-file-upload"]').html();
                  jQuery('div[class="ajax-file-upload"]').html('');
                  jQuery('body').append('<div id="hiddendatadiv" style="display:none">' + updata + '</div>');
                  <?php
                  break;
              }
              ?>
              break;
            case 'err':
              mswAlert(data['info'], data['sys'], 'err');
              setTimeout(function() {
                mswShowSpinner();
                window.location.reload();
              }, 1000);
              break;
          }
        }
      });
    });
    //]]>
    </script>
    <?php
    }

    if (isset($textareaFullScr)) {
    ?>
    <script src="templates/js/plugins/jquery.textareafullscreen.js"></script>
    <script>
    //<![CDATA[
    jQuery(document).ready(function() {
      jQuery('textarea').textareafullscreen({
        overlay: true,
        maxWidth: '80%',
        maxHeight: '80%'
      });
    });
    //]]>
    </script>
    <?php
    }

    if ($MSTEAM->mailbox == 'yes' && MAILBOX_UNREAD_REFRESH_TIME > 0) {
    ?>
    <script>
    //<![CDATA[
    jQuery(document).ready(function() {
      setInterval(function () {
        mswUnreadFlag();
      }, <?php echo MAILBOX_UNREAD_REFRESH_TIME; ?>);
    });
    //]]>
    </script>
    <?php
    }
    ?>

    <div id="leftpanelmenu">
		  <?php
      // Left slider menu..
      echo $footerSlideMenu;
      ?>
		</div>

    <?php
    // Action spinner, DO NOT REMOVE
    ?>
    <div class="overlaySpinner" style="display:none"></div>

    </div>

  </body>
</html>