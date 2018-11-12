// footer is apparently not rendered.
$(".userMission").each( function() {			
    var user = $(this).attr('id');	
    var user_id = user.substring(user.indexOf('-') + 1);
    var image = 'All';
    var elem = this;

    $.get( 
        '/ajax/getMissionInfo.php',
        {user_id : user_id, type : image}, 
        function( data ) {
            data = $.parseJSON(data);
            
            var stickers = {
                1	: 'Shabbos Mevorchim Tehillim.gif', 
                4	: 'Tefillah.gif',
                12	: 'Mivtzoim.gif',
                13	: 'Niggunim.gif',
                16	: 'Sticker - Hiskashrus outline.png', 
                21	: 'sefer hamitzvos bw.png',
                27	: 'Tanya.gif',
                40	: 'Yomei Dipagra.gif',
                41	: 'Avos Ubonim.gif',
                42	: 'Vihalachta Bidrachov.gif',
                45	: 'Cheshbon Hanefesh.gif',
                90	: 'Chitas.gif',
                100	: 'Sticker - Brias Haguf_outline bw.png'
            }
            
            var str = "<div class='finalFooter'>";
            $.each(data, function(i, val) { 
                str += "<span class='footer_info'>";
                var j = 0;
                var s = stickers;
                $.each(val, function(indx, value) {
                    //build footer info
                    if (j++ == 0) { //first get sticker info
                        str += "<img src='/mission_report/stickerOutlines/" + s[i] + "' /><br /><b>" + indx + "</b><br />";
                    } else { //then get medal info
                        str += "<i>" + value + " to " + indx + "</i>";
                    }
                });
                str += "</span>"; 
            });
            str += "</div>";
            $(elem).find("#" + user_id).append(str);
        }
    );
});