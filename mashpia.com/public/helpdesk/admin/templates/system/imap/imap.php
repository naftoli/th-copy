<?php if (!defined('PATH')) { exit; }
if (isset($_GET['edit'])) {
  $_GET['edit']  = (int)$_GET['edit'];
  $EDIT          = mswGetTableData('imap','id',$_GET['edit']);
  checkIsValid($EDIT);
}
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('imapman', $userAccess) || $MSTEAM->id == '1') {
      ?>
      <li><a href="index.php?p=imapman"><?php echo $msadminlang3_1[4]; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo (isset($EDIT->id) ? $msg_imap25 : $msg_adheader39); ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-envelope-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_imap32; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-cog fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_imap33; ?></span></a></li>
	        <li><a href="#three" data-toggle="tab"><i class="fa fa-edit fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_imap34; ?></span></a></li>
	        <li><a href="#four" data-toggle="tab"><i class="fa fa-cutlery fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msp_imap40; ?></span></a></li>
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
                      <input type="checkbox" name="im_piping" value="yes"<?php echo (isset($EDIT->im_piping) && $EDIT->im_piping=='yes' ? ' checked="checked"' : (!isset($EDIT->im_piping) ? ' checked="checked"' : '')); ?>> <?php echo $msg_imap3; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_imap7; ?></label>
                  <input type="text" class="form-control" maxlength="100" name="im_host" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->im_host) ? $EDIT->im_host : ''); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_imap10; ?></label>
                  <input type="text" class="form-control" maxlength="5" name="im_port" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->im_port) ? $EDIT->im_port : '143'); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_imap8; ?></label>
                  <input type="text" class="form-control" maxlength="250" name="im_user" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->im_user) ? mswSafeDisplay($EDIT->im_user) : ''); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_imap9; ?></label>
                  <input type="password" class="form-control" maxlength="100" name="im_pass" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->im_pass) ? mswSafeDisplay($EDIT->im_pass) : ''); ?>">
                </div>

                <div class="form-group" id="ifolderarea">
                  <label><?php echo $msg_imap11; ?></label>
                  <div class="form-group input-group">
                    <span class="input-group-addon"><a href="#" onclick="if(mswFolderCheck('ifolderarea')){mswShowImapFolders('ifolderarea');return false;}" title="<?php echo mswSafeDisplay($msg_imap31); ?>"><i class="fa fa-folder-open fa-fw"></i></a></span>
                    <input type="text" class="form-control" name="im_name" maxlength="50" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->im_name) ? $EDIT->im_name : 'inbox'); ?>">
			              <select style="display:none" class="form-control" onclick="if(this.value!='0'){mswInsertMailBox('ifolderarea')}"><option>1</option></select>
		              </div>
                </div>

              </div>
              <div class="tab-pane fade" id="two">

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="im_attach" value="yes"<?php echo (isset($EDIT->im_attach) && $EDIT->im_attach=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_imap13; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="im_ssl" value="yes"<?php echo (isset($EDIT->im_ssl) && $EDIT->im_ssl=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_imap16; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_imap12; ?></label>
                  <input type="text" class="form-control" name="im_flags" maxlength="250" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->im_flags) ? $EDIT->im_flags : '/novalidate-cert'); ?>">
                </div>

                <div class="form-group" id="ifolderarea2">
                  <label><?php echo $msg_imap14; ?></label>
                  <div class="form-group input-group">
                    <span class="input-group-addon"><a href="#" onclick="if(mswFolderCheck('ifolderarea2')){mswShowImapFolders('ifolderarea2');return false;}" title="<?php echo mswSafeDisplay($msg_imap31); ?>"><i class="fa fa-folder-open fa-fw"></i></a></span>
                    <input type="text" class="form-control" maxlength="50" tabindex="<?php echo (++$tabIndex); ?>" name="im_move" value="<?php echo (isset($EDIT->im_move) ? $EDIT->im_move : ''); ?>">
                    <select style="display:none" class="form-control" onclick="if(this.value!='0'){mswInsertMailBox('ifolderarea2')}"><option>1</option></select>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_imap15; ?></label>
                  <input type="text" class="form-control" maxlength="3" tabindex="<?php echo (++$tabIndex); ?>" name="im_messages" value="<?php echo (isset($EDIT->im_messages) ? $EDIT->im_messages : '50'); ?>">
                </div>

              </div>
              <div class="tab-pane fade" id="three">

                <div class="form-group">
                  <label><?php echo $msg_imap17; ?></label>
                  <select name="im_dept" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <?php
                  $q_dept = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`,`name` FROM `" . DB_PREFIX . "departments` ORDER BY `orderBy`")
                            or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
                  while ($DEPT = mysqli_fetch_object($q_dept)) {
                  ?>
                  <option value="<?php echo $DEPT->id; ?>"<?php echo (isset($EDIT->im_dept) ? mswSelectedItem($EDIT->im_dept,$DEPT->id) : ''); ?>><?php echo mswSafeDisplay($DEPT->name); ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_imap18; ?></label>
                  <select name="im_priority" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <?php
                  foreach ($ticketLevelSel AS $k => $v) {
                  ?>
                  <option value="<?php echo $k; ?>"<?php echo (isset($EDIT->im_priority) ? mswSelectedItem($EDIT->im_priority,$k) : ''); ?>><?php echo $v; ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_imap19; ?></label>
                  <input type="text" class="form-control" maxlength="250" name="im_email" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo mswCleanData(isset($EDIT->im_email) ? $EDIT->im_email : ''); ?>">
                </div>

              </div>
              <div class="tab-pane fade" id="four">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="im_spam" value="yes"<?php echo (isset($EDIT->im_spam) && $EDIT->im_spam=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msp_imap41; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="im_spam_purge" value="yes"<?php echo (isset($EDIT->im_spam_purge) && $EDIT->im_spam_purge=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_imap42; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_imap63; ?></label>
                  <input type="text" class="form-control" maxlength="10" tabindex="<?php echo (++$tabIndex); ?>" name="im_score" value="<?php echo (isset($EDIT->im_score) ? $EDIT->im_score : ''); ?>">
                </div>

              </div>
            </div>
          </div>
          <div class="panel-footer">
            <input type="hidden" name="<?php echo (isset($EDIT->id) ? 'update' : 'process'); ?>" value="<?php echo (isset($EDIT->id) ? $EDIT->id : '1'); ?>">
            <button class="btn btn-primary" type="button" onclick="mswProcess('imap')"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo (isset($EDIT->id) ? $msg_imap25 : $msg_imap); ?></span></button>
            <?php
            if (in_array('imapman', $userAccess)  || $MSTEAM->id == '1') {
            ?>
            <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=imapman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels11); ?></span></button>
            <?php
            }
            ?>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>