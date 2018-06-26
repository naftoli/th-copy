import * as types from '../types';

describe(`types`, () => {

  it(`exports 'errors/add_error' as ADD_ERROR`, () => {
    expect( types.ADD_ERROR ).toBe( 'errors/add_error' );
  });

  it(`exports 'errors/clear_error' as CLEAR_ERROR`, () => {
    expect( types.CLEAR_ERROR ).toBe( 'errors/clear_error' );
  });

});