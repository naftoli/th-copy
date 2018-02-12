<?php if (!defined('PATH')) { exit; }
$showAttachUploader = 'yes';
if (isset($_GET['edit'])) {
  $_GET['edit'] = (int)$_GET['edit'];
  $EDIT         = mswGetTableData('faqattach','id',$_GET['edit']);
  checkIsValid($EDIT);
  if ($EDIT->path == '') {
    $showAttachUploader = 'no';
  }
}
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('attachman', $userAccess) || $MSTEAM->id == '1') {
      ?>
      <li><a href="index.php?p=attachman"><?php echo $msg_adheader49; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo (isset($EDIT->id) ? $msg_attachments12 : $msg_attachments2); ?></li>
    </ol>

    <form method="post" action="index.php?ajax=faqattach" enctype="multipart/form-data" id="mswform">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <?php
          if (isset($EDIT->id)) {
          ?>
          <li class="active"><a href="#two" data-toggle="tab"><i class="fa fa-info fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msadminlang3_1faq[16]; ?></span></a></li>
          <?php
          if ($EDIT->path) {
          ?>
          <li><a href="#one" data-toggle="tab"><i class="fa fa-paperclip fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_kbase60; ?></span></a></li>
          <?php
          }
          } else {
          ?>
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-paperclip fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_attachments; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-globe fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_attachments4; ?></span></a></li>
          <?php
          }
          ?>
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <?php
              if ($showAttachUploader == 'yes') {
              ?>
              <div<?php echo (isset($EDIT->id) ? ' class="tab-pane fade"' : ' class="tab-pane active in"'); ?> id="one">

                <div id="dropzone" class="dropzone">
                  <div class="droparea">
                    <?php echo str_replace('{max}', mswFileSizeConversion($MSUPL->getMaxSize()), $msadminlang3_1uploads[7]); ?>
                  </div>
                </div>

              </div>
              <?php
              }
              ?>
              <div<?php echo (isset($EDIT->id) ? ' class="tab-pane active in"' : ' class="tab-pane fade"'); ?> id="two">

                <?php
                if (isset($EDIT->id)) {
                ?>
                <div class="form-group">
                  <label><?php echo $msg_attachments3; ?></label>
                  <input type="text" class="form-control" name="name" value="<?php echo (isset($EDIT->name) ? mswSafeDisplay($EDIT->name) : ''); ?>">
                </div>

                <?php
                if ($EDIT->remote) {
                ?>
                <div class="form-group">
                  <label><?php echo $msg_attachments4; ?></label>
                  <input type="text" class="form-control" name="remote" value="<?php echo (isset($EDIT->remote) ? mswSafeDisplay($EDIT->remote) : ''); ?>">
                </div>
                <?php
                }
                ?>

                <div class="form-group">
                  <label><?php echo $msadminlang3_1faq[17]; ?></label>
                  <input type="text" class="form-control" name="size" value="<?php echo (isset($EDIT->size) ? mswSafeDisplay($EDIT->size) : ''); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msadminlang3_1faq[18]; ?></label>
                  <input type="text" class="form-control" name="mimeType" value="<?php echo (isset($EDIT->mimeType) ? mswSafeDisplay($EDIT->mimeType) : ''); ?>">
                </div>

                <?php
                } else {
                ?>
                <div class="form-group">
                  <span class="pull-right">
                    <i class="fa fa-plus-circle fa-fw cursor_pointer" onclick="mswFaqAttBoxes('add')"></i> <i class="fa fa-minus-circle fa-fw cursor_pointer" onclick="mswFaqAttBoxes('minus')"></i>
                  </span>
                  <label>
                  <?php echo $msadminlang3_1faq[14]; ?></label>
                  <div class="form-group input-group">
                    <span class="input-group-addon">1</span>
                    <input type="text" class="form-control" name="remote[]" tabindex="<?php echo (++$tabIndex); ?>" value="">
                  </div>
                </div>

                <div class="form-group">
                  <div class="form-group input-group">
                    <span class="input-group-addon">2</span>
                    <input type="text" class="form-control" name="remote[]" tabindex="<?php echo (++$tabIndex); ?>" value="">
                  </div>
                </div>

                <div class="form-group">
                  <div class="form-group input-group">
                    <span class="input-group-addon">3</span>
                    <input type="text" class="form-control" name="remote[]" tabindex="<?php echo (++$tabIndex); ?>" value="">
                  </div>
                </div>
                <?php
                }
                ?>

              </div>
            </div>
          </div>
          <div class="panel-footer">
            <input type="hidden" name="<?php echo (isset($EDIT->id) ? 'update' : 'process'); ?>" value="<?php echo (isset($EDIT->id) ? $EDIT->id : '1'); ?>">
            <input type="hidden" name="opath" value="<?php echo (isset($EDIT->path) ? $EDIT->path : ''); ?>">
            <button class="btn btn-primary" type="submit" onclick="mswProcessMultiPart()" id="upbutton"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData((isset($EDIT->id) ? $msg_attachments12 : $msg_attachments2)); ?></span></button>
            <button class="btn btn-link" type="button" onclick="mswDropZoneReload('after')" id="dropzonereload" style="display:none"><i class="fa fa-refresh fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msadminlang3_1uploads[2]); ?></span></button>
            <?php
            if (in_array('attachman', $userAccess)  || $MSTEAM->id == '1') {
            ?>
            <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=attachman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels11); ?></span></button>
            <?php
            }
            ?>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>