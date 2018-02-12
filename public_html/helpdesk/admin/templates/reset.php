<?php if (!defined('RESET_LOADER')) { exit; } ?>
<!DOCTYPE html>
<html lang="<?php echo (isset($html_lang) ? $html_lang : 'en'); ?>" dir="<?php echo $lang_dir; ?>">
	<head>
    <meta charset="<?php echo $msg_charset; ?>">

    <title><?php echo $title; ?></title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <link href="templates/css/bootstrap.css" rel="stylesheet">
    <link href="templates/css/theme.css" rel="stylesheet">
    <link href="templates/css/font-awesome/font-awesome.css" rel="stylesheet">
    <link href="templates/css/ms.css" rel="stylesheet">
    <link rel="shortcut icon" href="favicon.ico">

  </head>

	<body style="padding-bottom:50px">

  <div id="mscontainer">

    <form method="post" action="#">
    <div class="container margin-top-container-nonefixed">

      <div class="panel panel-default">
        <div class="panel-heading text-uppercase">
          <i class="fa fa-lock fa-fw"></i> <?php echo $title; ?>
        </div>
        <div class="panel-body">
          <?php echo $msg_passreset; ?>
        </div>
      </div>

    </div>

    <div class="container">

      <div class="table-responsive">
        <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th><?php echo $msg_passreset7; ?></th>
            <th><?php echo $msg_passreset2; ?></th>
            <th><?php echo $msg_passreset3; ?></th>
          </tr>
        </thead>
        <tbody>
          <?php
          $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`,`email`,`accpass`,`name` FROM `" . DB_PREFIX . "users` ORDER BY `name`")
               or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
          while ($U = mysqli_fetch_object($q)) {
          ?>
          <tr>
            <td<?php echo ($U->id == '1' ? ' style="color:red;font-weight:bold"' : ''); ?>><i class="fa fa-user fa-fw"></i> <?php echo mswSafeDisplay($U->name) . ($U->id == '1' ? ' (ADMIN)' : ''); ?><input type="hidden" name="name[]" value="<?php echo mswSafeDisplay($U->name); ?>"></td>
            <td><input type="hidden" name="id[]" value="<?php echo $U->id; ?>"><input type="text" name="mail[]" value="<?php echo mswSafeDisplay($U->email); ?>" class="form-control"></td>
            <td><input type="hidden" name="password2[]" value="<?php echo $U->accpass; ?>"><input type="password" id="<?php echo $U->id; ?>" name="password[]" value="" class="form-control"></td>
          </tr>
          <?php
          }
          ?>
        </tbody>
        </table>
      </div>

      <hr>

      <div class="text-center">
        <div class="form-group">
          <div class="checkbox">
            <label><input type="checkbox" name="autoall" value="1"> <?php echo $msadminlang3_1[24]; ?></label>
          </div>
        </div>
        <button class="btn btn-primary" type="button" onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','pass-reset')"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_passreset4); ?></span></button>
      </div>

    </div>
    </form>

  </div>

  <script src="templates/js/jquery.js"></script>
  <script src="templates/js/msops.js"></script>
  <script src="templates/js/msp.js"></script>
  <script src="templates/js/bootstrap.js"></script>

  <?php
  // Action spinner, DO NOT REMOVE
  ?>
  <div class="overlaySpinner" style="display:none"></div>

  </body>
</html>