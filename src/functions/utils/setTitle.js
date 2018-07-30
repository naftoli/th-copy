// set the page title wherever it needs to be set..
const setTitle = ( title ) => {
  if ( document ) {
    document.title = `${title} | Mashpia.com`;
  }
  // TODO. set the title prop on Navbar.jsx
}

export default setTitle;