import reducer from '../reducer';
import * as actions from '../actions';

const initialState = reducer( undefined, {} );

describe( 'reducer', () => {

  it(`has a key 'loading' that defaults to 'false'`, () => {
    expect( initialState.loading ).toBe( false );
  });

  it(`has a key 'parshos' that defaults to '[]'`, () => {
    expect( initialState.parshos ).toEqual( [] );
  });

  it(`actions.setLoading: updates state.loading`, () => {
    expect( reducer( initialState, actions.setLoading(true) ).loading ).toBe( true );
    expect( initialState.loading ).toBe( false );
  });

  it(`actions.setParshos: updates state.parshos`, () => {
    const parshos = [ { foo: 'bar' }, { bar: 'foo' } ];
    expect( reducer(initialState, actions.setParshos( parshos )).parshos ).toEqual( parshos );
    expect( initialState.parshos ).toEqual( [] );
  });

});