import * as types from '../types';

describe(`types`, () => {

  it(`exports 'bases/set_loading' as SET_LOADING`, () => {
    expect( types.SET_LOADING ).toBe( 'bases/set_loading' );
  });

  it(`exports 'bases/set_bases' as SET_BASES`, () => {
    expect( types.SET_BASES ).toBe( 'bases/set_bases' );
  });

  it(`exports 'bases/update_base' as UPDATE_BASE`, () => {
    expect( types.UPDATE_BASE ).toBe( 'bases/update_base' );
  });

});