import reducer, { initialState } from '../reducer';
import * as actions from '../actions';

describe( 'initialState', () => {
  
  it(`has a key 'loading' set to 'false'`, () => {
    expect( initialState.loading ).toBe( false );
  });

  it(`has a key 'platoons' set to '[]'`, () => {
    expect( initialState.platoons ).toEqual( [] );
  });
  
});

describe( 'reducer', () => {

  it( 'returns the initial state', () => {
    expect( reducer(undefined, {}) ).toEqual( initialState );
  });

  it(`actions.setLoading: updates state.loading`, () => {
    expect( reducer(initialState, actions.setLoading(true)).loading ).toBe( true );
    expect( initialState.loading ).toBe( false );
  });

  it(`actions.setPlatoons: updates state.platoons`, () => {
    const platoons = [ { foo: 'bar' }, { bar: 'foo' } ];
    expect( reducer(initialState, actions.setPlatoons( platoons )).platoons ).toEqual( platoons );
    expect( initialState.platoons ).toEqual( [] );
  });

});