<?php if (!defined('PATH') || !isset($MID)) { exit; }
// Who started this message?
if ($MMSG->staffID == $MSTEAM->id) {
  $msgPoster = mswCleanData($MSTEAM->name);
} else {
  $PST       = mswGetTableData('users','id',$MMSG->staffID);
  $msgPoster = (isset($PST->name) ? mswCleanData($PST->name) : 'N/A');
}
define('JS_LOADER', 'print-friendly.php');
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <li><a href="index.php?p=mailbox"><?php echo $msg_mailbox; ?></a></li>
      <li class="active"><?php echo $msg_adheader61; ?> (<?php echo $msg_mailbox7; ?>)</li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <?php
      include(PATH . 'templates/system/mailbox/mailbox-nav.php');
	    ?>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-envelope-o fa-fw"></i> <?php echo mswSafeDisplay($MMSG->subject); ?>
          </div>
          <div class="panel-body">
            <?php
            echo $MSPARSER->mswTxtParsingEngine($MMSG->message);
            ?>
          </div>
          <div class="panel-footer">
            <i class="fa fa-user fa-fw"></i><?php echo mswSafeDisplay($msgPoster); ?> <i class="fa fa-clock-o fa-fw"></i> <?php echo $MSDT->mswDateTimeDisplay($MMSG->ts, $SETTINGS->dateformat) . ' @ ' . $MSDT->mswDateTimeDisplay($MMSG->ts, $SETTINGS->timeformat); ?>
		      </div>
        </div>

        <div class="text-center">
          <input type="hidden" name="msgStaff" value="<?php echo $MMSG->staffID; ?>">
		      <input type="hidden" name="msgID" value="<?php echo $MID; ?>">
          <input type="hidden" name="subject" value="<?php echo mswSafeDisplay($MMSG->subject); ?>">
          <textarea name="message" rows="5" cols="20" tabindex="<?php echo (++$tabIndex); ?>" class="form-control"></textarea><br><br>
          <button class="btn btn-primary" type="button" onclick="mswProcess('mbreply')"><i class="fa fa-check fa-fw"></i> <?php echo $msg_mailbox30; ?></button>
        </div>

        <?php
        $qPMR = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,`" . DB_PREFIX . "mailreplies`.`ts` AS `repStamp` FROM `" . DB_PREFIX . "mailreplies`
		            LEFT JOIN `" . DB_PREFIX . "users`
				        ON `" . DB_PREFIX . "mailreplies`.`staffID` = `" . DB_PREFIX . "users`.`id`
                WHERE `mailID` = '{$MMSG->id}'
                ORDER BY `" . DB_PREFIX . "mailreplies`.`id`
				        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
        if (mysqli_num_rows($qPMR)>0) {
        ?>
        <hr>
        <?php
		    while ($REPLIES = mysqli_fetch_object($qPMR)) {
        ?>
        <div class="panel panel-default">
          <div class="panel-body">
            <?php
            echo $MSPARSER->mswTxtParsingEngine($REPLIES->message);
            ?>
          </div>
          <div class="panel-footer">
            <i class="fa fa-user fa-fw"></i><?php echo mswSafeDisplay($REPLIES->name); ?> <i class="fa fa-clock-o fa-fw"></i> <?php echo $MSDT->mswDateTimeDisplay($REPLIES->repStamp, $SETTINGS->dateformat) . ' @ ' . $MSDT->mswDateTimeDisplay($REPLIES->repStamp, $SETTINGS->timeformat); ?>
		      </div>
        </div>
        <?php
        }
        }
        ?>
        <button class="btn btn-success" type="button" onclick="window.print()" title="<?php echo mswSafeDisplay($msg_script13); ?>"><i class="fa fa-print fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo mswSafeDisplay($msg_script13); ?></span></button>
        <?php
        $BF = mswGetTableData('mailassoc','mailID',(int) $_GET['msg'],'AND `staffID` = \'' . $MSTEAM->id . '\'');
        if (isset($BF->folder)) {
        $mailBoxUrl = ($BF->folder == 'inbox' ? 'index.php?p=mailbox' : 'index.php?p=mailbox&amp;f=' . $BF->folder);
        ?>
        <button class="btn btn-link" type="button" onclick="mswWindowLoc('<?php echo $mailBoxUrl; ?>')"><i class="fa fa-times fa-fw"></i> <?php echo mswCleanData($msg_levels11); ?></button>
        <?php
        }
        ?>
      </div>
    </div>
    </form>

  </div>