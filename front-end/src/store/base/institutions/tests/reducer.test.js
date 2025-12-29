import reducer, { initialState } from '../reducer';
import * as actions from '../actions';

describe( 'initialState', () => {
  
  it(`matches snapshot`, () => {
    expect( initialState ).toMatchSnapshot;
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

  it(`actions.setInstitutions: updates state.institutions`, () => {
    const institutions = [ { foo: 'bar' }, { bar: 'foo' } ];
    expect( reducer( initialState, actions.setInstitutions( institutions ) ).institutions ).toEqual( institutions );
    expect( initialState.institutions ).toEqual( [] );
  });

});