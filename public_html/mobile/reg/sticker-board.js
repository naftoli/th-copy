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
function setupSlider( start_index ){
    $('#loading').hide();
    $('#medal-slider, #medal-stickers').show();
    // setup the top slider
    $('#medal-slider').slick({
        initialSlide: start_index || 0,
        infinite: true, centerMode: true,
        centerPadding: '0px',   slidesToScroll: 1,
        asNavFor: '#medal-stickers',    mobileFirst: true,
        dots: false,     centerMode: true,   focusOnSelect: true,
        prevArrow: '<button type="button" class="slick-prev"><img src="/mobile/img_new/arrow-1-color-orange-svg.svg"/></button>',
        nextArrow: '<button type="button" class="slick-next"><img src="/mobile/img_new/arrow-1-color-orange-svg.svg"/></button>',
        responsive: [{
            breakpoint: 767,
            settings: { slidesToShow: 3, infinite: false, dots: true }
        }]
    });
    // sync the content below it with a fade effect
    $('#medal-stickers').slick({
        initialSlide: start_index || 0, lazyLoad: 'ondemand',
        slidesToShow: 1,    slidesToScroll: 1,
        arrows: false,  fade: true, infinite: true,
        asNavFor: '#medal-slider', adaptiveHeight: true,
        responsive: [{
            breakpoint: 767,
            settings: { infinite: true }
        }]
    });
}

function renderPage() {
    var user_id = findGetParameter('id');
    var selected_subject = findGetParameter('subject');
    $.post( 'ajax/getMedalInfo.php?v=2', { user_id: user_id }, function( response ){
        var html1 = '';
        var html2 = '';
        response.forEach( function( item, index ){
            if( item.subject_id == selected_subject ) selected_subject = index;
            html1 += renderMedalSliderItem( item );
            html2 += '<div class="sticker-board">' + renderStickerRows( item.sticker_name, item.total, item.medal_info ) + '</div>'
        });
        $('#medal-slider').html( html1 );
        $('#medal-stickers').html( html2 );
        setupSlider( selected_subject );
    });
}

function renderMedalSliderItem( data ){
    var percent_compleate = ( data.total / data.subject_total ) * 100;
    var html = '<div class="medal-slider-item">';
    html    +=      '<img src="' + data.photo + '" class="medal">';
    html    +=      '<div class="medal-status progress">';
    html    +=          '<div class="progress-bar" role="progressbar" style="width: ' + percent_compleate + '%;"></div>';
    html    +=          '<span>' + data.total + ' / ' + data.subject_total + ' Missions</span>';
    html    +=      '</div>';
    html    += '</div>';
    return html;
}

function renderStickerRows( sticker_name, total, data ){
    var html = '';

    data.forEach( function( medal_info ){
        html += '<div class="row">' +
            '<div class="col-4 col-sm-3 medal-level">' +
                '<span class="levelText">Level ' + medal_info.medal_ord + '</span>' + 
                '<img class="medal-img" data-lazy="' + medal_info.photo + '" />' + 
                '<div class="medal-status progress">' + 
                    '<div class="progress-bar white" role="progressbar" style="width: 83.33333333333334%;"></div>' + 
                    '<span>1 to Red</span>' +
                '</div>' + 
            '</div>' +
            '<div class="col-8 col-sm-9 medal-level-stickers">';
        // render all the stickers
        for( var i = 0; i < medal_info.missions_required; i++ ) {
            var slot_number = ( medal_info.running_total - medal_info.missions_required ) + i;
            var slot_sticker = sticker_name + ( slot_number > total ? '_bw' : '' )
            html += '<div class="sticker">' +
                ( slot_number ) + 
                '<img data-lazy="//mashpia.com/mobile/img_new/stickers/' + slot_sticker + '.gif">'
            +'</div>';
        }
        // close the tags;
        html += '</div></div>';
    });

    return html;
}

renderPage();