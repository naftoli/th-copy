<?php if (!defined('PATH')) { exit; }
$countOfCusFields  = mswRowCount('cusfields WHERE `enField` = \'yes\'');
$countOfOtherUsers = mswRowCount('users WHERE `id` > 0');
$dept              = array();
include(REL_PATH . 'control/classes/class.upload.php');
$MSUPL     = new msUpload();
$aMaxFiles = (LICENCE_VER == 'locked' && $SETTINGS->attachboxes > RESTR_ATTACH ? RESTR_ATTACH : '9999999');
$mSize     = $MSUPL->getMaxSize();
$mswUploadDropzone2 = array(
  'ajax' => 'ticket',
  'multiple' => ($aMaxFiles > 1 ? 'true' : 'false'),
  'max-files' => $aMaxFiles,
  'max-size' => $mSize,
  'drag' => 'false',
  'div' => 'four'
);
define('JS_LOADER', 'add-ticket.php');
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <li class="active"><?php echo $msg_open; ?></li>
    </ol>

    <form method="post" action="index.php?ajax=ticket" enctype="multipart/form-data" id="mswform">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_add; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-user fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_add5; ?></span></a></li>
          <?php
          if ($countOfCusFields > 0) {
          ?>
          <li id="licus"><a href="#three" data-toggle="tab"><i class="fa fa-list-alt fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_adheader26; ?></span></a></li>
          <?php
          }
          if ($SETTINGS->attachment == 'yes') {
          ?>
          <li><a href="#four" data-toggle="tab"><i class="fa fa-paperclip fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_add3; ?></span></a></li>
          <?php
          }
          ?>
          <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-wrench fa-fw" title="<?php echo mswSafeDisplay($msg_settings85); ?>"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_settings85; ?></span> <span class="caret"></span></a>
            <ul class="dropdown-menu dropdown-menu-right" role="menu">
            <?php
            if ($countOfOtherUsers > 0) {
            ?>
            <li id="liusr"><a href="#five" data-toggle="tab"><?php echo $msadminlang3_1adminticketedit[1]; ?></a></li>
            <?php
            }
            ?>
            <li><a href="#six" data-toggle="tab"><?php echo $msg_accounts18; ?></a></li>
          </ul>
          </li>
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div class="form-group">
                  <label><?php echo $msg_newticket15; ?></label>
                  <input type="text" class="form-control" name="subject" tabindex="<?php echo (++$tabIndex); ?>" value="">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_newticket6; ?></label>
                  <select name="dept" tabindex="<?php echo (++$tabIndex); ?>" class="form-control"<?php echo ($countOfCusFields > 0 ? ' onchange="mswDeptLoader(\'three\',\'add\',\'0\',\'ticket\')"' : ''); ?>>
                  <?php
                  $q_dept = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`,`name` FROM `" . DB_PREFIX . "departments` " . mswSQLDepartmentFilter($mswDeptFilterAccess,'WHERE') . " ORDER BY `orderBy`")
                            or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
                  while ($DEPT = mysqli_fetch_object($q_dept)) {
                  $dept[] = $DEPT->id;
                  ?>
                  <option value="<?php echo $DEPT->id; ?>"><?php echo mswCleanData($DEPT->name); ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_newticket8; ?></label>
                  <select name="priority" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <?php
                  if (!empty($ticketLevelSel)) {
                  foreach ($ticketLevelSel AS $k => $v) {
                  ?>
                  <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                  <?php
                  }
                  }
                  ?>
                  </select>
                </div>

                <div class="form-group">
                  <?php
                  // BBCode..
                  include(PATH . 'templates/system/bbcode-buttons.php');
                  ?>
                  <textarea name="comments" rows="15" cols="40" id="comments" tabindex="<?php echo (++$tabIndex); ?>" class="form-control"></textarea>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="closed" value="yes"> <?php echo $msg_add13; ?></label>
                  </div>
                </div>

              </div>
              <div class="tab-pane fade" id="two">

                <div class="form-group">
                  <label><?php echo $msg_viewticket2; ?></label>
                  <div class="form-group input-group">
                    <span class="input-group-addon"><a href="#" onclick="mswSearchAccounts('name',0);return false" title="<?php echo mswSafeDisplay($msg_add6); ?>"><i class="fa fa-search fa-fw"></i> </a></span>
                    <input type="text" class="form-control" name="name" tabindex="<?php echo (++$tabIndex); ?>" value="">
                  </div>
		            </div>

                <div class="form-group accntn" style="display:none">
                  <select name="accntn" class="form-control" onchange="mswSelectAccount(this.value,'name')"></select>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_viewticket3; ?></label>
                  <div class="form-group input-group">
                    <span class="input-group-addon"><a href="#" onclick="mswSearchAccounts('email',0);return false" title="<?php echo mswSafeDisplay($msg_add6); ?>"><i class="fa fa-search fa-fw"></i> </a></span>
                    <input type="text" class="form-control" name="email" tabindex="<?php echo (++$tabIndex); ?>" value="">
                  </div>
                </div>

                <div class="form-group accnte" style="display:none">
                  <select name="accnte" class="form-control" onchange="mswSelectAccount(this.value,'email')"></select>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="accMail" value="yes" checked="checked"> <?php echo $msg_viewticket18; ?></label>
                  </div>
                </div>

              </div>
              <?php
              if ($countOfCusFields > 0 && isset($dept[0])) {
              ?>
              <div class="tab-pane fade" id="three">

                <?php
                // Custom fields..
                $qF = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "cusfields`
                      WHERE FIND_IN_SET('ticket',`fieldLoc`)   > 0
                      AND `enField`                            = 'yes'
                      AND FIND_IN_SET('{$dept[0]}',`departments`) > 0
                      ORDER BY `orderBy`
                      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
                if (mysqli_num_rows($qF) > 0) {
                  while ($FIELDS = mysqli_fetch_object($qF)) {
                    switch ($FIELDS->fieldType) {
                      case 'textarea':
                        echo $MSFM->buildTextArea(mswCleanData($FIELDS->fieldInstructions),$FIELDS->id,(++$tabIndex));
                        break;
                      case 'input':
                        echo $MSFM->buildInputBox(mswCleanData($FIELDS->fieldInstructions),$FIELDS->id,(++$tabIndex));
                        break;
                      case 'select':
                        echo $MSFM->buildSelect(mswCleanData($FIELDS->fieldInstructions),$FIELDS->id,(++$tabIndex),$FIELDS->fieldOptions);
                        break;
                      case 'checkbox':
                        echo $MSFM->buildCheckBox(mswCleanData($FIELDS->fieldInstructions),$FIELDS->id,$FIELDS->fieldOptions);
                        break;
                    }
                  }
                } else {
                  echo '<i class="fa fa-warning fa-fw"></i>' . $msadminlang3_1[6];
                }
                ?>

              </div>
              <?php
              }
              if ($SETTINGS->attachment == 'yes') {
              ?>
              <div class="tab-pane fade" id="four">

                <div id="dropzone" class="dropzone">
                  <div class="droparea">
                    <?php echo str_replace('{max}', mswFileSizeConversion($mSize), $msadminlang3_1uploads[6]); ?>
                  </div>
                </div>

              </div>
              <?php
              }
              if ($countOfOtherUsers > 0) {
              ?>
              <div class="tab-pane fade" id="five">

                <?php
                $q_users  = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "users` ORDER BY `name`")
                            or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
                while ($USERS = mysqli_fetch_object($q_users)) {
                ?>
                <div class="form-group">
                    <div class="checkbox">
                    <label><input type="checkbox" name="assigned[]" value="<?php echo $USERS->id; ?>" onclick="if(this.checked){mswUncheckAssigned('box')}"> <?php echo mswCleanData($USERS->name); ?></label>
                  </div>
                </div>
                <?php
                }
                ?>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="waiting" value="yes" onclick="if(this.checked){mswUncheckAssigned('wait')}"> <?php echo $msg_add10; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="assignMail" value="yes" checked="checked"> <?php echo $msg_viewticket18 . ' ' . $msg_add12; ?></label>
                  </div>
                </div>

              </div>
              <?php
              }
              ?>
              <div class="tab-pane fade" id="six">

                <div class="form-group">
                  <textarea name="notes" rows="15" cols="40" tabindex="<?php echo (++$tabIndex); ?>" class="form-control"></textarea>
                </div>

              </div>
            </div>
          </div>
          <div class="panel-footer">
            <input type="hidden" name="process" value="1">
           <button class="btn btn-primary" type="submit" onclick="mswProcessMultiPart()"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_open; ?></span></button>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>