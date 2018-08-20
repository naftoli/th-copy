<style>
/*    #action-links{border: 1px solid;}
    div#action-links a {margin: 0px;border: none;width: 24.2%;display: inline-block;}
    #action-links div.button {width: 100%;box-shadow: none;height: 75px;border-radius: 0px;margin: 0px;}
    #action-links div.button:hover{box-shadow: none; transform: none; font-size: 1.09em;}
    #action-links div.button span.img-box{width: 100%; display: inline-block;}
    #action-links span.link-text{margin-top: 0px;}*/
</style>
<div id="action-links">
    <a href="gifts_and_prizes/<?=$debug ? "?debug=true": "";?>">
        <div class="button">
            <span class="img-box"><img src="/images/icon_auction.png" height="32" alt="tickets"/></span>
            <span class="link-text">Yearly Gift and Raffle Prizes</span>
        </div>
    </a>
    <a href="ranks_and_medals/<?=$debug ? "?debug=true": "";?>">
        <div class="button">
            <span class="img-box"><img src="/mobile/reg/images/medals/Avos.gif" height="32" alt="tickets"/></span>
            <span class="link-text">Medals and Rank Cards</span>
        </div>
    </a>
    <a href="hachayols/<?=$debug ? "?debug=true": "";?>">
        <div class="button">
            <span class="img-box"><img src="/images/cth_logo.png" height="32" alt="tickets"/></span>
            <span class="link-text">Hachayols</span>
        </div>
    </a>
    <a href="shipments/<?=$debug ? "?debug=true": "";?>">
        <div class="button">
            <span class="img-box"><img src="/images/box.png" height="32" alt="tickets"/></span>
            <span class="link-text">Shipments</span>
        </div>
    </a>
</div>