import * as types from '../types';

describe(`types`, () => {

  it(`.SET_LOADING = 'login/set_loading'`, () => {
    expect( types.SET_LOADING ).toBe( 'login/set_loading' );
  });

  it(`.SET_ERRORS = 'login/set_errors'`, () => {
    expect( types.SET_ERRORS ).toBe( 'login/set_errors' );
  });

  it(`.SET_TOKENS = 'login/set_tokens'`, () => {
    expect( types.SET_TOKENS ).toBe( 'login/set_tokens' );
  });

  it(`.SET_USER = 'login/set_user'`, () => {
    expect( types.SET_USER ).toBe( 'login/set_user' );
  });

  it(`.LOGOUT = 'login/logout'`, () => {
    expect( types.LOGOUT ).toBe( 'login/logout' );
  });

});