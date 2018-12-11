import store from 'store/index';
import { LEGACY_URL } from 'components/constants';
import { COOKIES, EXPIRES } from 'store/constants';

// detect login changes
export const loginStoreChanged = ( old_login ) => {
  const new_login = store.getState().login.current_login
  const { type, id } = new_login;
  const { type: prevType, id: prevId } = old_login;
  // return if true if there are any differences between the objects
  return type !== prevType || prevId !== id;
}
// login to the mobile site with a given key
export const mobileLogin = ( key, newTab = true ) => {
  COOKIES.set('admin', key, { path: '/', EXPIRES } );
  const url = `${LEGACY_URL}/mobile/reg/parent_detail.html`;

  if ( newTab ) {
    window.open( url, '_blank' ).focus();
  } else {
    window.document.location.href = url;
  }
}


export const isHQ = code => {
  return code === 'HQ';
}

export const isAdmin = code => {
  return [ 'HQ', 'INST' ].includes( code );
}

export const isBC = ( code, only = false ) => {
  if ( only )
    return code === 'BC';
  return [ 'HQ', 'INST', 'BC' ].includes( code );
}

export const isTeacher = code => {
  return code === 'TEACHER';
}

export const isBlank = code => {
  return code === 'BLANK';
}
