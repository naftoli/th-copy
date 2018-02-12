  <script>
  //<![CDATA[
  jQuery(document).ready(function(){
    setTimeout(function() {
      jQuery('div[class="graphLoader"]').remove();
      <?php
      if (!empty($buildGraph[0])) {
      ?>
      var line = [<?php echo implode(',',$buildGraph[0]); ?>];
      <?php
      $plot = '[line]';
      $clrs = "'" . $colors[0] . "'";
      }
      if (!empty($buildGraph[1])) {
      ?>
      var line2 = [<?php echo implode(',',$buildGraph[1]); ?>];
      <?php
      $plot = '[line2]';
      $clrs = "'" . $colors[1] . "'";
      }
      if (!empty($buildGraph[0]) && !empty($buildGraph[1])) {
      $plot = '[line,line2]';
      $clrs = "'" . $colors[0] . "','" . $colors[1] . "'";
      }
      ?>
      var plot = jQuery.jqplot('chart',<?php echo $plot; ?>, {
        seriesColors: [<?php echo $clrs; ?>],
        grid: {
          drawGridLines: true,
          gridLineColor: '#ddd',
          background: '#fdfdfd',
          borderColor: '#ddd',
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
  //]]>
  </script>