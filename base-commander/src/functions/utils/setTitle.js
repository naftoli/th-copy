import store from 'store/index';
import * as actions from 'store/login/actions'

// set the page title wherever it needs to be set..
export const setTitle = ( title ) => {
  if ( document ) {
    document.title = `${title} | Mashpia.com`;
  }
  store.dispatch( actions.setTitle( title ) );
}
