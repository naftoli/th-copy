import * as types from '../types';

describe(`types`, () => {

  it(`exports 'platoons/set_loading' as SET_LOADING`, () => {
    expect( types.SET_LOADING ).toBe( 'platoons/set_loading' );
  });

  it(`exports 'platoons/set_platoons' as SET_PLATOONS`, () => {
    expect( types.SET_PLATOONS ).toBe( 'platoons/set_platoons' );
  });

  it(`exports 'platoons/update_platoon' as UPDATE_PLATOON`, () => {
    expect( types.UPDATE_PLATOON ).toBe( 'platoons/update_platoon' );
  });

});