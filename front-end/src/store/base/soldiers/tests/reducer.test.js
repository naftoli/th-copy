import reducer, { initialState } from '../reducer';
import * as actions from '../actions';

describe( 'initialState', () => {
  
  it(`has a key 'loading' set to 'false'`, () => {
    expect( initialState.loading ).toBe( false );
  });

  it(`has a key 'soldiers' set to '[]'`, () => {
    expect( initialState.soldiers ).toEqual( [] );
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

  it(`actions.setSoldiers: updates state.soldiers`, () => {
    const users = [ { foo: 'bar' }, { bar: 'foo' } ];
    expect( reducer(initialState, actions.setSoldiers( users )).soldiers ).toEqual( users );
    expect( initialState.soldiers ).toEqual( [] );
  });

});