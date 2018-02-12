<?php if (!defined('PATH')) { exit; } ?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li class="active"><?php echo $this->TXT[0]; ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">

            <div class="form-group">
              <label><?php echo $this->TXT[2]; ?></label>
              <input type="text" class="form-control" name="email" tabindex="1" onkeypress="if(mswKeyCode(event)==13){mswProcess('login')}" value="" autofocus>
            </div>

            <div class="form-group" id="pw">
              <label><?php echo $this->TXT[3]; ?></label>
              <input type="password" class="form-control" name="pass" onkeypress="if(mswKeyCode(event)==13){mswProcess('login')}" tabindex="2" value="" autocomplete="off">
		        </div>

          </div>
          <div class="panel-footer">
            <button class="btn btn-primary" id="b1" type="button" onclick="mswProcess('login')"><i class="fa fa-check fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[4]; ?></span></button>
            <button class="btn btn-primary" id="b2" type="button" style="display:none" onclick="mswProcess('newpass')"><i class="fa fa-unlock-alt fa-fw"></i> <?php echo $this->TXT[6]; ?></button>
	          <button class="btn btn-link" id="b3" type="button" onclick="mswNewPass()"><i class="fa fa-key fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[5]; ?></span></button>
            <button class="btn btn-link" id="b4" type="button" style="display:none" onclick="mswNewPass()"><i class="fa fa-times fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[7]; ?></span></button>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>