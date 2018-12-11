<?php if (!defined('PATH') || !isset($SUPTICK->id)) { exit; }
checkIsValid($SUPTICK);
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
      <li class="active"><?php echo str_replace('{ticket}', mswTicketNumber($_GET['merged']), ($SUPTICK->isDisputed == 'yes' ? $msg_viewticket80 : $msg_viewticket));; ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading">
            <?php echo $msg_viewticket48; ?>
          </div>
          <div class="panel-body">

            <?php
            // Show message..
            echo $msadminlang3_1adminviewticket[20];
            ?>

          </div>
        </div>

      </div>
    </div>
    </form>

  </div>