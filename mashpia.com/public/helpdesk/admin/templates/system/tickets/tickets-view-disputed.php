<?php if (!defined('PATH') || !isset($_GET['id'])) { exit; }
define('TICKET_LOADER',1);
define('TICKET_TYPE','dispute');
$tickID = (int) $_GET['id'];
if ($tickID == 0) { exit; }
$tickDept = mswGetTableData('departments','id', $SUPTICK->department);
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
      <li class="active"><?php echo $title; ?></li>
    </ol>

    <?php
    if (isset($actionMsg)) {
    ?>
    <div class="alert alert-warning alert-dismissable">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      <strong><i class="fa fa-check fa-fw"></i></strong> <?php echo $actionMsg; ?>
    </div>
    <?php
    }
    ?>

    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10 text-right mobilemenu">
      <?php
      // Is notepad available..
      if ($MSTEAM->notePadEnable == 'yes' || $MSTEAM->id == '1') {
      ?>
      <button class="btn btn-success btn-sm" type="button" onclick="iBox.showURL('?p=view-dispute&amp;id=<?php echo $tickID; ?>&amp;editNotes=yes','',{width:<?php echo IBOX_NOTES_WIDTH; ?>,height:<?php echo IBOX_NOTES_HEIGHT; ?>});return false"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_viewticket54; ?></span></button>
      <?php
      }
      // Assigned..
      if ($tickDept->manual_assign == 'yes' && $SUPTICK->assignedto != 'waiting') {
        $atlinks = array();
        foreach (explode(',',$SUPTICK->assignedto) AS $assToK) {
          $thisAssignPerson = mswGetTableData('users','id', (int) $assToK);
          if (isset($thisAssignPerson->name)) {
            $atlinks[] = array(
              'link' => (in_array('teamman',$userAccess) || $MSTEAM->id == '1' ? '?p=team&amp;edit=' . $assToK : '#'),
              'name' => $thisAssignPerson->name
            );
          }
        }
        if (empty($atlinks)) {
          $atlinks[] = array(
            'link' => '#',
            'name' => $msadminlang3_1adminviewticket[23]
          );
        }
        if (USER_EDIT_T_PRIV == 'yes') {
          $atlinks[] = array(
            'link' => 'sep',
            'name' => ''
          );
          $atlinks[] = array(
            'link' => '?p=edit-ticket&amp;id=' . $tickID,
            'name' => $msadminlang3_1adminviewticket[21]
          );
        }
        echo $MSBOOTSTRAP->button($msadminlang3_1adminviewticket[22], $atlinks, ' dropdown-menu-right', 'yes', 'admin', 'users');
      }
      // Users in dispute..
      include(REL_PATH . 'control/classes/class.tickets.php');
      $MST            = new tickets();
      $usersInDispute = $MST->disputeUserNames($SUPTICK, $SUPTICK->name);
      $dlinks         = array();
      foreach ($usersInDispute AS $uiDSi => $uiDS) {
        $dlinks[] = array(
          'link' => (in_array('accounts',$userAccess) || $MSTEAM->id == '1' ? '?p=accounts&amp;edit=' . ($uiDSi > 0 ? $uiDSi : $SUPTICK->visitorID) : '#'),
          'name' => $uiDS
        );
      }
      $links = array(
        array('link' => 'sep'),
        array('link' => '?p=view-dispute&amp;disputeUsers=' . $tickID, 'name' => $msg_disputes8)
      );
      echo $MSBOOTSTRAP->button($msadminlang3_1adminviewticket[8], array_merge($dlinks,$links), ' dropdown-menu-right', 'yes', 'admin', 'user');
      // Actions..
      if ($SUPTICK->ticketStatus != 'open') {
        $links = array(
          array('link' => '?p=view-dispute&amp;id=' . $tickID . '&amp;act=reopen', 'name' => $msadminlang3_1adminviewticket[6])
        );
      } else {
        $links = array(
          array('link' => '#', 'name' => $msg_viewticket75, 'extra' => 'onclick="mswScrollToArea(\'replyArea\', \'60\', \'0\');return false"')
        );
        if (USER_EDIT_T_PRIV == 'yes') {
          $links[] = array(
            'link' => '?p=edit-ticket&amp;id=' . $tickID,
            'name' => $msadminlang3_1adminviewticket[2]
          );
        }
        $links[] = array(
          'link' => 'sep'
        );
        $links[] = array(
          'link' => '?p=view-dispute&amp;id=' . $tickID . '&amp;act=close',
          'name' => $msadminlang3_1adminviewticket[4]
        );
        $links[] = array(
          'link' => '?p=view-dispute&amp;id=' . $tickID . '&amp;act=lock',
          'name' => $msadminlang3_1adminviewticket[3]
        );
        $links[] = array(
          'link' => 'sep'
        );
        $links[] = array(
          'link' => '?p=view-dispute&amp;id=' . $tickID . '&amp;act=ticket',
          'name' => $msg_disputes4,
          'extra' => 'onclick="mswConfirmButtonLink(\'' . mswSafeDisplay($msg_script_action) . '\', this.href);return false;"'
        );
      }
      echo $MSBOOTSTRAP->button($msg_script43, $links, ' dropdown-menu-right', 'no', 'admin', 'cog');
      ?>
      </div>
    </div>

    <form method="post" action="index.php?ajax=tickreply" enctype="multipart/form-data" id="mswform">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading colorchangeheader left-align">
            <i class="fa fa-user fa-fw"></i> <?php echo mswSafeDisplay($SUPTICK->name); ?> <span class="mobilebreakpoint"><i class="fa fa-clock-o fa-fw"></i> <?php echo $MSDT->mswDateTimeDisplay($SUPTICK->ts,$SETTINGS->dateformat) . ' @ ' . $MSDT->mswDateTimeDisplay($SUPTICK->ts,$SETTINGS->timeformat); ?></span>
          </div>
          <div class="panel-body margin_top_10" id="tk<?php echo $SUPTICK->id; ?>">
            <span class="ticketsubject"><i class="fa fa-commenting-o fa-fw"></i> <?php echo mswSafeDisplay($SUPTICK->subject); ?></span>
            <hr>
            <?php
            echo $MSPARSER->mswTxtParsingEngine($SUPTICK->comments);
            $dRepID   = 0;
            $toggleID = 'tk' . $SUPTICK->id;
            $label    = 'panel panel-default';
            include(PATH . 'templates/system/tickets/tickets-view-data-area.php');
            ?>
          </div>
          <div class="panel-footer">
           <span class="pull-right">
             <?php echo loadIPAddresses($SUPTICK->ipAddresses); ?>
           </span>
           <?php echo $MSYS->levels($SUPTICK->priority); ?> <i class="fa fa-angle-right fa-fw hidden-xs"></i>
           <span class="mobilebreakpoint"><?php echo $MSYS->department($SUPTICK->department,$msg_script30); ?></span>
          </div>
        </div>

        <?php
        include(PATH . 'templates/system/tickets/tickets-view-replies.php');
        ?>

      </div>
    </div>

    <?php
    include(PATH . 'templates/system/tickets/tickets-view-reply-area.php');
    ?>

    <input type="hidden" name="isDisputed" value="yes">
    <?php
    if ($MSTEAM->id != '1') {
    ?>
    <input type="hidden" name="history" value="yes">
    <?php
    }
    ?>
    </form>

  </div>