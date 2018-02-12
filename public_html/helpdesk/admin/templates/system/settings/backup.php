<?php if (!defined('PATH')) { exit; }
$totalBackup = 0;
$msSPScheme  = mswDBSchemaArray();
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('settings', $userAccess) || $MSTEAM->id == '1') {
      ?>
      <li><a href="index.php?p=settings"><?php echo $msg_adheader2; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo $msg_adheader30.' ('.str_replace('{count}',count($msSPScheme),$msg_backup16).')'; ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th><?php echo $msg_backup3; ?></th>
                  <th><?php echo $msg_backup4; ?></th>
                  <th><?php echo $msg_backup5; ?></th>
                  <th><?php echo $msg_backup7; ?></th>
                  <th><?php echo $msg_backup6; ?></th>
                  <th><?php echo $msg_backup8; ?></th>
               </tr>
              </thead>
              <tbody>
               <?php
               $q = mysqli_query($GLOBALS["___mysqli_ston"], "SHOW TABLE STATUS FROM " . DB_NAME);
               while ($DB = mysqli_fetch_object($q)) {
               $SCHEMA = (array)$DB;
               if (in_array($SCHEMA['Name'],$msSPScheme)) {
               $size   = ($SCHEMA['Rows']>0 ? $SCHEMA['Data_length']+$SCHEMA['Index_length'] : '0');
               $ctTS   = strtotime($SCHEMA['Create_time']);
               $utTS   = strtotime($SCHEMA['Update_time']);
               ?>
               <tr>
                 <td><?php echo $SCHEMA['Name']; ?></td>
                 <td><?php echo $SCHEMA['Rows']; ?></td>
                 <td><?php echo ($SCHEMA['Rows']>0 ? mswFileSizeConversion($size) : '0'); ?></td>
                 <td><?php echo date($SETTINGS->dateformat,$utTS); ?></td>
                 <td><?php echo $SCHEMA['Collation']; ?></td>
                 <td><?php echo $SCHEMA['Engine']; ?></td>
               </tr>
               <?php
               $totalBackup = ($totalBackup+$size);
               }
               }
               ?>
               </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>

    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">

            <div class="form-group">
              <div class="checkbox">
                <label><input type="checkbox" name="download" value="yes" checked="checked"> <?php echo $msg_backup11; ?> <b>(<?php echo $msg_settings102 . ' ' . mswFileSizeConversion($totalBackup); ?>)</b></label>
              </div>
            </div>

            <div class="form-group">
              <div class="checkbox">
                <label><input type="checkbox" name="compress" value="yes"> <?php echo $msg_backup13; ?> <b>(GZ)</b></label>
              </div>
            </div>

            <div class="form-group">
              <label><?php echo $msg_backup12; ?>:</label>
              <input type="text" tabindex="<?php echo ++$tabIndex; ?>" name="emails" class="form-control" value="<?php echo mswSafeDisplay($SETTINGS->backupEmails); ?>">
            </div>

          </div>
          <div class="panel-footer">
            <button class="btn btn-primary" type="button" onclick="mswProcess('backup')"><i class="fa fa-save fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_backup14); ?></span></button>
          </div>
        </div>
      </div>
    </div>
    </form>

  </div>