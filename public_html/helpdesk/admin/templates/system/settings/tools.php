<?php if (!defined('PATH')) { exit; }
$resetMsg = @file_get_contents(REL_PATH . 'content/language/'.$SETTINGS->language.'/mail-templates/pass-reset.txt');
define('JS_LOADER', 'tools.php');
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('settings', $userAccess) || $MSTEAM->id == '1') {
      ?>
      <li><a href="index.php?p=settings"><?php echo $msg_adheader2; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo $msg_adheader15; ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <?php
          if (USER_DEL_PRIV == 'yes' || $MSTEAM->id == '1') {
          ?>
          <li class="dropdown active">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-eraser fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_tools13; ?></span><span class="caret"></span></a>
            <ul class="dropdown-menu">
		          <li><a href="#one" data-toggle="tab" onclick="mswResetPurgeFields('f1','tickets')"><?php echo $msg_tools2; ?></a></li>
		          <li><a href="#two" data-toggle="tab" onclick="mswResetPurgeFields('f1','attachments')"><?php echo $msg_tools6; ?></a></li>
		          <li><a href="#five" data-toggle="tab" onclick="mswResetPurgeFields('f1','accounts')"><?php echo $msg_tools26; ?></a></li>
		        </ul>
	        </li>
          <?php
          }
          ?>
	        <li<?php echo (USER_DEL_PRIV == 'no' && $MSTEAM->id != '1' ? ' class="active"' : ''); ?>><a href="#four" data-toggle="tab" onclick="mswResetPurgeFields('f2','')"><i class="fa fa-flag fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_tools24; ?></span></a></li>
          <?php
          if ($MSTEAM->id == '1') {
          ?>
          <li><a href="#three" data-toggle="tab" onclick="mswResetPurgeFields('f3','')"><i class="fa fa-lock fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_tools12; ?></span></a></li>
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
              if (USER_DEL_PRIV == 'yes' || $MSTEAM->id == '1') {
              ?>
              <div class="tab-pane active in" id="one">

                <div class="form-group">
                  <label><?php echo $msg_tools3; ?></label>
                  <input class="form-control" type="text" name="days1" placeholder="<?php echo mswSafeDisplay($msadminlang3_1[20]); ?>" tabindex="<?php echo (++$tabIndex); ?>">
                </div>

                <?php
                $q_dept = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "departments` " . mswSQLDepartmentFilter($mswDeptFilterAccess,'WHERE') . " ORDER BY `orderBy`")
                          or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
                while ($DEPT = mysqli_fetch_object($q_dept)) {
                ?>
                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="dept1[]" value="<?php echo $DEPT->id; ?>" checked="checked"> <?php echo mswCleanData($DEPT->name); ?></label>
                  </div>
                </div>
                <?php
                }
                ?>

                <hr>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="clear" value="yes"> <?php echo $msg_tools5; ?></label>
                  </div>
                </div>

              </div>
              <div class="tab-pane face" id="two">

                <div class="form-group">
                  <label><?php echo $msadminlang3_1[15]; ?></label>
                  <input class="form-control" type="text" name="days2" placeholder="<?php echo mswSafeDisplay($msadminlang3_1[20]); ?>" tabindex="<?php echo (++$tabIndex); ?>" value="">
                </div>

		            <?php
                $q_dept = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "departments` " . mswSQLDepartmentFilter($mswDeptFilterAccess,'WHERE') . " ORDER BY `orderBy`")
                          or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
                while ($DEPT = mysqli_fetch_object($q_dept)) {
                ?>
		            <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="dept2[]" value="<?php echo $DEPT->id; ?>" checked="checked"> <?php echo mswCleanData($DEPT->name); ?></label>
                  </div>
		            </div>
                <?php
                }
                ?>

              </div>
              <div class="tab-pane face" id="five">

                <div class="form-group">
                  <label><?php echo $msadminlang3_1[14]; ?></label>
                  <input class="form-control" type="text" name="days3" placeholder="<?php echo mswSafeDisplay($msadminlang3_1[20]); ?>" tabindex="<?php echo (++$tabIndex); ?>" value="">
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="mail" value="yes"> <?php echo $msg_tools27; ?></label>
                  </div>
                </div>

              </div>
              <?php
              }
              ?>
              <div class="tab-pane face four<?php echo (USER_DEL_PRIV == 'no' && $MSTEAM->id != '1' ? ' active in' : ''); ?>" id="four">

                <?php
                foreach ($batchEnDisFields AS $k => $v) {
                ?>
                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="tbls[]" value="<?php echo $k; ?>" onclick="mswCheckCount('#four','endisbutton','mswCVal')"> <?php echo $v; ?></label>
                  </div>
                </div>
                <?php
                }
                ?>

                <hr>

                <div class="form-group">
                  <label><?php echo $msadminlang3_1[16]; ?></label>
                  <select name="action" class="form-control">
                    <option value="0">- - - - - - -</option>
                    <option value="enable"><?php echo $msg_tools28; ?></option>
                    <option value="disable"><?php echo $msg_tools29; ?></option>
                  </select>
                </div>

              </div>
              <?php
              if ($MSTEAM->id == '1') {
              ?>
              <div class="tab-pane face" id="three">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="visitors" value="yes" onclick="mswCheckResetAcc()"> <?php echo $msg_tools15; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="team" value="yes" onclick="mswCheckResetAcc()"> <?php echo $msg_tools16; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="disabled" value="yes"> <?php echo $msg_tools19; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="sendmail" value="yes" checked="checked" onclick="if(this.checked){jQuery('#msgArea').slideDown()}else{jQuery('#msgArea').hide()}"> <?php echo $msg_tools21; ?></label>
                  </div>
                </div>

                <div id="msgArea">

                 <div class="form-group">
                   <label><?php echo $msg_tools17; ?></label>
                   <textarea name="message" rows="5" class="form-control" cols="20" tabindex="<?php echo (++$tabIndex); ?>"><?php echo mswSafeDisplay($resetMsg); ?></textarea>
                 </div>

                 <div class="form-group">
                   <select class="form-control" onchange="mswSelectMailTag(this.value)">
                     <option value=""><?php echo $msg_tools22; ?></option>
                     <?php
                     foreach ($msg_tools23 AS $k => $v) {
                     ?>
                     <option value="<?php echo $k; ?>"><?php echo $k.' = '.$v; ?></option>
                     <?php
                     }
                     ?>
                   </select>
                   <span class="help-block"><?php echo $msg_tools20; ?>: content/language/<?php echo $SETTINGS->language; ?>/mail-templates/pass-reset.txt</span>
                 </div>

               </div>

              </div>
              <?php
              }
              ?>
            </div>
          </div>
          <?php
          if (USER_DEL_PRIV == 'yes' || $MSTEAM->id == '1') {
          ?>
          <div class="panel-footer" id="f1">
           <input type="hidden" name="type" value="tickets">
           <button class="btn btn-danger" onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','tlpurge');return false;"><i class="fa fa-check fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo mswCleanData($msg_tools4); ?></span></button>
	        </div>
          <?php
          }
          ?>
          <div class="panel-footer" id="f2"<?php echo (USER_DEL_PRIV == 'no' && $MSTEAM->id != '1' ? '' : ' style="display:none"'); ?>>
           <button class="btn btn-danger" id="endisbutton" onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','tlendis');return false;" disabled="disabled"><i class="fa fa-check fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo mswCleanData($msadminlang3_1[13]); ?></span> <span id="mswCVal">(0)</span></button>
	        </div>
          <?php
          if ($MSTEAM->id == '1') {
          ?>
          <div class="panel-footer" id="f3" style="display:none">
           <button class="btn btn-danger" onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','tlreset');return false;" disabled="disabled"><i class="fa fa-check fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo mswCleanData($msg_tools14); ?></span></button>
	        </div>
          <?php
          }
          ?>
        </div>

      </div>
    </div>
    </form>

  </div>