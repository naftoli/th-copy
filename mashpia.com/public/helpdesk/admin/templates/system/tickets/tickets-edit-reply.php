<?php if (!defined('PATH')) { exit; }
$countOfEnFlds  = mswRowCount('cusfields WHERE `enField` = \'yes\'');
$repType        = ($REPLY->replyType=='admin' ? 'admin' : 'reply');
$qF = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "cusfields`
      WHERE FIND_IN_SET('{$repType}',`fieldLoc`)              > 0
      AND `enField`                                           = 'yes'
			AND FIND_IN_SET('{$SUPTICK->department}',`departments`) > 0
      ORDER BY `orderBy`
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
$countOfCusFields = mysqli_num_rows($qF);
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
      <li><a href="?p=view-<?php echo ($SUPTICK->isDisputed == 'yes' ? 'dispute' : 'ticket'); ?>&amp;id=<?php echo $REPLY->ticketID; ?>"><?php echo ($SUPTICK->isDisputed == 'yes' ? $msg_portal35 : $msg_portal8); ?></a></li>
      <li class="active"><?php echo $msg_viewticket37; ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-reply fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_edit; ?></span></a></li>
          <?php
	        if ($countOfEnFlds > 0 && $countOfCusFields > 0) {
	        ?>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_adheader26; ?></span></a></li>
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
                  <?php
                  // BBCode..
                  include(PATH . 'templates/system/bbcode-buttons.php');
                  ?>
                  <textarea name="comments" rows="15" cols="40" id="comments" tabindex="<?php echo (++$tabIndex); ?>" class="form-control"><?php echo mswSafeDisplay($REPLY->comments); ?></textarea>
                </div>

              </div>
              <?php
              if ($countOfEnFlds > 0) {
              ?>
              <div class="tab-pane fade" id="two">

                <?php
                if ($countOfCusFields > 0) {
                  while ($FIELDS = mysqli_fetch_object($qF)) {
                    $TF = mswGetTableData('ticketfields','ticketID',$REPLY->ticketID,' AND `replyID` = \'' . $REPLY->id . '\' AND `fieldID` = \'' . $FIELDS->id . '\'');
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
              ?>
            </div>
          </div>
          <div class="panel-footer">
           <input type="hidden" name="ticketID" value="<?php echo $SUPTICK->id; ?>">
           <input type="hidden" name="replyID" value="<?php echo $REPLY->id; ?>">
           <button class="btn btn-primary" type="button" onclick="mswProcess('tickrepedit')"><i class="fa fa-check fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_viewticket37; ?></span></button>
           <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=view-ticket&amp;id=<?php echo $REPLY->ticketID; ?>')"><i class="fa fa-times fa-fw"></i> <?php echo mswCleanData($msg_levels11); ?></button>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>