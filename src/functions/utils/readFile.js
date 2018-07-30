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