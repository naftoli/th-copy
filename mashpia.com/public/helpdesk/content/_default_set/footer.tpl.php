<?php if (!defined('PATH')) { exit; } ?>
    <!--<footer>-->
    <?php
	  // Please don`t remove the footer unless you have purchased a licence..
	  // https://www.maiansupport.com/purchase.html
	  if (LICENCE_VER == 'unlocked' && $this->SETTINGS->publicFooter) {
	  //echo mswCleanData($this->SETTINGS->publicFooter);
	  } else {
	  ?>
<!--	  Powered by: <a href="https://www.maiansupport.com" onclick="window.open(this);return false" title="Maian Support">Maian Support</a><br>-->
<!--    <a href="https://www.maianscriptworld.co.uk" title="Maian Script World" onclick="window.open(this);return false">&copy; 2005 - <?php echo date('Y'); ?> Maian Script World</a>-->
	  <?php
	  }
	  ?>
		<!--</footer>-->

    <script src="<?php echo $this->SYS_BASE_HREF; ?>js/jquery.js"></script>
    <script src="<?php echo $this->SYS_BASE_HREF; ?>js/jquery-ui.js"></script>
    <script src="<?php echo $this->SYS_BASE_HREF; ?>js/bootstrap.js"></script>
    <script src="<?php echo $this->SYS_BASE_HREF; ?>js/plugins/bootstrap.dialog.js"></script>
    <script src="<?php echo $this->SYS_BASE_HREF; ?>js/plugins/jquery.form.js"></script>

    <?php
	  // Load Page Specific JS..
	  echo $this->FILES;

    // Load date picker..
    if (in_array('picker', $this->FILE_LOADER)) {
      include(PATH . '<?php echo $this->SYS_BASE_HREF; ?>js-loader/date-pickers.php');
    }

    // Load recaptcha if enabled..
    if (defined('LOAD_RECAPTCHA')) {
    ?>
    <script src="https://www.google.com/recaptcha/api.js?hl=<?php echo $this->SETTINGS->recaptchaLang; ?>" async defer></script>
    <?php
    }

    // Load print friendly service..
    // Can be customised. For information see:
    // http://www.printfriendly.com/button
    if (defined('PRINT_FRIENDLY')) {
    ?>
    <script>
	  //<![CDATA[
	  var pfHeaderImgUrl      = '';
	  var pfHeaderTagline     = '<?php echo str_replace("'","\'",mswCleanData($this->SETTINGS->website)); ?>';
	  var pfdisableClickToDel = 0;
	  var pfHideImages        = 1;
	  var pfImageDisplayStyle = 'right';
	  var pfDisablePDF        = 0;
	  var pfDisableEmail      = 0;
	  var pfDisablePrint      = 0;
	  var pfCustomCSS         = '';
	  var pfBtVersion         = '1';
	  (function(){
      var js, pf;
      pf      = document.createElement('script');
      pf.type = 'text/javascript';
      if('https:' == document.location.protocol){
        js = 'https://pf-cdn.printfriendly.com/ssl/main.js'
      } else {
        js = 'http://cdn.printfriendly.com/printfriendly.js'
      }
      pf.src = js;
      document.getElementsByTagName('head')[0].appendChild(pf)
	  })();
	  //]]>
	  </script>
    <?php
    }

    // Load file upload dropzone..
    if (in_array('uploader', array_keys($this->FILE_LOADER))) {
    ?>
    <script src="<?php echo $this->SYS_BASE_HREF; ?>js/plugins/jquery.uploader.js"></script>
    <script>
    //<![CDATA[
    jQuery(document).ready(function() {
      jQuery('#dropzone').uploadFile({
        url : 'index.php?ajax=<?php echo $this->DROPZONE['ajax']; ?>',
        maxFileCount : <?php echo $this->DROPZONE['max-files']; ?>,
        maxFileSize: '<?php echo $this->DROPZONE['max-size']; ?>',
        dragDrop: <?php echo $this->DROPZONE['drag']; ?>,
        multiple : <?php echo $this->DROPZONE['multiple']; ?>,
        allowedTypes : '<?php echo $this->DROPZONE['allowed']; ?>',
        returnType : 'json',
        showCancel : false,
        autoSubmit : false,
        showDone : false,
        showError : false,
        showFileSize : true,
        dragDropStr: '<?php echo $this->DROPZONE['txt']; ?>',
        dropzoneDiv: '<?php echo $this->DROPZONE['div']; ?>'
      });
    });
    //]]>
    </script>
    <?php
    }

    // Required files..
    ?>
    <script src="<?php echo $this->SYS_BASE_HREF; ?>js/msops.js"></script>
    <script src="<?php echo $this->SYS_BASE_HREF; ?>js/msp.js"></script>
		<script src="<?php echo $this->SYS_BASE_HREF; ?>js/mashipa/footerfunctions.js?v=2.0"></script> <!-- Custom JS code to run after the helpdesk code has been loaded up... -->
    <?php

    // Load textarea plugin..
    if (in_array('textarea', array_keys($this->FILE_LOADER))) {
    ?>
    <script src="<?php echo $this->SYS_BASE_HREF; ?>js/plugins/jquery.textareafullscreen.js"></script>
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

    // Auto open sub cats..
    if (isset($_GET['c'])) {
    ?>
    <script>
    //<![CDATA[
    jQuery(document).ready(function() {
      mswLoadSubCatLinks('<?php echo (int) $_GET['c']; ?>');
    });
    //]]>
    </script>
    <?php
    }

    // Logout screen has loaded..
    if (defined('LOGGED_OUT_SCR')) {
    ?>
    <script>
    //<![CDATA[
    jQuery(document).ready(function() {
      setTimeout(function() {
        window.location = 'index.php?p=login';
      }, 3000);
    });
    //]]>
    </script>
    <?php
    }

    // Action spinner, DO NOT REMOVE
    ?>
    <div class="overlaySpinner" style="display:none"></div>

</body>
</html>