<?php if (!defined('PATH')) { exit; } ?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $this->TXT[2]; ?></a></li>
      <li><a href="index.php?p=disputes"><?php echo $this->TXT[1]; ?></a></li>
      <li class="active"><?php echo $this->TXT[0]; ?></li>
    </ol>

    <?php
    // Show system message..
    if ($this->SYSTEM_MESSAGE) {
    ?>
    <div class="alert alert-warning alert-dismissable">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      <b><i class="fa fa-check fa-fw"></i></b> <?php echo $this->SYSTEM_MESSAGE; ?>
    </div>
    <?php
    }

    // If waiting assignment, no actions are allowed..
    if ($this->TICKET->assignedto != 'waiting' && $this->USERS_IN_DISPUTE_COUNT > 1) {
    ?>
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10 text-right mobilemenu">
        <div class="btn-group">
          <button class="btn btn-primary btn-sm" type="button"><span class="hidden-xs"><?php echo $this->TXT[29]; ?></span><span class="hidden-sm hidden-md hidden-lg"><i class="fa fa-user fa-fw"></i></span></button>
          <button class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown">
            <span class="caret"></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-right center_dropdown">
          <?php
          // USERS IN DISPUTE
          foreach ($this->USERS_IN_DISPUTE AS $uiD) {
          ?>
          <li><a href="#"><?php echo $uiD; ?></a></li>
          <?php
          }
          ?>
          </ul>
        </div>
        <div class="btn-group">
          <button class="btn btn-primary btn-sm" type="button"><span class="hidden-xs"><?php echo $this->TXT[28]; ?></span><span class="hidden-sm hidden-md hidden-lg"><i class="fa fa-cog fa-fw"></i></span></button>
          <button class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown">
            <span class="caret"></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-right">
          <?php
          if ($this->TICKET->ticketStatus == 'open' && $this->REPLY_PERMISSIONS == 'yes') {
          ?>
          <li><a href="#" onclick="mswScrollToArea('replyArea','60','0');return false" title="<?php echo mswSafeDisplay($this->TXT[7]); ?>"><?php echo $this->TXT[7]; ?></a></li>
          <?php
          // Only original ticket creator can close ticket..
          if ($this->TICKET->visitorID == $this->USER_DATA->id) {
          ?>
          <li><a href="?d=<?php echo $_GET['d']; ?>&amp;cl=yes" title="<?php echo mswSafeDisplay($this->TXT[23]); ?>"><?php echo $this->TXT[23]; ?></a></li>
          <?php
          }
          }
          if ($this->TICKET->ticketStatus == 'close') {
          ?>
          <li><a class="open" href="?d=<?php echo $_GET['d']; ?>&amp;lk=yes" title="<?php echo mswSafeDisplay($this->TXT[11]); ?>"><?php echo $this->TXT[11]; ?></a></li>
          <?php
          }
          ?>
          </ul>
        </div>
      </div>
    </div>
    <?php
    }
    ?>

    <form method="post" action="index.php?ajax=tickreply" enctype="multipart/form-data" id="mswform">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading colorchangeheader left-align">
            <i class="fa fa-user fa-fw"></i> <?php echo mswSafeDisplay((isset($this->ORG_USER->name) ? $this->ORG_USER->name : $this->USER_DATA->name)); ?> <span class="mobilebreakpoint"><i class="fa fa-clock-o fa-fw"></i> <?php echo $this->TXT[5] . ' @ ' . $this->TXT[6]; ?></span>
          </div>
          <div class="panel-body margin_top_10" id="tk<?php echo $this->TICKET->id; ?>">
            <span class="ticketsubject"><i class="fa fa-commenting-o fa-fw"></i> <?php echo mswSafeDisplay($this->TICKET->subject); ?></span>
            <hr>
            <?php
            // Ticket comments..
            echo $this->COMMENTS;

            // CUSTOM FIELDS
            // html/ticket-custom-fields.htm
            // html/ticket-custom-fields-wrapper.htm
            $sublinks = array();
            if ($this->CUSTOM_FIELD_DATA) {
              $sublinks[] = '<a href="#" onclick="mswToggleTicketData(\'tk' . $this->TICKET->id . '\', \'field\');return false"><i class="fa fa-file-text-o fa-fw"></i></a> <span class="hidden-sm hidden-xs">' . $this->TXT[31] . '</span> (' . $this->CUSTOM_FIELD_DATA_COUNT . ')';
              echo $this->CUSTOM_FIELD_DATA;
            }

            // ATTACHMENTS
            // html/ticket-attachment.htm
            // html/ticket-attachment-wrapper.htm
            if ($this->ATTACHMENTS) {
              $sublinks[] = '<a href="#" onclick="mswToggleTicketData(\'tk' . $this->TICKET->id . '\', \'attach\');return false"><i class="fa fa-paperclip fa-fw"></i></a> <span class="hidden-sm hidden-xs">' . $this->TXT[30] . '</span> (' . $this->ATTACHMENTS_COUNT . ')';
              echo $this->ATTACHMENTS;
            }

            // Show links..
            if (!empty($sublinks)) {
            ?>
            <div class="text-right">
              <hr>
              <?php echo implode(SUBLINK_SEPARATOR, $sublinks); ?>
            </div>
            <?php
            }
            ?>
          </div>
          <div class="panel-footer">
           <span class="pull-right">
              <?php
              // IP address(es)..
              echo ($this->TICKET->ipAddresses ? mswCleanData($this->TICKET->ipAddresses) : '&nbsp;');
              ?>
            </span>
            <?php echo $this->TXT[4]; ?> <i class="fa fa-angle-right fa-fw hidden-xs"></i>
            <span class="mobilebreakpoint"><?php echo $this->TXT[8]; ?></span>
          </div>
        </div>

        <?php
        // TICKET REPLIES
        // html/ticket-reply.htm
        // html/ticket-reply-sublink.htm
        // html/ticket-reply-field-link.htm
        // html/ticket-reply-attachment-link.htm
        // html/ticket-message.htm
        // html/ticket-attachment.htm
        // html/ticket-attachment-wrapper.htm
        // html/ticket-signature.htm
        // html/ticket-custom-fields.htm
        // html/ticket-custom-fields-wrapper.htm
        if ($this->TICKET->assignedto != 'waiting') {
          echo $this->TICKET_REPLIES;
        }
        ?>

      </div>

    </div>

    <?php
    // REPLY AREA
    // Show reply area OR show message..
	  if ($this->TICKET->ticketStatus == 'open' && $this->REPLY_PERMISSIONS == 'yes' &&
	      $this->TICKET->assignedto != 'waiting' && $this->USERS_IN_DISPUTE_COUNT > 1) {

      include(PATH . 'content/' . MS_TEMPLATE_SET . '/account-view-ticket-reply.tpl.php');

    } else {

      // MESSAGES
      // Awaiting assignment..
	    if ($this->TICKET->assignedto == 'waiting') {
	      $this->TICKET->ticketStatus = 'waiting';
	    // No more users added to dispute..
	    } elseif ($this->USERS_IN_DISPUTE_COUNT<2) {
	      $this->TICKET->ticketStatus = 'no-other-users';
	    // No replies are allowed until admin has replied..(Admin settings)
	    } elseif (in_array($this->TICKET->replyStatus, array('admin','start')) && $this->SETTINGS->disputeAdminStop == 'yes') {
	      $this->TICKET->ticketStatus = 'awaiting-admin';
	    }
	    // Show message based on closed status..
	    switch ($this->TICKET->ticketStatus) {
	      // Just closed, can be re-opened..
	      case 'close':
          $msg = $this->TXT[9];
          break;
        // Closed and locked, cannot be re-opened..
        case 'closed':
          $msg = $this->TXT[10];
          break;
        // Waiting operator assignment..
        case 'waiting':
          $msg = $this->TXT[25];
          break;
        // No other users in dispute..
        case 'no-other-users':
          $msg = $this->TXT[26];
          break;
        // Awaiting admin..
        case 'awaiting-admin':
          $msg = $this->TXT[27];
          break;
        // Default..should never trigger, but prevents php error..
        default:
          $msg = '';
          break;
      }
	    if ($msg) {
      ?>
      <div class="row margin_top_20">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
          <div class="alert alert-danger"><i class="fa fa-warning fa-fw"></i> <?php echo $msg; ?></div>
        </div>
      </div>
      <?php
      }

    }
    ?>

    </form>

  </div>