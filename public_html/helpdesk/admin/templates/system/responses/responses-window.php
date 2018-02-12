<?php if (!defined('PATH') || !isset($_GET['view'])) { exit; }
$_GET['view']  = (int) $_GET['view'];
$SR            = mswGetTableData('responses','id',$_GET['view']);
if (!isset($SR->id)) {
  die('Invalid ID');
}
?>
  <div class="fluid-container">

    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="fa fa-file-text-o fa-fw"></i> <?php echo mswSafeDisplay($SR->title); ?>
      </div>
      <div class="panel-body">
        <?php
        echo $MSPARSER->mswTxtParsingEngine($SR->answer);
        ?>
        <hr>
        <?php echo mswSrCat($SR->departments); ?>
      </div>
    </div>

    <div class="text-center mswitalics">
        <i class="fa fa-clock-o fa-fw"></i> <?php echo $msg_response18 . ': ' . $MSDT->mswDateTimeDisplay($SR->ts, $SETTINGS->dateformat) . ' / ' . $MSDT->mswDateTimeDisplay($SR->ts, $SETTINGS->timeformat); ?>
    </div>

  </div>