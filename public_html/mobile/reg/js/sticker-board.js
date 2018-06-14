/**
 * The following code is line for line from leaderboard/main.js - 6/13/2018
 */
// make sure that we do not get a no-refrance error.
var authenticate;
// get the ID from get GET params
var id = findGetParameter("id");
// make sure we can authenticate the user
if ( !authenticate ) {
    window.location = "/mobile";
} else {
    authenticate( id ); // get the users ID and authenticate them
    setNavLinks ( id ); // set the user ID in the navigation links
    setUserPhoto( id ); // set the users profile picture
}
// setup all the links on the page
function setNavLinks( id ) {
    // links on the bottom of the page (nav-bar)
    if( localStorage.getItem("login") == "user" ){ // support the Kiosk Mode
        $( "#mainLink" ).attr('href', '/mobile/reg/medals/?id=' + id);
    };
    $("#missionsLink").attr('href', '/mobile/missionsNew.html?id=' + id);
    $("#rankLink").attr('href', '/mobile/reg/rank.html?id=' + id);
    $("#storeLink").attr('href', '/mobile/store/index.html?id=' + id);
    // set the links on the top of the page
    $("#rankBoardLink").attr('href', '/mobile/reg/rank.html?id=' + id);
    $("#medalBoardLink").attr('href', '/mobile/reg/medals4.html?id=' + id);
    $("#leaderBoardLink").attr('href', '/mobile/leaderboard/?id=' + id);
    // setup the ID in the bug reporting tool
    $(".bug-report img").data("user_id", id);
}
// set the user's photo on the top of the page
function setUserPhoto( id ){
    $.post('/mobile/reg/ajax/getPhoto.php', { user_id : id }, function( response ) {
        response = $.parseJSON( response );
        var html = '<a href="/mobile/reg/medals/index.html?id=' + id +'">';
        html += '<img id="userImg" src="'; // open the image tag
        if (response.mobile_pic) // up to date pictures
            html += '//mashpia.com/mobile/reg/' + response.mobile_pic;
        else if (response.thumb) // if we only have a thumbnail
            html += '/thumbs/' + response.thumb;
        else if (response.photo) // picture is really old
            html += '/file_view.php?id=' + response.photo;
        html += '"></a>';
        $(".personalImg").append( html );
    });
}
// function to get GET paramaters
function findGetParameter(parameterName) {
    var result = null,
        tmp = [];
    var items = location.search.substr(1).split("&");
    for (var index = 0; index < items.length; index++) {
        tmp = items[index].split("=");
        if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
    }
    return result;
}

/**
 * sticker-board.js
 * 
 * File to handle dynamic content on sticker-board.html / medals3.html
 */
var sticker_board = function() {
    var cache = []; // this is a cache for what to render for performace reasons.
    // Load the page from the server
    function loadPage() {
        var user_id = findGetParameter('id');
        $.post( 'ajax/getMedalInfo.php?v=2', { user_id: user_id }, renderPage );
    }
    // render the response
    function renderPage( campaigns ) {
        var selected_subject = findGetParameter('subject');
        var sliderHtml = '';
        var boardHtml = '';
        campaigns.forEach( function( campaign, index ){
            if( campaign.subject_id == selected_subject ) selected_subject = index;
            sliderHtml += renderMedalSliderItem( campaign );
            boardHtml += renderStickerRows( index, campaign, cache ); // cache defined right under sticker_board;
        });
        $('#medal-slider').html(sliderHtml);
        $('#medal-stickers').html(boardHtml);
        setupSlider( selected_subject );
    }
    // template for top slider
    function renderMedalSliderItem( campaign ){
        var percent_compleate = ( campaign.total / campaign.subject_total ) * 100;
        return  '<div class="medal-slider-item">' +
                    '<img src="' + campaign.photo + '">' + 
                    '<div class="medal-subject"><span>' + campaign.subject_name + '</span></div>' + 
                    '<div class="medal-status progress">' + 
                        '<div class="progress-bar" role="progressbar" style="width: ' + percent_compleate + '%;"></div>' +
                        '<span>' + campaign.total + ' / ' + campaign.subject_total + ' Missions</span>' +
                    '</div>' +
                '</div>';
    }

    function renderStickerRows( index, campaign, cache ){
        var total = parseInt( campaign.total );
        var html = renderCampaignInfo( campaign ); // variable to store sticker rows, will be added to cache.
        // render the row on the sticker board for each of the medals in the campaign
        campaign.medal_info.forEach( function( medal_info ){
            var last_total = medal_info.running_total - medal_info.missions_required; // the total reqired to earn the previous medal. Used in math later
            var medal_classes = ''; var compleation_status = 0; // special classes and the % compleate for this medal ( default 100% ).
            var status_text = medal_info.medal_name + ' Medal Earned!'; // medal is earned by default
            var medal_color = medal_info.medal_name.toLowerCase();
            //******************** Calculate the % status of the medal ********************/
            // earned
            if ( total >= medal_info.running_total ) {
                medal_classes = 'earned animated tada';
                compleation_status = 100;
            // current
            } else if ( total > last_total && medal_info.running_total > total ) {
                compleation_status = ( ( total - last_total ) / ( medal_info.running_total - last_total ) ) * 100;
                status_text = ( medal_info.missions_required - ( total - last_total ) ) + ' to ' + medal_info.medal_name;
            // future
            } else {
                compleation_status = ( 1 - ( ( medal_info.running_total - total ) / medal_info.running_total ) ) * 100;
                status_text = ( medal_info.running_total - total ) + ' to ' + medal_info.medal_name;
            }
            
            // render the medal icon
            html += '<hr><div class="row">' +
                '<div class="col-4 col-sm-3 medal-level">' +
                    '<span class="levelText">Level ' + medal_info.medal_ord + '</span>' + 
                    '<img class="medal-img ' + medal_classes + '" src="' + medal_info.photo + '" />' + 
                    '<div class="medal-status progress">' + 
                        '<div class="progress-bar ' + medal_color + '" role="progressbar" style="width: ' + compleation_status + '%;"></div>' + 
                        '<span>' + status_text + '</span>' +
                    '</div>' + 
                '</div>' +
                '<div class="col-8 col-sm-9 medal-level-stickers">';
            // render all the stickers
            for( var i = 1; i <= medal_info.missions_required; i++ ) {
                var slot_number = ( medal_info.running_total - medal_info.missions_required ) + i;
                var earned = slot_number <= total;
                var slot_sticker = campaign.sticker_name + ( earned ? '' : '_bw' )
                html += '<div class="sticker ' + ( earned ? 'earned' : '') + '">' +
                    ( slot_number ) + 
                    '<img src="//mashpia.com/mobile/img_new/stickers/' + slot_sticker + '.gif">'
                +'</div>';
            }
            // close the tags;
            html += '</div></div>';
        });
        cache[ index ] = html; // add the item to the cache;
        // return a wrapper container with an ID we can work with ;-)
        return '<div class="sticker-board" id="sticker-board-' + index + '"></div>';
    }

    function renderCampaignInfo( campaign ) {
        return '<div class="campaign-info">' +
            '<img src="/mobile/img_new/campaig-logos-bw/' + campaign.campaign_logo + '" alt="icon" />' +
            '<p>' +
                '<span class="campaign-info-title">' + campaign.subject_name + ' Campaign</span>' +
                campaign.subject_description +
            '</p>' +
        '</div>';
    }

    function setupSlider( start_index ){
        start_index = start_index || 0;

        $('#medal-slider').slick({
            initialSlide: start_index,  infinite: true, dots: false, centerMode: true,
            centerPadding: '0px', slidesToScroll: 1, asNavFor: '#medal-stickers', mobileFirst: true, focusOnSelect: true,
            prevArrow: '<button type="button" class="slick-prev"><img src="/mobile/img_new/arrow-1-color-orange-svg.svg"/></button>',
            nextArrow: '<button type="button" class="slick-next"><img src="/mobile/img_new/arrow-1-color-orange-svg.svg"/></button>',
            responsive: [{
                breakpoint: 767,
                settings: { slidesToShow: 3, infinite: false, dots: true,  }
            }]
        });
        // sync the content below it with a fade effect. Set to infinite, however do not allow the user to swipe it ( causes bug at literal edge case on desktop )
        $('#medal-stickers').slick({
            initialSlide: start_index, lazyLoad: 'ondemand', slidesToShow: 1, slidesToScroll: 1, adaptiveHeight: true,
            arrows: false,  fade: true, infinite: true, swipe: false, asNavFor: '#medal-slider'
        });

        $('#medal-stickers').on('beforeChange', function(event, slick, currentSlide, nextSlide){
            renderFromCache( nextSlide );
        });
        // fix initial index bug
        $('#medal-slider').slick( 'slickGoTo', start_index );

        renderFromCache( start_index ); // render the first item index;
        $('#loading').hide();
        $('#medal-slider, #medal-stickers').show();
    }
    
    // render the item from the cache if it has not been rendered yet...
    function renderFromCache( index ){
        if ( cache[ index ] !== '' ) {
            $('#sticker-board-' + index).html( cache[ index ] );
            cache[ index ] = ''; // clear the cache once rendered;
            // fix issue where height is not calculated correctly on first load
            setTimeout( function() {
                var height = $('#sticker-board-' + index)[0].scrollHeight;
                $('#medal-stickers .slick-list.draggable').height( height );
                
            }, 750 ); // 750ms should be enough time to come in and fix the issue then.
        }
    }

    return { loadPage: loadPage }
}();

sticker_board.loadPage();