<?php if (!defined('PATH') || !isset($_GET['view'])) { exit; }
$_GET['view']  = (int) $_GET['view'];
$PG            = mswGetTableData('pages','id',$_GET['view']);
if (!isset($PG->id)) {
  die('Invalid ID');
}
?>
  <div class="fluid-container">

    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="fa fa-file-text-o fa-fw"></i> <?php echo mswSafeDisplay($PG->title); ?>
      </div>
      <div class="panel-body">
        <?php
        echo $MSPARSER->mswTxtParsingEngine($PG->information);
        ?>
        <hr>
        <?php
        switch($PG->secure) {
          case 'yes':
            echo str_replace(array('{yesno}','{acc}'),array($msg_script4,($PG->accounts == 'all' ? $msadminlang3_1cspages[12] : count(explode(',',$PG->accounts)))),$msadminlang3_1cspages[11]);
            break;
          default:
            echo str_replace(array('{yesno}','{acc}'),array($msg_script5,'N/A'),$msadminlang3_1cspages[11]);
            break;
          }
        ?>
      </div>
    </div>

    <div class="text-center mswitalics">
        <i class="fa fa-clock-o fa-fw"></i> <?php echo $msg_response18 . ': ' . $MSDT->mswDateTimeDisplay($PG->ts, $SETTINGS->dateformat) . ' / ' . $MSDT->mswDateTimeDisplay($PG->ts, $SETTINGS->timeformat); ?>
    </div>

  </div>