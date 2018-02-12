jQuery(document).ready(function(){

    new jPlayerPlaylist({
        jPlayer: "#jquery_jplayer_1",
        cssSelectorAncestor: "#jp_container_1",
        autoPlay: true
    }, [
        {
            title:"Cras lobortis augue",
            image:"http://placehold.it/900x600",
            artist:"John Doe",
            mp3:"audio/TSP-01-Cro_magnon_man.mp3",
            oga:"audio/TSP-01-Cro_magnon_man.ogg",
            descrip:"Pellentesque suscipit ligula id lorem interdum pellentesque. Vivamus vehicula scelerisque suscipit. Mauris ultrices bibendum ipsum ac posuere. Ut libero massa, cursus pretium bibendum at, eleifend id massa."
        },
        {
            title:"Maecenas imperdiet",
            image:"http://placehold.it/900x600",
            artist:"Quisque",
            mp3:"audio/TSP-01-Cro_magnon_man.mp3",
            oga:"audio/TSP-01-Cro_magnon_man.ogg",
            descrip:"Pellentesque suscipit ligula id lorem interdum pellentesque. Vivamus vehicula scelerisque suscipit. Mauris ultrices bibendum ipsum ac posuere. Ut libero massa, cursus pretium bibendum at, eleifend id massa."
        },
        {
            title:"Integer eros turpis",
            image:"http://placehold.it/900x600",
            artist:"Maecenas",
            mp3:"audio/TSP-01-Cro_magnon_man.mp3",
            oga:"audio/TSP-01-Cro_magnon_man.ogg",
            descrip:"Pellentesque suscipit ligula id lorem interdum pellentesque. Vivamus vehicula scelerisque suscipit. Mauris ultrices bibendum ipsum ac posuere. Ut libero massa, cursus pretium bibendum at, eleifend id massa."
        },
        {
            title:"Sed ipsum ligula",
            image:"http://placehold.it/900x600",
            artist:"Phasellus",
            mp3:"audio/TSP-01-Cro_magnon_man.mp3",
            oga:"audio/TSP-01-Cro_magnon_man.ogg",
            descrip:"Pellentesque suscipit ligula id lorem interdum pellentesque. Vivamus vehicula scelerisque suscipit. Mauris ultrices bibendum ipsum ac posuere. Ut libero massa, cursus pretium bibendum at, eleifend id massa."
        },
        {
            title:"Pellentesque suscipit",
            image:"http://placehold.it/900x600",
            artist:"Madonna",
            mp3:"audio/TSP-01-Cro_magnon_man.mp3",
            oga:"audio/TSP-01-Cro_magnon_man.ogg",
            descrip:"Pellentesque suscipit ligula id lorem interdum pellentesque. Vivamus vehicula scelerisque suscipit. Mauris ultrices bibendum ipsum ac posuere. Ut libero massa, cursus pretium bibendum at, eleifend id massa."
        }

    ],{
        swfPath: "js/jplayer",
        solution: 'html, flash',
        supplied: 'mp3,oga',
        wmode: "window"
    });
});
