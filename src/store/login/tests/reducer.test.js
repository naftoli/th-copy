import reducer, { initialState } from '../reducer';
import * as actions from '../actions';

import Cookies from 'universal-cookie';
const cookies = new Cookies();

describe( 'initialState', () => {
  
  it(`has a key 'tokens' set to '{}'`, () => {
    expect( initialState.tokens ).toEqual( {} );
  });

  it(`has a key 'current_user' set to 'false'`, () => {
    expect( initialState.current_user ).toEqual( false );
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

  it(`actions.setLoading: updates state.loading`, () => {
    expect( reducer(initialState, actions.setLoading(true)).loading ).toBe( true );
    expect( initialState.loading ).toBe( false );
  });

  describe('actions.setTokens', () => {

    it(`updates state.tokens`, () => {
      expect(
        reducer(initialState, actions.setTokens( 'legacy', 'mobile' ) ).tokens 
      ).toEqual( { legacy: 'legacy', mobile: 'mobile' } );
      expect( initialState.tokens ).toEqual( {} );
    });

    it('sets the admin_auth cookie', () => {
      cookies.remove('admin_auth');
      reducer(initialState, actions.setTokens( 'legacy', 'mobile' ) )
      expect( cookies.get('admin_auth') ).toBe( 'legacy' );
      cookies.remove('admin_auth');
    });

    it('sets the admin_id cookie', () => {
      cookies.remove('admin_id');
      reducer(initialState, actions.setTokens( 'legacy', 'mobile', 567 ));
      expect( cookies.get('admin_id') ).toBe( '567' ); // cookies always return a string
      cookies.remove('admin_id');
    });

  });
  
  describe('actions.setUser', () => {

    it(`updates state.current_user`, () => {
      const user = { foo: 'bar', logins: [ { type: 'user', id: 5 }] };
      expect( reducer(initialState, actions.setUser( user )).current_user ).toEqual( user );
      expect( reducer(initialState, actions.setUser( user )).current_login ).toEqual( user.logins[0] );
      expect( initialState.current_user ).toEqual( false );
      expect( initialState.current_login ).toEqual( {} );
    });

  });
  
  describe('actions.logout', () => {
    
    it(`clears the admin_auth cookie`, () => {
      cookies.set('admin_auth', 'legacy');
      reducer( initialState, actions.logout() );
      expect( cookies.get('admin_auth') ).not.toBeDefined();
    });
    
    it('clears the admin_id cookie', () => {
      cookies.set('admin_id', '1234');
      reducer( initialState, actions.logout() );
      expect( cookies.get('admin_id') ).not.toBeDefined();
    });

    it('returns the initial state', () => {
      const state = { loading: true };
      const result = reducer( state, actions.logout() );
      expect( result ).toEqual( initialState );
      expect( state.loading ).not.toEqual( result.loading );
    });
  
  });
})