<?php if (!defined('PATH') || !isset($SUPTICK->id)) { exit; } ?>
  <div class="fluid-container">

    <div class="panel panel-default">
      <div class="panel-heading">
        (#<?php echo mswTicketNumber($SUPTICK->id); ?>) <?php echo mswSafeDisplay($SUPTICK->subject); ?>
      </div>
      <div class="panel-body">
        <?php
        echo $MSPARSER->mswTxtParsingEngine($SUPTICK->comments);
        $url = '?p=view-' . ($SUPTICK->isDisputed == 'yes' ? 'dispute' : 'ticket') . '&amp;id=' . $SUPTICK->id;
        ?>
      </div>
      <div class="panel-footer text-center">
         <button class="btn btn-primary" type="button" onclick="window.location = '<?php echo $url; ?>'"><i class="fa fa-search fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_open7; ?></span></button>
	    </div>
    </div>

    <?php
    $q_replies = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "replies`
                 WHERE `ticketID` = '{$SUPTICK->id}'
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
      <div class="<?php echo $label; ?>">
        <div class="panel-heading colorchangeheader left-align">
          <i class="fa fa-<?php echo $icon; ?> fa-fw"></i> <?php echo $replyName; ?> <span class="mobilebreakpoint"><i class="fa fa-clock-o fa-fw"></i><?php echo $MSDT->mswDateTimeDisplay($REPLIES->ts, $SETTINGS->dateformat).' @ '.$MSDT->mswDateTimeDisplay($REPLIES->ts,$SETTINGS->timeformat); ?></span>
        </div>
        <div class="panel-body" id="rp<?php echo $REPLIES->id; ?>">
          <?php
          echo $MSPARSER->mswTxtParsingEngine($REPLIES->comments);
          ?>
        </div>
      </div>
      <?php
    }
    ?>

  </div>