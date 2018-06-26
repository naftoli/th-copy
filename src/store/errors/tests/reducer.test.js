import reducer, { initialState } from '../reducer';
import * as actions from '../actions';

describe( 'initialState', () => {
  it(`is an empty array`, () => {
    expect( initialState ).toEqual( [] );
  });
});

describe( 'reducer', () => {

  it( 'returns the initial state', () => {
    expect( reducer(undefined, {}) ).toEqual( initialState );
  });

  it( 'adds an item to the state with actions.addError()', () => {
    const state = reducer( undefined, {} );
    const newstate = reducer( state, actions.addError( 'Hello World' ) );
    expect( state.length ).toBe( 0 );
    expect( newstate.length ).toBe( 1 );
  });

  it( 'removes an item from the state with actions.clearError( index )', () => {
    const state = [ { id: 'a' }, { id: 'b' }, { id: 'c' }, { id: 'd' } ];
    const newstate = reducer( state, actions.clearError( 'b' ));
    expect( state.length ).toBe( 4 );
    expect( newstate.length ).toBe( 3 );
    expect( newstate ).toEqual( [ { id: 'a' }, { id: 'c' }, { id: 'd' } ] )
  });

});