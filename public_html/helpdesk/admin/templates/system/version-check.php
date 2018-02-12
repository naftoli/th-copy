<?php if (!defined('PATH')) { exit; }
define('JS_LOADER', 'version-check.php');
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <li class="active"><?php echo $msg_versioncheck; ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body vcheckarea">

            <div class="text-center">
              <img src="templates/images/doing-something.gif" alt="" title="">
              <span><?php echo $msg_versioncheck2; ?></span>
            </div>

          </div>
          <div class="panel-footer">
           <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels11); ?></span></button>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>