<div class="modal fade" role="dialog" id="hachayolPoll">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <!--<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>-->
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="img_new/x-color-white-svg.svg"/></button>
                <h4 class="modal-title">Hachayol Poll</h4>
            </div>
            <div class="modal-body">
              <h1>Why do you open Hachayol?</h1>
              <p>
                Here's your chance to tell the Hachayol editors your favorite part(s) of the magazine. For each page described below, 
                choose the answer you feel is best. You can also add the reasons for your answers in the "notes" section below.
              </p>
              <?php
              $parts = ['Parshifier', 'Veholachto Bidrochov', 'Roots', 'Comics', 'Shine Back Page & Game', 'Dubbie\'s Diary', 'Yom Tov Poems'];
              foreach ( $parts as $k => $part ) {
                echo "<h4>" . $part . "</h4>";
                $name = str_replace("'", "", $part);
                echo "<div id='" . ($k + 1) . "'><input type='radio' class='poll' name='" . strtolower($name) . "' value='1' /> I love it<br />";
                echo "<input type='radio' class='poll' name='" . strtolower($name) . "' value='2' /> Not my favorite<br />";
                echo "<input type='radio' class='poll' name='" . strtolower($name) . "' value='3' /> I never read it<br /></div><br />";
              }
              ?>
              <h4>Notes</h4>
              <textarea name="notes" id="poll-notes"></textarea>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger" id="submit-poll" style="background-color: #5e1c77;border-color:#834999;">Submit</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal" style="background-color: #5e1c77;border-color:#834999;">Cancel</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script>
  $("#submit-poll").click( function() {
    var user_id = <?= $user_id ?>;
    var poll_result = {};
    var parts = ['Parshifier', 'Veholachto Bidrochov', 'Roots', 'Comics', 'Shine Back Page & Game', 'Dubbie\'s Diary', 'Yom Tov Poems'];
    for (var i in [1,2,3,4,5,6,7]) {
      var val = $("#" + i + " .poll:checked").val();
      if ( val ) {
        poll_result[ parts[i-1] ] = val;
      }
    }
    poll_result.notes = $("#poll-notes").val();
    console.log( poll_result );
    $.post("registration/submitPoll.php", { user: user_id, poll_info: JSON.stringify(poll_result) }, function( res ) {
      alert(res);
      $("#hachayolPoll").modal('hide');
    });
  });
</script>