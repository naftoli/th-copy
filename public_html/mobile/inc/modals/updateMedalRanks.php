<div class="modal fade rank-medal-modal" role="dialog" id="rankMedalModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <!--<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>-->
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="/mobile/img_new/x-color-white-svg.svg"/></button>
                <h4 class="modal-title"><span id="award-type">Medal</span> Awarded!</h4>
            </div>
            <div class="modal-body">
                <div class="cong-box">
                    <? if (!$lang || $lang == 1) { ?>
                    <div class="cong-text">
                        <span id="l1">M</span>
                        <span id="l2">a</span>
                        <span id="l3">z</span>
                        <span id="l4">a</span>
                        <span id="l5">l</span>
                        <span id="l4">&nbsp;</span>
                        <span id="l6">t</span>
                        <span id="l7">o</span>
                        <span id="l8">v</span>
                        <span id="l9">!</span>
                    </div>
                    <? } else if ($lang == 2) {?>
                    <div class="cong-text he">
                        <span id="l8">!</span>
                        <span id="l1">מ</span>
                        <span id="l2">ז</span>
                        <span id="l3">ל</span>
                        <span id="l4">&nbsp;</span>
                        <span id="l5">ט</span>
                        <span id="l6">ו</span>
                        <span id="l7">ב</span>
                    </div>
                    <? } ?>
                    <div class="rbn" style="display: none;">
                        <!--<img src="https://mashpia.com/file_view.php?id=92041472">-->
                    </div>
                    <div class="rank">
                        <!--<img src="//mashpia.com/mobile/reg/medals/images/trophits/1.png" alt="">-->
                    </div>
                    <br/>
                    <p id="details"></p>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?if(!isset($demo)) {?>
    <link href="inc/modals/css/updateMedalRanks.css?v=1.3.2" rel="stylesheet"/>
    <script src="js/updateMedalRanks.js"></script>
<?} ?>
