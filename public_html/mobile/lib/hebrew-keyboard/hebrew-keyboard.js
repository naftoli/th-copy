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
        // get the character
        var character = String.fromCharCode(event.which).toLowerCase(); // get the typed character
        // if we have a letter to replace it...
        if( keys[ character ] ){
            event.target.value = event.target.value + keys[ character ];
            event.preventDefault(); // do not add the typed letter
        };
    }
    // funciton to attach to ids
    function attachInput( id ){
        $( id ).keydown( onKeyPressed );
    }
    // public functions
    return {
        attach: attachInput
    }
}();