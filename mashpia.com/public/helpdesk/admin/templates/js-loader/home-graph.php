  <script>
  //<![CDATA[
  function mswShowHideDateRange() {
    if (jQuery('div[class="panel-body hdates"]').css('display') == 'none') {
      jQuery('div[class="panel-body hdates"]').slideDown();
      jQuery('div[class="panel-body hgraph"]').hide();
    } else {
      jQuery('div[class="panel-body hdates"]').hide();
      jQuery('div[class="panel-body hgraph"]').show();
    }
  }
  function mswChangeDateRange() {
    var fm = jQuery('input[name="from"]').val();
	  var to = jQuery('input[name="to"]').val();
	  var dd = jQuery('input[name="def"]').val();
	  if (dd=='') {
	    if (fm=='') {
	      jQuery('input[name="from"]').focus();
	      return false;
	    }
	    if (to=='') {
	      jQuery('input[name="to"]').focus();
	      return false;
	    }
    }
	  mswWindowLoc('index.php?f=' + fm + '&t=' + to + '&dd=' + dd);
  }
  <?php
  if (defined('HOME_GRAPH_LOAD')) {
  ?>
  jQuery(document).ready(function(){
    setTimeout(function() {
      jQuery('div[class="graphLoader"]').remove();
      var line  = [];
      var line2 = [];
      <?php
      if ($g_tick) {
      ?>
      line = [<?php echo $g_tick; ?>];
      <?php
      }
      if ($SETTINGS->disputes == 'yes' && $g_disp) {
      ?>
      line2 = [<?php echo $g_disp; ?>];
      <?php
      }
      ?>
      var plot = jQuery.jqplot('chart',[line,line2], {
        seriesColors: ['<?php echo $g_config['color1']; ?>','<?php echo $g_config['color2']; ?>'],
        grid: {
          drawGridLines: true,
          gridLineColor: '<?php echo $g_config['gline']; ?>',
          background: '<?php echo $g_config['bg']; ?>',
          borderColor: '<?php echo $g_config['border']; ?>',
          borderWidth: '0.5',
          shadow: false
        },
        axes: {
          xaxis: {
            renderer: jQuery.jqplot.DateAxisRenderer,
            labelRenderer: jQuery.jqplot.CanvasAxisLabelRenderer,
            tickRenderer: jQuery.jqplot.CanvasAxisTickRenderer,
            tickOptions: {
              angle: 90
            }
          },
          yaxis: {
            min: 0,
            labelRenderer: jQuery.jqplot.CanvasAxisLabelRenderer,
            tickOptions: {
              formatString: '%d'
            },
            tickInterval: 1
          }
        }
      })
    }, 3000);
  });
  <?php
  }
  ?>
  //]]>
  </script>