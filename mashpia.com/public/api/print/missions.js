// footer is apparently not rendered.
$(".userMission").each( function() {			
    var user = $(this).attr('id');	
    var user_id = user.substring(user.indexOf('-') + 1);
    var elem = this;

    $.ajax({
        url: '/ajax/getMissionInfo.php', 
        async: false, 
        data: { user_id : user_id }, 
        success: function( data ) {
            if ( typeof data == 'string' )
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
                100	: 'Sticker - Brias Haguf_outline bw.png',
                121 :  "day-school-Jewish Day 250 px.svg",
                122 :  "day-school-Jewish Uniform 250px.svg",
                124 :  "day-school-Health 250px.svg",
                125 :  "day-school_Torah 250px.svg",
                126 :  "day-school_Shabbat 250px.svg",
                127 :  "day-school_Special Days 250px.svg",
                129 :  "day-school_Kosher 250px.svg",
                130 :  "day-school_Tefilla.svg",
                131 :  "day-school-ahavat-yisrael.svg",
                132 :  "day-school-brachot.svg",
                133 :  "day-school-Tzedaka.svg",
                134 :  "day-school-honoring parents 250px.svg",
                135 :  "day-school-Middot-icon.svg"
            }
            
            var str = "<div class='finalFooter'>";
            $.each(data, function(i, val) { 
                str += "<span class='footer_info'>";
                var j = 0;
                var s = stickers;
                $.each(val, function(indx, value) {
                    //build footer info
                    if (j++ == 0) { //first get sticker info
                        // make sure subject has a sticker image
                        if (i in s) {
                            if (i < 120) str += "<img src='/mission_report/stickerOutlines/" + s[i] + "' /><br /><b>" + indx + "</b><br />";
                            else str += "<img src='/mission_report/campaignLogos/" + s[i] + "' /><br /><b>" + indx + "</b><br />";
                        }
                        else str += "<img src='' /><br /><b>" + indx + "</b><br />";
                    } else { //then get medal info
                        str += "<i>" + value + " to " + indx + "</i>";
                    }
                });
                str += "</span>"; 
            });
            str += "</div>";
            $(elem).find("#" + user_id).append(str);
        }
    });
});