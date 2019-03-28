function tz_init(defaultwidth){
    var contentWidth    = jQuery('#timeline').width();
    var columnWidth     = defaultwidth;
    var curColCount     = 0;
    var maxColCount     = 0;
    var newColCount     = 0;
    var newColWidth     = 0;
    var featureColWidth = 0;

    curColCount = Math.floor(contentWidth / columnWidth);
    maxColCount = curColCount + 1;
    if((maxColCount - (contentWidth / columnWidth)) > ((contentWidth / columnWidth) - curColCount)){
    newColCount     = curColCount;
    }
else{
    newColCount = maxColCount;
    }

newColWidth = contentWidth;
featureColWidth = contentWidth;


if(newColCount > 1){
    newColWidth = Math.floor(contentWidth / newColCount);
    featureColWidth = newColWidth * 2;
    }
jQuery('.element').width(newColWidth);
jQuery('.tz_item').each(function(){
    jQuery(this).find('img').first().attr('width','100%');
    });

jQuery('.tz_feature_item').width(featureColWidth);
jQuery('.TzDate').each(function(){
    //               jQuery(this).width(contentWidth);
    });
var $container = jQuery('#timeline');
$container.imagesLoaded(function(){
    $container.isotope({
        masonry:{
            columnWidth: newColWidth
        }
});
});
}
jQuery(document).ready(function(){

    jQuery(function(){
        var tzpage    =   1;
        function getTags() {
        var tags    =   [];
        jQuery('#filter a').each(function (index) {
        tags.push(jQuery(this).attr('data-option-value').replace(".",""));
        });
    return JSON.encode(tags);
    }


    var LastDate = jQuery('div.TzDate:last').attr('data-category');

    var $container = jQuery('#timeline');

    jQuery('#tz_append').css({'border':0,'background':'none'});

    $container.infinitescroll({
        navSelector  : '#loadaj a',    // selector for the paged navigation
        nextSelector : '#loadaj a:first',  // selector for the NEXT link (to page 2)
        itemSelector : '.element',     // selector for all items you'll retrieve
        bufferPx:   200,
        errorCallback: function(){
        jQuery('#tz_append').removeAttr('style').html('<a class="tzNomore">No more pages <span>to load</span> </a>');
        jQuery('#tz_append a').addClass('tzNomore');
        },
    loading: {
        msgText:'<em>Load<span> more...</span></em>',
        finishedMsg: '',
        img:'images/default.gif',
        selector: '#tz_append'
        }
    },
    // call Isotope as a callback
    function( newElements ) {

        var $newElems =   jQuery( newElements ).css({ opacity: 0 });


    // ensure that images load before adding to masonry layout
    $newElems.imagesLoaded(function(){


        // show elems now they're ready
        $newElems.animate({ opacity: 1 });
    var LastDate2 = null;

    tz_init(300);

    // trigger scroll again
    $container.isotope( 'insert', $newElems);

    var str = '';

    // Delete date haved
    $newElems.each(function(index){
        var tzClass = jQuery(this).attr('class');
        if(tzClass.match(/.*?TzDate.*?/i)){
        var LastDate2 = jQuery(this).attr('data-category');
        if(LastDate == LastDate2){

        jQuery(this).remove();
        $container.isotope('reloadItems');
        }
    else
    LastDate    = LastDate2;
    }
    });

    tzpage++;
    jQuery.ajax({
        url:'index.php?option=com_tz_portfolio&amp;task=timeline.ajaxtags',
        data:{
        'tags':getTags(),
        'Itemid':'147',
        'page': tzpage
        }
    }).success(function(data){
        if (data.length) {
        tztag   = jQuery(data);
        jQuery('#filter ul').append(tztag);
        loadTimeline();

        }
    });


    //Sort tags or categories filter
    tzSortFilter(jQuery('#filter ul').find('li'),jQuery('#filter ul'),'auto');

    //if there still more item
    if($newElems.length){

        //move item-more to the end
        jQuery('div#tz_append').find('a:first').show();
        }
    });

    }
    );

    });

    var resizeTimer = null;
    jQuery(window).bind('load resize', function() {
        if (resizeTimer) clearTimeout(resizeTimer);
        resizeTimer = setTimeout("tz_init("+"300)", 100);
    });

    var $container = jQuery('#timeline');
    $container.imagesLoaded( function(){
        $container.isotope({
            itemSelector    : '.element',
            layoutMode      : 'masonry',
            sortBy          : 'date',
            getSortData     : {
                name: function( $elem ) {
                    var name = $elem.find('.name'),
                        _date   = $elem.attr('data-category'),
                        itemText = name.length ? name : $elem.text();
                    if(_date.length){
                        itemText = _date + itemText;
                    }

                    return itemText;
                },
                date: function($elem){
                    var number = $elem.hasClass('element') ?
                        $elem.find('.create').text() :
                        $elem.attr('data-date');
                    return number;

                }
            }
        },function(){

            //Sort tags or categories filter
            tzSortFilter(jQuery('#filter ul').find('li'),jQuery('#filter ul'),'auto');
        });
        tz_init(300);
    });

    function loadTimeline(){
        var $optionSets = jQuery('#tz_options .option-set'),
            $optionLinks = $optionSets.find('a');
        $optionLinks.click(function(event){
            event.preventDefault();
            var $this = jQuery(this);
            // don't proceed if already selected
            if ( $this.hasClass('selected') ) {
                return false;
            }
            var $optionSet = $this.parents('.option-set');
            $optionSet.find('.selected').removeClass('selected');
            $optionSet.find('.arrow_box').removeClass('arrow_box');
            $this.addClass('selected');
            $this.parent().addClass('arrow_box');
            $this.addClass('selected');

            // make option object dynamically, i.e. { filter: '.my-filter-class' }
            var options = {},
                key = $optionSet.attr('data-option-key'),
                value = $this.attr('data-option-value');
            // parse 'false' as false boolean

            value = value === 'false' ? false : value;
            options[ key ] = value;
            if ( key === 'layoutMode' && typeof changeLayoutMode === 'function' ) {

                // changes in layout modes need extra logic
                changeLayoutMode( $this, options )
            } else {
                // otherwise, apply new options
                $container.isotope( options );
            }

            return false;
        });
    }
    loadTimeline();


    });