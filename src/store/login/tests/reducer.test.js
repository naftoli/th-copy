import reducer, { initialState } from '../reducer';
import { actions } from '../actions';

import Cookies from 'universal-cookie';
const cookies = new Cookies();

describe( 'initialState', () => {
  
  it(`has a key 'tokens' set to '{}'`, () => {
    expect( initialState.tokens ).toEqual( {} );
  });

  it(`has a key 'current_user' set to '{}'`, () => {
    expect( initialState.current_user ).toEqual( {} );
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

  it(`actions.setErrors: updates state.errors`, () => {
    expect( reducer(initialState, actions.setErrors("Test")).errors ).toEqual(["Test"]);
    expect( initialState.errors ).toEqual( [] );
  });

  it(`actions.loading: updates state.loading`, () => {
    expect( reducer(initialState, actions.loading(true)).loading ).toBe( true );
    expect( initialState.loading ).toBe( false );
  });

  describe('actions.tokens', () => {
    it(`updates state.tokens`, () => {
      expect(
        reducer(initialState, actions.tokens( 'legacy', 'mobile')).tokens 
      ).toEqual( { legacy: 'legacy', mobile: 'mobile' } );
      expect( initialState.tokens ).toEqual( {} );
    });
    it('sets the admin_auth cookie', () => {
      cookies.remove('admin_auth');
      reducer(initialState, actions.tokens( 'legacy', 'mobile'))
      expect( cookies.get('admin_auth') ).toBe( 'legacy' );
      cookies.remove('admin_auth');
    })
  })
  
  describe('actions.setUser', () => {
    it(`updates state.current_user`, () => {
      const user = { foo: 'bar' };
      expect( reducer(initialState, actions.setUser( user )).current_user ).toEqual( user );
      expect( initialState.current_user ).toEqual( {} );
    });
    it('sets the admin_id cookie', () => {
      cookies.remove('admin_id');
      reducer(initialState, actions.setUser( { admin_id: 567 } ));
      expect( cookies.get('admin_id') ).toBe( '567' ); // cookies always return a string
      cookies.remove('admin_id');
    });
  });
  

})