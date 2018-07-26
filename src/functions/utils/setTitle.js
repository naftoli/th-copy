const setTitle = ( title ) => {
  if ( document ) {
    document.title = `${title} | Mashpia.com`;
  }
}

export default setTitle;