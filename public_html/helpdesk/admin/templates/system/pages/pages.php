<?php if (!defined('PATH')) { exit; }
if (isset($_GET['edit'])) {
  $_GET['edit']  = (int) $_GET['edit'];
  $EDIT          = mswGetTableData('pages', 'id', $_GET['edit']);
  checkIsValid($EDIT);
}
define('JS_LOADER', 'pages.php');
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('pageman', $userAccess) || $MSTEAM->id == '1') {
      ?>
      <li><a href="index.php?p=pageman"><?php echo $msadminlang3_1cspages[2]; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo (isset($_GET['edit']) ? $msadminlang3_1cspages[3] : $msadminlang3_1cspages[1]); ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msadminlang3_1cspages[4]; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-user fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msadminlang3_1cspages[5]; ?></span></a></li>
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="enPage" value="yes"<?php echo (isset($EDIT->enPage) && $EDIT->enPage=='yes' ? ' checked="checked"' : (!isset($EDIT->enPage) ? ' checked="checked"' : '')); ?>> <?php echo $msadminlang3_1cspages[6]; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input onclick="mswSecureFag(this.checked)" type="checkbox" name="secure" value="yes"<?php echo (isset($EDIT->secure) && $EDIT->secure=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msadminlang3_1cspages[7]; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_response; ?></label>
                  <input type="text" class="form-control" name="title" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->title) ? mswSafeDisplay($EDIT->title) : ''); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msadminlang3_1cspages[8]; ?></label>
                  <?php
                  define('BB_BOX','information');
                  include(PATH . 'templates/system/bbcode-buttons.php');?>
                  <textarea class="form-control" rows="8" cols="40" name="information" id="information" tabindex="<?php echo (++$tabIndex); ?>"><?php echo (isset($EDIT->information) ? mswSafeDisplay($EDIT->information) : ''); ?></textarea>
                </div>

              </div>
              <div class="tab-pane fade" id="two">

                <div class="form-group">
                  <label><?php echo $msadminlang3_1cspages[9]; ?></label>
                  <?php
                  $dis = 'disabled="disabled"';
                  if (isset($EDIT->secure) && $EDIT->secure == 'yes') {
                    $dis = '';
                  }
                  ?>
                  <input type="text" class="form-control" name="search" <?php echo $dis; ?> tabindex="<?php echo (++$tabIndex); ?>" value="">
                </div>

                <?php
                $html = '';
                if (isset($EDIT->accounts) && !in_array($EDIT->accounts, array('','all',null))) {
                  $qA = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`, `name`, `email` FROM `" . DB_PREFIX . "portal`
                        WHERE `id` IN(" . mswSafeImportString($EDIT->accounts) . ")
                        ORDER BY `name`
                        ");
                  while ($ACC = mysqli_fetch_object($qA)) {
                    $rembox = '<a href="#" onclick="mswRemFltrBox(\'' . $ACC->id . '\');return false"><i class="fa fa-times fa-fw ms_red"></i></a>';
                    $html  .= '<p id="acf_' . $ACC->id . '">' . $rembox . ' <input type="hidden" name="acc[]" value="' . $ACC->id . '">' . mswSafeDisplay($ACC->name) . ' (' . mswSafeDisplay($ACC->email) .  ')</p>';
                  }
                }
                ?>
                <div class="accFilterArea"><?php echo $html; ?></div>

              </div>
            </div>
          </div>
          <div class="panel-footer">
           <input type="hidden" name="<?php echo (isset($EDIT->id) ? 'update' : 'process'); ?>" value="<?php echo (isset($EDIT->id) ? $EDIT->id : '1'); ?>">
           <button class="btn btn-primary" type="button" onclick="mswProcess('pages')"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData((isset($EDIT->id) ? $msadminlang3_1cspages[3] : $msadminlang3_1cspages[1])); ?></span></button>
           <?php
           if (in_array('pageman', $userAccess)  || $MSTEAM->id == '1') {
           ?>
           <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=pageman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels11); ?></span></button>
           <?php
           }
           ?>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>