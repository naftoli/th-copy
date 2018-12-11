jQuery(document).ready(function(){

    new jPlayerPlaylist({
        jPlayer: "#jquery_jplayer_1",
        cssSelectorAncestor: "#jp_container_1"
    }, [
    	{
            title:"The Story Of Yud Beis Tammuz",
            artist:"Rabbi Sholem Perl ",
            mp3:"../audio/Yud Beis Tammuz Trailer.mp3"
        },
        {
            title:"The Mystery of the Empty Shul",
            artist:"Rabbi Sholem Perl  ",
            m4a:"../audio/empty_shul.m4a",
            oga:"../audio/empty_shul.ogg",
            mp3:"../audio/empty_shul.mp3"

        },
        {
            title:"The Story of Chof Daled Teves – The Fierce Battle ",
            artist:"Rabbi Sholem Perl ",
            m4a:"../audio/Chof_Daled_Teves.m4a",
            oga:"../audio/Chof_Daled_Teves.ogg",
            mp3:"../audio/Chof_Daled_Teves.mp3"

        },
        {
            title:"Reb Amnon of Mainz",
            artist:"Rabbi Sholem Perl ",
            m4a:"../audio/Reb_Amnon.m4a",
            oga:"../audio/Reb_Amnon.ogg",
            mp3:"../audio/Reb_Amnon.mp3"

        },
        {
            title:"Trapped in a Vegetable",
            artist:"Rabbi Sholem Perl ",
            m4a:"../audio/Trapped_in_a_Vegetable.m4a",
            oga:"../audio/Trapped_in_a_Vegetable.ogg",
            mp3:"../audio/Trapped_in_a_Vegetable.mp3"

        },		
		 {
            title:"Voices From Another World",
            artist:"Rabbi Sholem Perl ",
            m4a:"../audio/Trailer Story Voices From Another World - .m4a",
            oga:"../audio/Trailer Story Voices From Another World - .ogg",
            mp3:"../audio/Trailer Story Voices From Another World - .mp3"

        },		
		 {
            title:"A Siddur Flies To Heaven",
            artist:"Rabbi Sholem Perl  ",
            m4a:"../audio/A_Sidder_Flies_To_Heaven.m4a",
            oga:"../audio/A_Sidder_Flies_To_Heaven.ogg",
            mp3:"../audio/A_Sidder_Flies_To_Heaven.mp3"

        },
         {
            title:"The Water Carrier ",
            artist:"Rabbi Sholem Perl ",
            m4a:"../audio/The_Water_Carrier.m4a",
            oga:"../audio/The_Water_Carrier.ogg",
            mp3:"../audio/The_Water_Carrier.mp3"

        },		
		 {
            title:"The Speechless Talmid",
            artist:"Rabbi Sholem Perl  ",
            m4a:"../audio/The_Speechless_Talmid_.m4a",
            oga:"../audio/The_Speechless_Talmid_.ogg",
            mp3:"../audio/The_Speechless_Talmid_.mp3"

        },
         {
            title:"The Three Smiles ",
            artist:"Rabbi Sholem Perl ",
            m4a:"../audio/The_Three_Smiles.m4a",
            oga:"../audio/The_Three_Smiles.ogg",
            mp3:"../audio/The_Three_Smiles.mp3"

        }, 
        {
            title:"The Story of Yud Tes Kislev",
            artist:"Rabbi Sholem Perl ",
            m4a:"../audio/Yud_Kislev_Trailer_.m4a",
            oga:"../audio/Yud_Kislev_Trailer_.ogg",
            mp3:"../audio/Yud_Kislev_Trailer_.mp3"

        }
    ], {
        swfPath: "../../js/jplayer",
        solution: 'html, flash',
        supplied: "mp3,m4a,ogg",
        wmode: "window"
    });
});
