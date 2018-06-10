import reducer, { initialState } from './reducer';

describe( 'initialState', () => {
  
  it(`has a key 'login' set to 'false'`, () => {
    expect( initialState.login ).toBe( false );
  });

  it(`has a key 'current_user' set to 'false'`, () => {
    expect( initialState.current_user ).toBe( false );
  });

  it(`has a key 'loading' set to 'false'`, () => {
    expect( initialState.loading ).toBe( false );
  });

  it(`has a key 'errors' set to '[]'`, () => {
    expect( initialState.errors ).toEqual( [] );
  });
});

describe( 'reducer', () => {

  it( 'returns the initial state', () => {
    expect( reducer(undefined, {}) ).toEqual( initialState );
  });
})