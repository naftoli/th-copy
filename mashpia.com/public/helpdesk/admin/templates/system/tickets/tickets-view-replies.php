<?php if (!defined('TICKET_LOADER') || !isset($tickID)) { exit; }

      // Replies..
      $q_replies = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "replies`
                   WHERE `ticketID` = '{$tickID}'
                   ORDER BY `id`
                   ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
      while ($REPLIES = mysqli_fetch_object($q_replies)) {
        switch ($REPLIES->replyType) {
         case 'admin':
           $USER       = mswGetTableData('users', 'id', $REPLIES->replyUser);
           $replyName  = (isset($USER->name) ? mswSafeDisplay($USER->name) : 'N/A');
           $label      = 'panel panel-warning';
           $icon       = 'users';
           break;
         case 'visitor':
           if ($REPLIES->disputeUser > 0) {
             $DU         = mswGetTableData('portal', 'id', $REPLIES->disputeUser, '', '`name`');
             $replyName  = (isset($DU->name) ? mswSafeDisplay($DU->name) : 'N/A');
           } else {
             $USER       = mswGetTableData('portal', 'id', $REPLIES->replyUser, '', '`name`');
             $replyName  = mswSafeDisplay($USER->name);
           }
           $label      = 'panel panel-default';
           $icon       = 'user';
           break;
        }
        ?>
        <div class="<?php echo $label; ?>" id="datarp_<?php echo $REPLIES->id; ?>">
          <div class="panel-heading colorchangeheader left-align">
            <span class="pull-right hidden-xs">(<?php echo $msadminlang3_1adminviewticket[18]; ?>: <?php echo $REPLIES->id; ?>)</span>
            <i class="fa fa-<?php echo $icon; ?> fa-fw"></i> <?php echo $replyName; ?> <span class="mobilebreakpoint"><i class="fa fa-clock-o fa-fw"></i><?php echo $MSDT->mswDateTimeDisplay($REPLIES->ts, $SETTINGS->dateformat).' @ '.$MSDT->mswDateTimeDisplay($REPLIES->ts,$SETTINGS->timeformat); ?></span>
          </div>
          <div class="panel-body" id="rp<?php echo $REPLIES->id; ?>">
            <?php
            echo $MSPARSER->mswTxtParsingEngine($REPLIES->comments);
            if ($REPLIES->replyType == 'admin' && $USER->signature) {
            ?>
            <hr>
            <?php
            echo mswNL2BR($MSPARSER->mswAutoLinkParser(mswSafeDisplay($USER->signature)));
            }
            $dRepID   = $REPLIES->id;
            $toggleID = 'rp' . $REPLIES->id;
            include(PATH . 'templates/system/tickets/tickets-view-data-area.php');
            ?>
          </div>
          <div class="panel-footer <?php echo $REPLIES->replyType; ?>panelfooter">
            <span class="pull-right">
              <?php
              if (USER_EDIT_R_PRIV == 'yes' || USER_DEL_PRIV == 'yes') {
                if (USER_EDIT_R_PRIV == 'yes') {
                ?>
                <a href="?p=edit-reply&amp;id=<?php echo $REPLIES->id; ?>" title="<?php echo mswSafeDisplay($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a>
                <?php
                }
                if (USER_DEL_PRIV == 'yes') {
                ?>
                <a href="#" onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','tickrepdel','<?php echo $REPLIES->id; ?>');return false;" title="<?php echo mswSafeDisplay($msg_script8); ?>"><i class="fa fa-times fa-fw ms_red"></i></a>
                <?php
                }
              } else {
                ?>
                &nbsp;
                <?php
              }
              ?>
            </span>
            <?php echo loadIPAddresses($REPLIES->ipAddresses); ?>
          </div>
        </div>
        <?php
      }

      ?>