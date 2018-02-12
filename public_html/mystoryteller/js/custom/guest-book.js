function tz_init(defaultwidth){
    var contentWidth    = jQuery('#wrap-baiviet').width();
    var columnWidth     = defaultwidth;
    if(columnWidth >contentWidth ){
    columnWidth = contentWidth;
    }
var  curColCount = Math.floor(contentWidth / columnWidth);
var newwidth = columnWidth * curColCount;
var newwidth2 = contentWidth - newwidth;
var newwidth3 = newwidth2/curColCount;
var newColWidth = Math.floor(columnWidth + newwidth3);
jQuery('.warp-comment').css("width",newColWidth);
jQuery('#wrap-baiviet').masonry({
    itemSelector: '.warp-comment'
    });
}
var resizeTimer = null;
jQuery(window).bind('resize', function() {
    if (resizeTimer) clearTimeout(resizeTimer);
    resizeTimer = setTimeout("tz_init("+"260)", 100);
    });
jQuery.noConflict();

jQuery(document).ready(function(){
    tz_init(260);
    jQuery('#nnt_comment_a1').click(function(){
    jQuery('#tz-Guestbook-warp').fadeIn();
    jQuery('#warp-fom').fadeIn();
    jQuery('#tz-guestbook-h5-img').click(function(){
    jQuery('#tz-Guestbook-warp').fadeOut();
    });
});
// -------------------------------------------------------------
// check full name
jQuery('#warp-input1').focus(function(){
    var inpName = jQuery('#warp-input1').attr('value');
    if(inpName == "Full name"){
    jQuery('#warp-input1').attr('value','');
    }
if(inpName !=""){
    jQuery('#warp-input1').keyup(function(){
        var maxName = 200;
        var inpName =jQuery('#warp-input1').attr('value');
        jQuery(".tz_input_name").css("display","block");
        var pp = document.getElementById("pname");
        var countTen =inpName.length;
        var HieuName = maxName - countTen;
        if(HieuName > 0){
            pp.innerHTML="Characters for  full name : "+ HieuName;
        }else {
    pp.innerHTML ="You cannot enter more not add text";
    }
});
}
});

jQuery('#warp-input1').blur(function(){
    var inpName =jQuery('#warp-input1').attr('value');
    jQuery(".tz_input_name").css("display","none");
    if(inpName==""){
    document.getElementById("warp-input1").value="Full name";
    }
}); // end full name

// check email
jQuery('#warp-input2').focus(function(){
    var inpEmail =jQuery('#warp-input2').attr('value');
    if(inpEmail =="Email"){
    jQuery('#warp-input2').attr('value','');
    }
if(inpEmail !=""){
    jQuery('#warp-input2').keyup(function(){
        var maxEmail = 200;
        var inpEmail =jQuery('#warp-input2').attr('value');
        jQuery(".tz_input_email").css("display","block");
        var ppemail = document.getElementById("pemail");
        var countTen =inpEmail.length;
        var HieuName = maxEmail - countTen;
        if(HieuName > 0){
            ppemail.innerHTML="Characters for  email  : "+ HieuName;
        } else {
    ppemail.innerHTML ="You cannot enter more not add text";
    }
});
}
});

jQuery('#warp-input2').blur(function(){
    var inpName =jQuery('#warp-input2').attr('value');
    jQuery(".tz_input_email").css("display","none");
    if(inpName==""){
    document.getElementById("warp-input2").value="Email";
    }
}); // end email

// check title
jQuery('#warp-input3').focus(function(){
    var inpTitle = jQuery('#warp-input3').attr('value');
    if(inpTitle  == "Title"){
    jQuery('#warp-input3').attr('value','');
    }
if(inpTitle !=""){
    jQuery('#warp-input3').keyup(function(){
        var maxTile     =  500;
        var inpTitle    =  jQuery('#warp-input3').attr('value')
        jQuery(".tz_input_title").css("display","block");
        var pptitle     = document.getElementById("ptitle");
        var countTen    = inpTitle.length;
        var HieuName    = maxTile - countTen;
        if(HieuName > 0){
            pptitle.innerHTML = "Characters for  title  : "+ HieuName;
        }else {
    pptitle.innerHTML = "You cannot enter more not add text";
    }
});
}
});

jQuery('#warp-input3').blur(function(){
    var inpTitle = jQuery('#warp-input3').attr('value');
    jQuery(".tz_input_title").css("display","none");
    if(inpTitle == ""){
    document.getElementById("warp-input3").value="Title";
    }
}); // end title

// end website
jQuery('#warp-input4').focus(function(){
    var inpWeb = jQuery('#warp-input4').attr('value');
    if(inpWeb  == "Your website"){
    jQuery('#warp-input4').attr('value','');
    }
if(inpWeb != ""){
    jQuery('#warp-input4').keyup(function(){
        var maxWeb   = 200;
        var inpWeb   = jQuery('#warp-input4').attr('value')
        jQuery(".tz_input_website").css("display","block");
        var ppweb    = document.getElementById("p_website");
        var countTen = inpWeb.length;
        var HieuName = maxWeb - countTen;
        if(HieuName > 0){
            ppweb.innerHTML="Characters for  website  : "+ HieuName;
        }else {
    ppweb.innerHTML ="You cannot enter more not add text";
    }
});
}
});
jQuery('#warp-input4').blur(function(){
    var inpWeb = jQuery('#warp-input4').attr('value');
    jQuery(".tz_input_website").css("display","none");
    if(inpWeb == ""){
    document.getElementById("warp-input4").value="Your website";
    }
}); // end website

// check comment
jQuery('#text-ra').focus(function(){
    var inpWeb = jQuery('#text-ra').attr('value');
    if(inpWeb == "Your guestbook..."){
    jQuery('#text-ra').attr('value','');
    }
if(inpWeb !=""){
    jQuery('#text-ra').keyup(function(){
        var maxWeb   = 2000;
        var inpWeb   = jQuery('#text-ra').attr('value')
        jQuery(".tz_input_comment").css("display","block");
        var ppweb    = document.getElementById("p_nntconten");
        var countTen =inpWeb.length;
        var HieuName = maxWeb - countTen;
        if(HieuName > 0){
            ppweb.innerHTML = "Characters for  website  : "+ HieuName;
        }else {
    ppweb.innerHTML = "You cannot enter more not add text";
    }
});
}
});

jQuery('#text-ra').blur(function(){
    var inpWeb = jQuery('#text-ra').attr('value');
    jQuery(".tz_input_comment").css("display","none");
    if(inpWeb  == ""){
    document.getElementById("text-ra").value="Your guestbook...";
    }
}); // end comment
// end fom

//-----------------------------------------------------------------------------------------//

jQuery('#warp-input-sub').click(function(){
    var subname     = jQuery('#warp-input1').attr('value');
    var erroname  = jQuery('#warp-input1');
    var pp      = document.getElementById("pname");

    var subemail    = jQuery('#warp-input2').attr('value');
    var erroemail  = jQuery('#warp-input2');
    var ppemail = document.getElementById("pemail");

    var subtitle     = jQuery('#warp-input3').attr('value');
    var errotitle  = jQuery('#warp-input3');
    var ptitle  = document.getElementById("ptitle");

    var subcontent    = jQuery('#text-ra').attr('value');
    var errocontent = jQuery('#text-ra');
    var p_nntconten = document.getElementById("p_nntconten");

    var websi = jQuery('#warp-input4').attr('value');
    var loiwebsite = jQuery('#warp-input4');
    var p_website = document.getElementById("p_website");

    var str2 = /^([a-zA-Z0-9_\.])+\@([a-zA-Z0-9_\-])+\.([a-zA-Z]{2,4})([a-z-A-Z\.]{2,4})?$/;
var srt3 =/^http(s)?:\/\/(www\.)?([a-zA-Z0-9\_])+\.([a-zA-Z0-9\/]{1,5})+(\.[A-Za-z0-9\/]{1,4})?([a-zA-Z0-9\/\.&=_\+\#\-\?]*)?$/


if( subname ==""){
    jQuery(".tz_input_name").css("display","block");
    pp.innerHTML="You not be empty";
    erroname.focus();
    return false;
    }else if(subname == 'Full name'){
    jQuery(".tz_input_name").css("display","block");
    pp.innerHTML="You have not entered  full name";
    erroname.focus();
    return false;
    }
if( subemail ==""){
    jQuery(".tz_input_email").css("display","block");
    ppemail.innerHTML="You not be empty";
    erroemail.focus();
    return false;
    }else if(str2.test(subemail) == false){
    jQuery(".tz_input_email").css("display","block");
    ppemail.innerHTML="Email is invalid";
    erroemail.focus();
    return false;
    }
if( subtitle ==""){
    jQuery(".tz_input_title").css("display","block");
    ptitle.innerHTML="You not be empty";
    errotitle.focus();
    return false;
    }else if(subtitle == 'Title'){
    jQuery(".tz_input_title").css("display","block");
    ptitle.innerHTML="You have not entered title";
    errotitle.focus();
    return false;
    }
if(websi !="" && websi != "Your website"){
    if(srt3.test(websi) == false){
    jQuery(".tz_input_website").css("display","block");
    p_website.innerHTML="Website is invalid ( ex: http://www.templaza.com/ )";
    loiwebsite.focus();
    return false;
    }
}
if( subcontent ==""){
    jQuery(".tz_input_comment").css("display","block");
    p_nntconten.innerHTML="You not be empty";
    errocontent.focus();
    return false;
    }else if(subcontent == 'Your guestbook...'){
    jQuery(".tz_input_comment").css("display","block");
    p_nntconten.innerHTML="You have not entered content";
    errocontent.focus();
    return false;
    }
var data_input = jQuery('#warp-check');
var inp = 0;
if(data_input.attr('checked')){
    inp = (data_input.attr('value'));
    }

jQuery.ajax({
    url: 'index.php?option=com_tz_guestbook&view=guestbook&task=add&Itemid=109',
    type: 'post',
    data:{
    name: jQuery('#warp-input1').attr('value'),
    email: jQuery('#warp-input2').attr('value'),
    title: jQuery('#warp-input3').attr('value'),
    website: jQuery("#warp-input4").attr('value'),
    content:jQuery('#text-ra').attr('value'),
    "1d800c523fd89a059e5e8e15e756bf65" : 1,
    recaptcha_response_field: jQuery('#recaptcha_response_field').attr('value'),
    recaptcha_challenge_field: jQuery('#recaptcha_challenge_field').attr('value'),
    check:inp
    }
}).success(function(data){

    var checkcapta = jQuery('#checkcapcha').attr('value');
    if(checkcapta  == 1){
    javascript:Recaptcha.reload();
    }
var statuss    = 1;
if(statuss == 1){
    jQuery('#wrap-baiviet').prepend( jQuery(data) ).masonry( 'reload' );
    tz_init(260);
    }
if(data==1){

    jQuery("#nnt-comment-input-loi-capchat").slideDown();
    jQuery("#nnt-comment-input-loi-capchat").animate({
    "opacity":"hide"
    },3000);
document.getElementById("nnt_p_capchar").innerHTML=" You enter the wrong captcha";
}
if(data != 1){
    jQuery('#warp-input3').attr('value','Title');
    jQuery('#text-ra').attr('value','Your guestbook...');
    jQuery('#warp-fom').hide();
    jQuery('#tz-Guestbook-seccess').slideDown(1200);
    jQuery("#tz-Guestbook-seccess").animate({
    "opacity":"hide"
    },2000,function(){
    jQuery('#tz-Guestbook-warp').fadeOut();
    });
}
});
});
});
jQuery(document).ready(function(){
var $container = jQuery('#wrap-baiviet') ;
$container.infinitescroll({
        navSelector  : '#loadaj a',
        nextSelector : '#loadaj a:first',
        itemSelector : '.warp-comment',
        errorCallback: function(){
            jQuery('#tz_append').removeAttr('style').html('<a id="tz_append-a"  class="btn btn-large-tz">No more page to load</a>');
        },
        loading: {
            msgText: "<em><em>Load<span> more...</span></em></em>",
            finishedMsg: '',
            img:'http://demo.templaza.com/j/meloul/templates/tz_meloul/images/loader.gif',
            selector: '#tz_append'
        }
    },
    function( newElements ){
        if(newElements.length){
            var arrganerme_gustbooks = 0;
            if(arrganerme_gustbooks  == 0){
                $container.append(jQuery(newElements));
                tzSortFilter(jQuery('.warp-comment'),$container);
                $container.masonry( 'reload' );
            }else{
                jQuery('#wrap-baiviet').prepend( jQuery(newElements) ).masonry( 'reload' );
            }
            tz_init(260);

            jQuery('div#tz_append').find('a:first').show();
        }
    });

function setDataOrder(srcObj){
    var maxOrder    = parseInt(srcObj.first().attr('data-order'));

    srcObj.each(function(){
        if(maxOrder < parseInt(jQuery(this).attr('data-order')) && jQuery(this).attr('id') != 'nnt_comment'){
            maxOrder    = parseInt(jQuery(this).attr('data-order'));
        }
    });
    maxOrder    += 1;
    jQuery('#nnt_comment').attr('data-order',maxOrder);
}
//Sort
function tzSortFilter(srcObj,desObj,order){
    //Set data-order for element again.
    setDataOrder(srcObj);

    srcObj.sort(function(a,b){
        var compA = jQuery(a).attr('data-order');
        var compB = jQuery(b).attr('data-order');

        return (parseInt(compA) < parseInt(compB)) ? -1 : (parseInt(compA) > parseInt(compB)) ? 1 : 0;
    });
    desObj.stop().append(srcObj).height('auto');
}
});