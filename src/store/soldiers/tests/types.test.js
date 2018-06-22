import * as types from '../types';

describe(`types`, () => {

  it(`exports 'soldiers/set_loading' as SET_LOADING`, () => {
    expect( types.SET_LOADING ).toBe( 'soldiers/set_loading' );
  });

  it(`exports 'soldiers/set_soldiers' as SET_SOLDIERS`, () => {
    expect( types.SET_SOLDIERS ).toBe( 'soldiers/set_soldiers' );
  });

});