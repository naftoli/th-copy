<?php if (!defined('TICKET_LOADER') || !isset($tickID)) { exit; }
           $sublinks = array();

           // Custom Fields..
           $qT = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `fieldData`,`fieldType`,`fieldInstructions` FROM `" . DB_PREFIX . "ticketfields`
                 LEFT JOIN `" . DB_PREFIX . "cusfields`
                 ON `" . DB_PREFIX . "ticketfields`.`fieldID`      = `" . DB_PREFIX . "cusfields`.`id`
                 WHERE `" . DB_PREFIX . "ticketfields`.`ticketID`  = '{$tickID}'
                 AND `" . DB_PREFIX . "ticketfields`.`replyID`     = '{$dRepID}'
                 AND `" . DB_PREFIX . "ticketfields`.`fieldData`  != 'nothing-selected'
                 AND `" . DB_PREFIX . "ticketfields`.`fieldData`  != ''
                 AND `" . DB_PREFIX . "cusfields`.`enField`        = 'yes'
                 ORDER BY `" . DB_PREFIX . "cusfields`.`orderBy`
                 ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
            $cFCount = mysqli_num_rows($qT);
            if ($cFCount > 0) {
              $sublinks[] = '<a href="#" onclick="mswToggleTicketData(\'' . $toggleID . '\', \'field\');return false"><i class="fa fa-file-text-o fa-fw"></i></a> <span class="hidden-sm hidden-xs">' . $msg_viewticket97 . '</span> (' . $cFCount . ')';
            }

            // Attachments..
            $qA = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,DATE(FROM_UNIXTIME(`ts`)) AS `addDate` FROM `" . DB_PREFIX . "attachments`
                  WHERE `ticketID` = '{$tickID}'
                  AND `replyID` = '{$dRepID}'
                  ORDER BY `fileName`
                  ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
            $aCount = mysqli_num_rows($qA);
            if ($aCount > 0) {
              $sublinks[] = '<span class="attachlink"><a href="#" onclick="mswToggleTicketData(\'' . $toggleID . '\', \'attach\');return false"><i class="fa fa-paperclip fa-fw"></i></a> <span class="hidden-sm hidden-xs">' . $msg_viewticket40 . '</span> (<span class="attachcount">' . $aCount . '</span>)</span>';
            }

            // If something is to display, add a horizontal line..
            if ($cFCount > 0 || $aCount > 0) {
            ?>
            <hr>
            <?php
            }

            if ($cFCount > 0) {
            ?>
            <div class="mswcf" style="display:none">
              <?php
              while ($TS = mysqli_fetch_object($qT)) {
                ?>
                <div class="<?php echo $label; ?>">
                <?php
                switch ($TS->fieldType) {
                  case 'textarea':
                  case 'input':
                  case 'select':
                    ?>
                    <div class="panel-heading"><i class="fa fa-caret-right fa-fw"></i> <?php echo mswSafeDisplay($TS->fieldInstructions); ?></div>
                    <div class="panel-body"><?php echo $MSPARSER->mswTxtParsingEngine($TS->fieldData); ?></div>
                    <?php
                    break;
                  case 'checkbox':
                    ?>
                    <div class="panel-heading"><i class="fa fa-caret-right fa-fw"></i> <?php echo mswSafeDisplay($TS->fieldInstructions); ?></div>
                    <div class="panel-body"><?php echo str_replace('#####', '<br>', mswSafeDisplay($TS->fieldData)); ?></div>
                    <?php
                    break;
                }
                ?>
                </div>
                <?php
              }
              ?>
              <hr>
            </div>
            <?php
            }

            if ($aCount > 0) {
            ?>
            <div class="mswatt" style="display:none">
              <div class="table-responsive">
                <table class="table table-striped table-hover">
                <tbody>
                <?php
                while ($ATT = mysqli_fetch_object($qA)) {
                  $ext    = strrchr($ATT->fileName, '.');
                  $split  = explode('-', $ATT->addDate);
                  $base   = $SETTINGS->attachpath . '/';
                  // Check for newer folder structure..
                  if (file_exists($SETTINGS->attachpath . '/' . $split[0] . '/' . $split[1] . '/' . $ATT->fileName)) {
                    $base = $SETTINGS->attachpath . '/' . $split[0] . '/' . $split[1] . '/';
                  }
                  ?>
                  <tr id="datatrat_<?php echo $ATT->id; ?>">
                    <?php
                    if (file_exists($base . $ATT->fileName)) {
                    ?>
                    <td>[<?php echo substr(strtoupper($ext), 1); ?>] <a href="?attachment=<?php echo $ATT->id; ?>" title="<?php echo mswSafeDisplay($msg_viewticket50); ?>"><?php echo substr($ATT->fileName, 0, strpos($ATT->fileName, '.')); ?></a></td>
                    <?php
                    } else {
                    ?>
                    <td>[<?php echo substr(strtoupper($ext), 1); ?>] <?php echo substr($ATT->fileName, 0, strpos($ATT->fileName, '.')); ?>
                    <span class="does_not_exist"><i class="fa fa-warning fa-fw"></i> <?php echo $msadminlang3_1adminviewticket[17]; ?></span>
                    </td>
                    <?php
                    }
                    ?>
                    <td><?php echo mswFileSizeConversion($ATT->fileSize); ?></td>
                    <?php
                    if (USER_DEL_PRIV == 'yes') {
                    ?>
                    <td><a href="#" onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','tickattdel','<?php echo $ATT->id; ?>');return false;"><i class="fa fa-times fa-fw ms_red"></i></a></td>
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
              <hr>
            </div>
            <?php
            }

            if (!empty($sublinks)) {
            ?>
            <div class="text-right" id="sublinks_<?php echo $dRepID; ?>">
              <?php echo implode(SUBLINK_SEPARATOR, $sublinks); ?>
            </div>
            <?php
            }
            ?>



