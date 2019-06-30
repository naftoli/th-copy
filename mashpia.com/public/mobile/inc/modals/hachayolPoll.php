<div class="modal fade" role="dialog" id="hachayolPoll">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <!--<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>-->
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="img_new/x-color-white-svg.svg"/></button>
                <h4 class="modal-title">Hachayol Poll</h4>
            </div>
            <div class="modal-body">
              <h4>Why do you open Hachayol?</h4>
              <p>
                Here's your chance to tell the Hachayol editors your favorite part(s) of the magazine. For each page described below, 
                choose the answer you feel is best. You can also add the reasons for your answers in the "notes" section below.
              </p>
              <?php
              $parts = ['Parshifier', 'Veholachto Bidrochov', 'Roots', 'Comics', 'Shine Back Page & Game', 'Dubbie\'s Diary', 'Yom Tov Poems'];
              foreach ( $parts as $part ) {
                echo "<h5>" . $part . "</h5>";
                $name = str_replace("'", "", $part);
                echo "<div><input type='radio' name='" . strtolower($name) . "' value='1' /> I love it<br />";
                echo "<input type='radio' name='" . strtolower($name) . "' value='2' /> Not my Favorite<br />";
                echo "<input type='radio' name='" . strtolower($name) . "' value='3' /> I never read it<br /></div>";
              }
              ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal" style="background-color: #5e1c77;border-color:#834999;">Cancel</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->