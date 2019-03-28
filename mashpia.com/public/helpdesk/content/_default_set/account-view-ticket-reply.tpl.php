<?php if (!defined('PATH') || !isset($this->TICKET->id)) { exit; } ?>

  <div class="row margin_top_20" id="replyArea">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
      <ul class="nav nav-tabs">
        <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-quote-left fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->REPTXT[0]; ?></span></a></li>
        <?php
        if ($this->ENTRY_CUSTOM_FIELDS) {
        ?>
        <li><a href="#two" data-toggle="tab"><i class="fa fa-list fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->REPTXT[1]; ?></span></a></li>
        <?php
        }
        ?>
        <li><a href="#three" data-toggle="tab"><i class="fa fa-paperclip fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->REPTXT[2]; ?></span></a></li>
        <?php
        // Only person who opened ticket can close it..
        if ($this->TICKET_CLOSE_PERMS == 'yes') {
        ?>
        <li><a href="#four" data-toggle="tab"><i class="fa fa-cog fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->REPTXT[3]; ?></span></a></li>
        <?php
        }
        ?>
      </ul>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="tab-content">
            <div class="tab-pane active in" id="one">

              <div class="form-group">
                <?php
                // BBCODE
                if ($this->SETTINGS->enableBBCode == 'yes') {
                  include(PATH.'content/' . MS_TEMPLATE_SET . '/bb-code.tpl.php');
                }
                ?>
                <textarea name="comments" rows="15" cols="40" tabindex="50" id="comments" class="form-control"></textarea>
              </div>

            </div>
            <?php
	          // CUSTOM FIELDS
		        // html/custom-fields/*
	          if ($this->ENTRY_CUSTOM_FIELDS) {
            ?>
            <div class="tab-pane fade" id="two">
              <?php
              echo $this->ENTRY_CUSTOM_FIELDS;
              ?>
            </div>
            <?php
            }

            // ENTRY ATTACHMENTS
		        if ($this->SETTINGS->attachment == 'yes') {
            ?>
            <div class="tab-pane fade" id="three">

              <div id="dropzone" class="dropzone">
                <div class="droparea">
                  <?php echo $this->REPTXT[4]; ?>
                </div>
              </div>

            </div>
            <?php
            }

            // Only person who opened ticket can close it..
            if ($this->TICKET_CLOSE_PERMS == 'yes') {
            ?>
            <div class="tab-pane fade" id="four">

              <div class="form-group">
                <div class="checkbox">
                  <label><input type="checkbox" name="close" value="1"> <?php echo $this->TXT[14]; ?></label>
                </div>
              </div>

            </div>
            <?php
            }
            ?>
          </div>
        </div>
        <div class="panel-footer">
          <input type="hidden" name="ticketID" value="<?php echo $this->TICKET->id; ?>">
          <input type="hidden" name="ticketType" value="<?php echo ($this->TICKET->isDisputed == 'yes' ? 'dispute' : 'ticket'); ?>">
          <button class="btn btn-primary" type="submit" onclick="mswProcessMultiPart()"><i class="fa fa-check fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[7]; ?></span></button>
	      </div>
      </div>
    </div>
  </div>