var hebrew_keyboard = function(){
    // all the keys we want to replace
    var keys = {
        'q': '/',   'w': '\'', 'e': 'ק',   'r': 'ר',  't': 'א',
        'y': 'ט',   'u': 'ו',  'i': 'ן',   'o': 'ם',  'p': 'פ',
        'a': 'ש',   's': 'ד',  'd': 'ג',   'f': 'כ',  'g': 'ע',
        'h': 'י',   'j': 'ח',  'k': 'ל',   'l': 'ך',  ';': 'ף',
        '\'': ',',  'z': 'ז',  'x': 'ס',   'c': 'ב',  'v': 'ה',
        'b': 'נ',   'n': 'מ',  'm': 'צ',   ',': 'ת',  '.': 'ץ',
    }
    // function to replace keys when typing
    function onKeyPressed( event ){
        var characters = event.target.value.split("");
        // get the character
        $.each( characters, function( index, character ){
            if( keys[ character ] ){
                characters[index] =  keys[ character ];
            };
        });
        event.target.value = characters.join("");
        // if we have a letter to replace it...
        event.preventDefault(); // do not add the typed letter
    }
    // funciton to attach to ids
    function attachInput( id ){
        $( id ).keyup( onKeyPressed );
    }
    // public functions
    return {
        attach: attachInput
    }
}();