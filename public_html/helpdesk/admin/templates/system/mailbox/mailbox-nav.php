<?php if (!defined('PARENT')) { exit; }
$moveToFolders           = array();
$moveToFolders['inbox']  = $msg_mailbox;
$moveToFolders['outbox'] = $msg_mailbox2;
$moveToFolders['bin']    = $msg_mailbox3;
if (!isset($keys)) {
  $keys = '';
}
?>
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
<ul class="nav nav-tabs">
  <?php
  if (isset($_GET['msg'])) {
  ?>
  <li class="active"><a href="?p=mailbox&amp;msg=<?php echo (int) $_GET['msg']; ?>"><i class="fa fa-search fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_mailbox7; ?></span></a></li>
  <?php
  }
  if ($keys) {
  ?>
  <li class="active"><a href="?p=mailbox&amp;keys=<?php echo urlencode(mswSafeDisplay($keys)); ?>"><i class="fa fa-search fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_mailbox32; ?></span></a></li>
  <?php
  }
  if (isset($_GET['new'])) {
  ?>
  <li class="active"><a href="?p=mailbox&amp;new=1"><i class="fa fa-pencil fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_mailbox4; ?></span></a></li>
  <?php
  }
  if (isset($_GET['folders'])) {
  ?>
  <li class="active"><a href="?p=mailbox&amp;folders=1"><i class="fa fa-folder fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_mailbox6; ?></span></a></li>
  <?php
  }
  if (isset($_GET['f']) && (int) $_GET['f'] > 0) {
  ?>
  <li class="active"><a href="?p=mailbox&amp;folders=1"><i class="fa fa-folder-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $boxName; ?></span></a></li>
  <?php
  }
  ?>
  <li<?php echo (!isset($_GET['f']) && !isset($_GET['msg']) && !isset($_GET['folders']) && !isset($_GET['new']) && $keys=='' ? ' class="active"' : ''); ?>><a href="?p=mailbox"><i class="fa fa-inbox fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_mailbox; ?></span></a></li>
  <li<?php echo (isset($_GET['f']) && $_GET['f']=='outbox' ? ' class="active"' : ''); ?>><a href="?p=mailbox&amp;f=outbox"><i class="fa fa-sign-in fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_mailbox2; ?></span></a></li>
  <li<?php echo (isset($_GET['f']) && $_GET['f']=='bin' ? ' class="active"' : ''); ?>><a href="?p=mailbox&amp;f=bin"><i class="fa fa-trash fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_mailbox3; ?></span></a></li>
  <?php
  // Are additional folders allowed?
  if ($MSTEAM->mailFolders > 0) {
  ?>
  <li class="dropdown">
   <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-folder-open fa-fw"></i> <?php echo $msg_mailbox5; ?><span class="caret"></span></a>
   <ul class="dropdown-menu">
    <li><a href="?p=mailbox&amp;folders=1"><?php echo $msg_mailbox6; ?></a></li>
    <li role="separator" class="divider"></li>
    <?php
    $qF = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`,`folder` FROM `" . DB_PREFIX . "mailfolders`
          WHERE `staffID` = '{$MSTEAM->id}'
          ORDER BY `folder`
          ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
    if (mysqli_num_rows($qF) > 0) {
      $moveToFolders['-'] = '';
    }
    while ($FOLDERS = mysqli_fetch_object($qF)) {
    $moveToFolders[$FOLDERS->id] = mswCleanData($FOLDERS->folder)
    ?>
    <li><a href="?p=mailbox&amp;f=<?php echo $FOLDERS->id; ?>"><?php echo mswSafeDisplay($FOLDERS->folder); ?></a></li>
    <?php
    }
    ?>
   </ul>
  </li>
  <?php
  }
  if (!isset($_GET['new'])) {
  ?>
  <li><a href="?p=mailbox&amp;new=1"><i class="fa fa-pencil fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_mailbox4; ?></span></a></li>
  <?php
  }
  ?>
</ul>
</div>