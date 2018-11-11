const hebrew_map = {
  "q": "/", "w": "'", "e": "ק", "r": "ר", "t": "א", "y": "ט", "u": "ו", "i": "ן", "o": "ם", "p": "פ",
  "a": "ש", "s": "ד", "d": "ג", "f": "כ", "g": "ע", "h": "י", "j": "ח", "k": "ל", "l": "ך", ";": "ף", "'": ",",
  "z": "ז", "x": "ס", "c": "ב", "v": "ה", "b": "נ", "n": "מ", "m": "צ", ",": "ת", ".": "ץ", "/": "." 
}

export const toHebrew = ( text ) => {
  return text.toLowerCase()
    .split('')
    .map( character => hebrew_map[ character ] || character )
    .join('')
}

// read a file as a data url
export const readFile = ( file ) => {
  return new Promise( ( resolve, reject ) => {
    if ( FileReader && file ) {
      const fr = new FileReader();
      fr.onload = () => { resolve( fr ) }
      fr.readAsDataURL( file );
    } else {
      reject( new Error('Your browser does not support reading files.') );
    }
  });
}
