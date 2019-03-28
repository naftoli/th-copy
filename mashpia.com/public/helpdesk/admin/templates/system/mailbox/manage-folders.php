<?php if (!defined('PATH')) { exit; } ?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <li><a href="index.php?p=mailbox"><?php echo $msg_mailbox; ?></a></li>
      <li class="active"><?php echo $msg_adheader61; ?> (<?php echo $msg_mailbox6; ?>)</li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <?php
      include(PATH . 'templates/system/mailbox/mailbox-nav.php');
	    $qF = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`,`folder`
            FROM `" . DB_PREFIX . "mailfolders`
            WHERE `staffID` = '{$MSTEAM->id}'
            ORDER BY `folder`
            ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
      $foundRows = mysqli_num_rows($qF);
      ?>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading text-right">
            <button<?php echo ($MSTEAM->mailFolders > 0 && $MSTEAM->mailFolders == $foundRows ? ' disabled="disabled" ' : ' '); ?>class="btn btn-success btn-sm" type="button" onclick="mswMBFolders('add', 0, '<?php echo $MSTEAM->mailFolders; ?>')"><i class="fa fa-plus fa-fw"></i></button>
          </div>
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <?php
                if ($foundRows > 0) {
                while ($FOLDERS = mysqli_fetch_object($qF)) {
                ?>
                <div class="form-group" id="fldr_<?php echo $FOLDERS->id; ?>">
                 <div class="form-group input-group">
                  <span class="input-group-addon"><a href="#" onclick="mswMBFolders('remove', '<?php echo $FOLDERS->id; ?>', 0);return false"><i class="fa fa-trash fa-fw"></i></a></span>
                  <input type="text" class="form-control" maxlength="50" tabindex="<?php echo (++$tabIndex); ?>" name="folder[<?php echo $FOLDERS->id; ?>]" value="<?php echo mswSafeDisplay($FOLDERS->folder); ?>">
                 </div>
                </div>
                <?php
                }
                } else {
                ?>
                <div class="form-group" id="fldr_new">
                 <div class="form-group input-group">
                  <span class="input-group-addon"><a href="#" onclick="mswMBFolders('remove', 'new', 0);return false" title="<?php echo mswSafeDisplay($msg_script47); ?>"><i class="fa fa-trash fa-fw"></i></a></span>
                  <input type="text" class="form-control" maxlength="50" tabindex="<?php echo (++$tabIndex); ?>" name="new[]" value="">
                 </div>
                </div>
                <?php
                define('JS_LOADER', 'mailbox-folders.php');
                }
                ?>

              </div>
            </div>
          </div>
          <div class="panel-footer">
           <button class="btn btn-primary" type="button" onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','mbfolders');return false;"><i class="fa fa-check fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_mailbox12; ?></span></button>
	        </div>
        </div>

      </div>
    </div>
    </form>

  </div>