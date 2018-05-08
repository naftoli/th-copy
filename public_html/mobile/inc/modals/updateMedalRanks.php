
<div class="modal fade rank-medal-modal" role="dialog" id="rankMedalModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <!--<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>-->
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="/mobile/img_new/x-color-white-svg.svg"/></button>
                <h4 class="modal-title"><span id="award-type">Medal</span> Awarded!</h4>
            </div>
            <div class="modal-body">
                <div class="cong-box animate">
                    
                    <div class="cong-text animated flash infinite">
                        <? if (!$lang || $lang == 1) { ?>
                        Mazel Tov <?=$user->first?>!
                        <? } else if ($lang == 2) {?>
                        <span class="he-text">!מזל טוב <?=$user->first_he?></span>
                        <? } ?>
                    </div>

                    <div class="rbn" style="display: none;">
                        <!--<img src="https://mashpia.com/file_view.php?id=92041472">-->
                    </div>
                    <div class="rank">
                        <!--<img src="//mashpia.com/mobile/reg/medals/images/trophits/1.png" alt="">-->
                    </div>
                    <br/>
                    <p id="details"></p>
                    <div id="share">
                        <h2>Share with Friends!</h2>
                        <a href="whatsapp://send?text=Mazel Tov! <?=$user->first?> has earned a new medal!" data-href="" data-action="share/whatsapp/share" target="_blank">
                            <i class="fa fa-whatsapp" aria-hidden="true"></i>
                        </a>
                        <a href="mailto:?subject=Tzivos Hashem Nachas!&body=Mazel Tov! <?=$user->first?> has earned a new medal!" target="_blank">
                            <i class="fa fa-envelope" aria-hidden="true"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?text=Mazel Tov! <?=$user->first?> has earned a new medal!" target="_blank">
                            <i class="fa fa-twitter" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<link href="/mobile/css/lib/animate.css" rel="stylesheet"/>
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
<link href="inc/modals/css/updateMedalRanks.css?v=1.3.2" rel="stylesheet"/>
<script>
    var first_name  = "<?= addslashes( $user->first )?>";
    var last_name   = "<?= addslashes( $user->last )?>";
</script>
<script src="js/updateMedalRanks.js?v=2.0"></script>
