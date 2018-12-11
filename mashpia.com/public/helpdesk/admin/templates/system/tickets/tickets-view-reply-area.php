<?php if (!defined('TICKET_LOADER') || !isset($tickID)) { exit; }
  // Ticket Reply Area..
  if (in_array($SUPTICK->ticketStatus, array('open','closed')) && $SUPTICK->spamFlag == 'no' && $SUPTICK->assignedto != 'waiting') {
  include(REL_PATH . 'control/classes/class.upload.php');
  $MSUPL     = new msUpload();
  $aMaxFiles = (LICENCE_VER == 'locked' && $SETTINGS->attachboxes > RESTR_ATTACH ? RESTR_ATTACH : '9999999');
  $mSize     = $MSUPL->getMaxSize();
  $mswUploadDropzone2 = array(
    'ajax' => 'tickreply',
    'multiple' => ($aMaxFiles > 1 ? 'true' : 'false'),
    'max-files' => $aMaxFiles,
    'max-size' => $mSize,
    'drag' => 'false',
    'div' => 'four'
  );
  define('JS_LOADER', 'ticket-reply.php');
  // Custom  fields..
  $qF = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "cusfields`
        WHERE FIND_IN_SET('admin',`fieldLoc`) > 0
        AND `enField`                         = 'yes'
        ORDER BY `orderBy`
        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
  $cusFieldRows = mysqli_num_rows($qF);
  // Standard responses..
  $numResp = mswRowCount('responses WHERE `enResponse` = \'yes\' AND FIND_IN_SET(\'' . $SUPTICK->department . '\',`departments`) > 0');
  ?>
  <div class="row" id="replyArea">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
      <ul class="nav nav-tabs">
        <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-quote-left fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msadminlang3_1adminviewticket[14]; ?></span></a></li>
        <li><a href="#two" data-toggle="tab"><i class="fa fa-commenting-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_viewticket12; ?></span></a></li>
        <?php
        if ($cusFieldRows > 0) {
        ?>
        <li><a href="#three" data-toggle="tab"><i class="fa fa-list fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_adheader26; ?></span></a></li>
        <?php
        }
        ?>
        <li><a href="#four" data-toggle="tab"><i class="fa fa-paperclip fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_attachments; ?></span></a></li>
        <li><a href="#five" data-toggle="tab"><i class="fa fa-cog fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_accounts8; ?></span></a></li>
      </ul>
    </div>
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="tab-content">
            <div class="tab-pane active in" id="one">

              <div class="form-group">
                <?php
                include(PATH . 'templates/system/bbcode-buttons.php');
                ?>
                <textarea name="comments" rows="15" cols="40" id="comments" class="form-control" tabindex="<?php echo (++$tabIndex); ?>"></textarea>
              </div>

            </div>
            <div class="tab-pane fade" id="two">

              <div class="form-group">
                <label><?php echo $msadminlang3_1adminviewticket[12]; ?></label>
                <input type="text" name="sresp" value="" class="form-control" tabindex="<?php echo (++$tabIndex); ?>">
              </div>

              <?php
              if (in_array('standard-responses', $userAccess) || $MSTEAM->id == '1') {
              ?>
              <div class="form-group">
                <label><?php echo $msadminlang3_1adminviewticket[13]; ?></label>
                <input type="text" class="form-control" name="response" value="" tabindex="<?php echo (++$tabIndex); ?>">
                <input type="hidden" name="dept[]" value="<?php echo $SUPTICK->department; ?>">
              </div>
              <?php
              }
              ?>

            </div>
            <?php
            if ($cusFieldRows > 0) {
            ?>
            <div class="tab-pane fade" id="three">

              <?php
              while ($FIELDS = mysqli_fetch_object($qF)) {
                switch ($FIELDS->fieldType) {
                  case 'textarea':
                    echo $MSFM->buildTextArea(mswCleanData($FIELDS->fieldInstructions),$FIELDS->id,(++$tabIndex));
                    break;
                  case 'input':
                    echo $MSFM->buildInputBox(mswCleanData($FIELDS->fieldInstructions),$FIELDS->id,(++$tabIndex));
                    break;
                  case 'select':
                    echo $MSFM->buildSelect(mswCleanData($FIELDS->fieldInstructions),$FIELDS->id,$FIELDS->fieldOptions,(++$tabIndex));
                    break;
                  case 'checkbox':
                    echo $MSFM->buildCheckBox(mswCleanData($FIELDS->fieldInstructions),$FIELDS->id,$FIELDS->fieldOptions);
                    break;
                }
              }
              ?>

            </div>
            <?php
            }
            ?>
            <div class="tab-pane fade" id="four">

              <div id="dropzone" class="dropzone">
                <div class="droparea">
                  <?php echo str_replace('{max}', mswFileSizeConversion($mSize), $msadminlang3_1uploads[6]); ?>
                </div>
              </div>

            </div>
            <div class="tab-pane fade" id="five">

              <?php
              // Merging only allowed for standard tickets..
              if (TICKET_TYPE == 'ticket' && ($MSTEAM->id == '1' || $MSTEAM->mergeperms == 'yes')) {
              ?>
              <div class="form-group">
                <label><?php echo $msg_viewticket102; ?></label>
                <input type="text" class="form-control" value="" name="mergeid" onkeyup="mswMergeClear()">
              </div>
              <?php
              }
              ?>

              <div class="form-group">
                <label><?php echo $msg_viewticket17; ?></label>
                <select name="status" class="form-control">
                  <option value="open" selected="selected"><?php echo $msg_viewticket14; ?></option>
                  <option value="close"><?php echo $msg_viewticket15; ?></option>
                  <option value="closed"><?php echo $msg_viewticket16; ?></option>
                </select>
              </div>

              <div class="form-group">
                <label><?php echo $msg_viewticket18; ?></label>
                <select name="mail" class="form-control">
                <option value="yes" selected="selected"><?php echo $msg_script4; ?></option>
                <option value="no"><?php echo $msg_script5; ?></option>
                </select>
              </div>

              <?php
              if ($MSTEAM->id == '1') {
              ?>
              <div class="form-group">
                <div class="checkbox">
                  <label><input type="checkbox" name="history" value="yes" checked="checked"> <?php echo $msg_viewticket109; ?></label>
                </div>
              </div>
              <?php
              }
              ?>

            </div>
          </div>
        </div>
        <?php
        if ($SUPTICK->ticketStatus == 'closed') {
          $msg_viewticket13 = $msadminlang3_1adminviewticket[24];
        }
        ?>
        <div class="panel-footer">
          <input type="hidden" name="ticketID" value="<?php echo $tickID; ?>">
          <button class="btn btn-primary" type="submit" onclick="mswProcessMultiPart()"><i class="fa fa-check fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo mswCleanData($msg_viewticket13); ?></span></button>
	      </div>
      </div>
    </div>

    <?php
    // History..
    include(PATH . 'templates/system/tickets/tickets-view-history.php');
    ?>

  </div>
  <?php
  } else {
  ?>
  <div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
      <?php
      $url = (TICKET_TYPE == 'dispute' ? '?p=view-dispute&amp;id=' . $tickID . '&amp;act=reopen' : '?p=view-ticket&amp;id=' . $tickID . '&amp;act=reopen');
      if ($SUPTICK->spamFlag == 'yes') {
        $msg = $msg_spam3;
      } elseif ($SUPTICK->assignedto == 'waiting') {
        $msg = $msadminlang3_1adminviewticket[7];
      } else {
        $msg = str_replace('{url}', $url, $msg_viewticket45);
      }
      ?>
      <div class="alert alert-danger"><i class="fa fa-warning fa-fw"></i> <?php echo $msg; ?></div>
    </div>

    <?php
    // History..
    include(PATH . 'templates/system/tickets/tickets-view-history.php');
    ?>

  </div>
  <?php
  }
  ?>