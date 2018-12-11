<?php if (!defined('PATH')) { exit; }
// Pre-populate search box if query exists.
$searchTxt = '';
// Ticket search..
if (isset($_GET['qt']) && $_GET['qt']) {
  $searchTxt = mswSafeDisplay($_GET['qt']);
}
if (isset($_GET['qd']) && $_GET['qd']) {
  $searchTxt = mswSafeDisplay($_GET['qd']);
}
$pageParam = ($this->IS_DISPUTED == 'yes' ? 'disputes' : 'history');
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>"><?php echo $this->TXT[1]; ?></a></li>
      <li class="active"><?php echo ($this->IS_DISPUTED == 'yes' ? $this->TXT[9] : $this->TXT[0]); ?></li>
    </ol>

    <form method="get" action="index.php">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading text-right">

            <div class="btn-group">
              <button class="btn btn-primary btn-sm" type="button"><span class="hidden-xs"><?php echo ($this->IS_DISPUTED == 'yes' ? $this->TXT[10] : $this->TXT[9]); ?></span><span class="hidden-sm hidden-md hidden-lg"><i class="fa fa-sort fa-fw"></i></span></button>
              <button class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown">
               <span class="caret"></span>
              </button>
              <ul class="dropdown-menu">
              <?php
              foreach ($this->DD_ORDER AS $fk1 => $fv1) {
              ?>
              <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=<?php echo $pageParam; ?>&amp;order=<?php echo $fk1 . mswQueryParams(array('p','order','next')); ?>"><?php echo $fv1; ?></a></li>
              <?php
              }
              ?>
              </ul>
            </div>

            <div class="btn-group">
              <button class="btn btn-primary btn-sm"><span class="hidden-xs"><?php echo ($this->IS_DISPUTED == 'yes' ? $this->TXT[11] : $this->TXT[10]); ?></span><span class="hidden-sm hidden-md hidden-lg"><i class="fa fa-filter fa-fw"></i></span></button>
              <button class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown">
              <span class="caret"></span>
              </button>
              <ul class="dropdown-menu topbar-dropdowns">
              <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=<?php echo $pageParam.mswQueryParams(array('p','filter','next')); ?>"><?php echo ($this->IS_DISPUTED=='yes' ? $this->TXT[14] : $this->TXT[13]); ?></a></li>
              <?php
              foreach ($this->DD_FILTERS AS $fk2 => $fv2) {
              ?>
              <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=<?php echo $pageParam; ?>&amp;filter=<?php echo $fk2 . mswQueryParams(array('p','filter','next')); ?>"><?php echo $fv2; ?></a></li>
              <?php
              }
              ?>
              </ul>
            </div>

            <div class="btn-group">
              <button class="btn btn-primary btn-sm"><span class="hidden-xs"><?php echo ($this->IS_DISPUTED=='yes' ? $this->TXT[12] : $this->TXT[11]); ?></span><span class="hidden-sm hidden-md hidden-lg"><i class="fa fa-filter fa-fw"></i></span></button>
              <button class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown">
                <span class="caret"></span>
              </button>
              <ul class="dropdown-menu dropdown-menu-right">
              <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=<?php echo $pageParam.mswQueryParams(array('p','dept','next')); ?>"><?php echo ($this->IS_DISPUTED == 'yes' ? $this->TXT[13] : $this->TXT[12]); ?></a></li>
              <?php
              foreach ($this->DD_DEPT AS $fk3 => $fv3) {
              ?>
              <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=<?php echo $pageParam; ?>&amp;dept=<?php echo $fk3 . mswQueryParams(array('p','dept','next')); ?>"><?php echo $fv3; ?></a></li>
              <?php
               }
              ?>
             </ul>
            </div>

            <button class="btn btn-info btn-sm" type="button" onclick="mswToggleSearch()"><i class="fa fa-search fa-fw"></i></button>

          </div>
          <div class="panel-body">

            <div class="form-group searchbox" style="height:60px;display:none">
             <div class="form-group input-group">
              <input class="form-control" type="text" name="<?php echo ($this->IS_DISPUTED=='yes' ? 'qd' : 'qt'); ?>" value="<?php echo $searchTxt; ?>">
              <span class="input-group-addon"><a href="#" onclick="mswDoSearch('<?php echo $pageParam; ?>','<?php echo ($this->IS_DISPUTED=='yes' ? 'qd' : 'qt'); ?>')"><i class="fa fa-arrow-right fa-fw"></i></a></span>
             </div>
            </div>

            <div class="table-responsive">
              <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th><?php echo $this->TXT[7]; ?></th>
                  <th><?php echo $this->TXT[8]; ?></th>
                  <th><?php echo $this->TXT[5]; ?></th>
                  <th><?php echo $this->TXT[6]; ?></th>
                  <th><?php echo $this->TXT[($this->IS_DISPUTED == 'yes' ? 15 : 14)]; ?></th>
                </tr>
              </thead>
              <tbody>
              <?php
                // TICKETS
                // html/tickets/ticket-list-entry.htm
                // html/tickets/tickets-no-data.htm
                // html/tickets/tickets-last-reply-date.htm
                echo $this->TICKETS;
              ?>
              </tbody>
              </table>
            </div>
          </div>
        </div>

        <?php
        // PAGE NUMBERS
        if ($this->PAGES) {
        ?>
        <div class="pagination pagination-small pagination-right">
        <?php
        // control/classes/page.php
        echo $this->PAGES;
        ?>
        </div>
        <?php
        }
        ?>

      </div>
    </div>
    </form>

  </div>