import reducer, { initialState } from '../reducer';
import * as actions from '../actions';

describe( 'initialState', () => {
  
  it(`has a key 'loading' set to 'false'`, () => {
    expect( initialState.loading ).toBe( false );
  });

  it(`has a key 'bases' set to '[]'`, () => {
    expect( initialState.bases ).toEqual( [] );
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

  it(`actions.setBases: updates state.bases`, () => {
    const bases = [ { foo: 'bar' }, { bar: 'foo' } ];
    expect( reducer(initialState, actions.setBases( bases )).bases ).toEqual( bases );
    expect( initialState.bases ).toEqual( [] );
  });

});