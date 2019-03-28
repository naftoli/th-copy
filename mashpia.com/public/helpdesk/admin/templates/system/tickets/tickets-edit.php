<?php if (!defined('PATH') || !isset($SUPTICK->id)) { exit; }
checkIsValid($SUPTICK);
$countOfEnFlds     = mswRowCount('cusfields WHERE `enField` = \'yes\'');
$tickID            = (int) $_GET['id'];
$aCount            = mswRowCount('attachments WHERE `ticketID` = \'' . $tickID . '\' AND `replyID` = \'0\'');
$uCount            = mswRowCount('users WHERE `id` > 0');
// Fields..
$qF = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "cusfields`
      WHERE FIND_IN_SET('ticket',`fieldLoc`)       > 0
      AND `enField`                                = 'yes'
			AND FIND_IN_SET('{$SUPTICK->department}', `departments`) > 0
      ORDER BY `orderBy`
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
$countOfCusFields = mysqli_num_rows($qF);
$tickDept         = mswGetTableData('departments','id', $SUPTICK->department);
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      // Status of ticket for link
      $link = getTicketLink($SUPTICK);
      if ($link[0]) {
      ?>
      <li><a href="index.php?p=<?php echo $link[0]; ?>"><?php echo $link[1]; ?></a></li>
      <?php
      }
      ?>
      <li><a href="?p=view-<?php echo ($SUPTICK->isDisputed == 'yes' ? 'dispute' : 'ticket'); ?>&amp;id=<?php echo $tickID; ?>"><?php echo ($SUPTICK->isDisputed == 'yes' ? $msg_portal35 : $msg_portal8); ?></a></li>
      <li class="active"><?php echo str_replace('{ticket}', mswTicketNumber($SUPTICK->id), $msg_viewticket20); ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-ticket fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_add; ?></span></a></li>
          <?php
          if ($uCount > 0 && $tickDept->manual_assign == 'yes' && $SUPTICK->assignedto != 'waiting' && $SUPTICK->spamFlag == 'no') {
          ?>
          <li id="liusr"><a href="#four" data-toggle="tab"><i class="fa fa-users fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msadminlang3_1adminticketedit[1]; ?></span></a></li>
          <?php
          }
          if ($countOfEnFlds > 0) {
          ?>
          <li id="licus"<?php echo ($countOfCusFields == 0 ? ' style="display:none"' : ''); ?>><a href="#two" data-toggle="tab"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_adheader26; ?></span></a></li>
          <?php
          }
          if ($SETTINGS->attachment == 'yes' && $aCount > 0) {
          ?>
          <li><a href="#three" data-toggle="tab"><i class="fa fa-paperclip fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_attachments; ?></span></a></li>
          <?php
          }
          ?>
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div class="form-group">
                  <label><?php echo $msg_newticket15; ?></label>
                  <input type="text" class="form-control" name="subject" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo mswSafeDisplay($SUPTICK->subject); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_newticket6; ?></label>
                  <select name="dept" tabindex="<?php echo (++$tabIndex); ?>" class="form-control"<?php echo ($countOfCusFields > 0 ? ' onchange="mswDeptLoader(\'two\',\'ticket\',\'0\',\'ticket\')"' : ''); ?>>
                  <?php
                  $q_dept = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "departments` " . mswSQLDepartmentFilter($mswDeptFilterAccess,'WHERE') . " ORDER BY `orderBy`")
                            or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
                  while ($DEPT = mysqli_fetch_object($q_dept)) {
                  ?>
                  <option value="<?php echo $DEPT->id; ?>"<?php echo mswSelectedItem($DEPT->id,$SUPTICK->department); ?>><?php echo mswCleanData($DEPT->name); ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_newticket8; ?></label>
                  <select name="priority" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <?php
                  foreach ($ticketLevelSel AS $k => $v) {
                  ?>
                  <option value="<?php echo $k; ?>"<?php echo mswSelectedItem($k,$SUPTICK->priority); ?>><?php echo $v; ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

		            <div class="form-group">
                  <?php
		              // BBCode..
		              include(PATH . 'templates/system/bbcode-buttons.php');
		              ?>
		              <textarea name="comments" rows="15" cols="40" id="comments" tabindex="<?php echo (++$tabIndex); ?>" class="form-control"><?php echo mswSafeDisplay($SUPTICK->comments); ?></textarea>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_viewticket17; ?></label>
			            <select name="status" class="form-control">
                    <option value="open"<?php echo mswSelectedItem('open',$SUPTICK->ticketStatus); ?>><?php echo $msg_viewticket14; ?></option>
                    <option value="close"<?php echo mswSelectedItem('close',$SUPTICK->ticketStatus); ?>><?php echo $msg_viewticket15; ?></option>
                    <option value="closed"<?php echo mswSelectedItem('closed',$SUPTICK->ticketStatus); ?>><?php echo $msg_viewticket16; ?></option>
                  </select>
                </div>

              </div>
              <?php
              if ($uCount > 0 && $SUPTICK->assignedto != 'waiting' && $SUPTICK->spamFlag == 'no') {
              ?>
              <div class="tab-pane fade" id="four">

                <div class="table-responsive">
                 <table class="table table-striped table-hover">
                 <tbody>
                 <?php
                 $boomUsers = explode(',', $SUPTICK->assignedto);
                 $q_users   = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "users` ORDER BY `name`")
                              or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
                 while ($USERS = mysqli_fetch_object($q_users)) {
                   ?>
                   <tr>
                   <td><input type="checkbox" name="assigned[]" value="<?php echo $USERS->id; ?>"<?php echo (in_array($USERS->id,$boomUsers) ? ' checked="checked"' : ''); ?>></td>
                   <td><?php echo mswCleanData(mswSafeDisplay($USERS->name)); ?></td>
                   <td><?php echo mswCleanData(mswSafeDisplay($USERS->email)); ?></td>
                   <?php
                   if (in_array('users',$userAccess) || $MSTEAM->id == '1') {
                   // Only show global edit id for global user..
                   if ($USERS->id == '1' && $MSTEAM->id == '1') {
                   ?>
                   <td><a href="?p=team&amp;edit=<?php echo $USERS->id; ?>" title="<?php echo mswSafeDisplay($msg_user14); ?>"><i class="fa fa-pencil fa-fw"></i></a></td>
                   <?php
                   } else {
                   if ($USERS->id > '1') {
                   ?>
                   <td><a href="?p=team&amp;edit=<?php echo $USERS->id; ?>" title="<?php echo mswSafeDisplay($msg_user14); ?>"><i class="fa fa-pencil fa-fw"></i></a></td>
                   <?php
                   }
                   }
                   }
                   ?>
                   </tr>
                 <?php
                 }
                 ?>
                 </tbody>
                 </table>
               </div>

              </div>
              <?php
              }
              if ($countOfEnFlds > 0) {
              ?>
              <div class="tab-pane fade" id="two">

                <?php
                if ($countOfCusFields > 0) {
                  while ($FIELDS = mysqli_fetch_object($qF)) {
                    $TF = mswGetTableData('ticketfields','ticketID',(int) $tickID,' AND `replyID` = \'0\' AND `fieldID` = \'' . $FIELDS->id . '\'');
                    switch ($FIELDS->fieldType) {
                      case 'textarea':
                        echo $MSFM->buildTextArea(mswCleanData($FIELDS->fieldInstructions),$FIELDS->id,(++$tabIndex),(isset($TF->fieldData) ? $TF->fieldData : ''));
                        break;
                      case 'input':
                        echo $MSFM->buildInputBox(mswCleanData($FIELDS->fieldInstructions),$FIELDS->id,(++$tabIndex),(isset($TF->fieldData) ? $TF->fieldData : ''));
                        break;
                      case 'select':
                        echo $MSFM->buildSelect(mswCleanData($FIELDS->fieldInstructions),$FIELDS->id,$FIELDS->fieldOptions,(++$tabIndex),(isset($TF->fieldData) ? $TF->fieldData : ''));
                        break;
                      case 'checkbox':
                        echo $MSFM->buildCheckBox(mswCleanData($FIELDS->fieldInstructions),$FIELDS->id,$FIELDS->fieldOptions,(isset($TF->fieldData) ? $TF->fieldData : ''));
                        break;
                    }
                  }
                } else {
                  echo '<i class="fa fa-warning fa-fw"></i> ' . $msadminlang3_1adminticketedit[0];
                }
                ?>

              </div>
              <?php
              }
              if ($SETTINGS->attachment == 'yes' && $aCount > 0) {
              ?>
              <div class="tab-pane fade" id="three">

               <div class="table-responsive">
                 <table class="table table-striped table-hover">
                 <tbody>
                 <?php
                 $qA = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,DATE(FROM_UNIXTIME(`ts`)) AS `addDate` FROM `" . DB_PREFIX . "attachments`
                       WHERE `ticketID` = '{$tickID}' AND `replyID` = '0'
                       ORDER BY `fileName`
                       ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
                 while ($ATT = mysqli_fetch_object($qA)) {
                   $ext     = strrchr($ATT->fileName, '.');
                   $split   = explode('-', $ATT->addDate);
                   $folder  = '';
                   // Check for newer folder structure..
                   if (file_exists($SETTINGS->attachpath . '/' . $split[0] . '/' . $split[1] . '/' . $ATT->fileName)) {
                     $folder  = $split[0] . '/' . $split[1] . '/';
                   }
                   ?>
                   <tr id="attrow<?php echo $ATT->id; ?>">
                   <td>[<?php echo substr(strtoupper($ext),1); ?>] <a href="?attachment=<?php echo $ATT->id; ?>" title="<?php echo mswSafeDisplay($msg_viewticket50); ?>"><?php echo substr($ATT->fileName,0,strpos($ATT->fileName,'.')); ?></a></td>
                   <td><?php echo mswFileSizeConversion($ATT->fileSize); ?></td>
                   <?php
                   if (USER_DEL_PRIV == 'yes') {
                   ?>
                   <td>
                   <a href="#" onclick="mswRowForDel('attrow<?php echo $ATT->id; ?>','attachment');return false"><i class="fa fa-times fa-fw ms_red"></i></a>
                   </td>
                   <?php
                   }
                   ?>
                   </tr>
                 <?php
                 }
                 ?>
                 </tbody>
                 </table>
               </div>

              </div>
              <?php
              }
              ?>
            </div>
          </div>
          <div class="panel-footer">
            <input type="hidden" name="odeptid" value="<?php echo $SUPTICK->department; ?>">
            <input type="hidden" name="id" value="<?php echo $tickID; ?>">
            <input type="hidden" name="area" value="ticket">
            <button class="btn btn-primary" type="button" onclick="mswProcess('tickedit')"><i class="fa fa-check fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_viewticket21; ?></span></button>
            <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=view-ticket&amp;id=<?php echo $tickID; ?>')"><i class="fa fa-times fa-fw"></i> <?php echo $msg_levels11; ?></button>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>