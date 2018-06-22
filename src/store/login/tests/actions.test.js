import * as actions from '../actions';
import * as types from '../types';

describe(`actions`, () => {

  describe(`.setLoading`, () => {

    it( `returns types.SET_LOADING as the type`, () => {
      expect( actions.setLoading().type ).toBe( types.SET_LOADING );
    });

    it( `returns it's paramater as 'payload'`, () => {
      expect( actions.setLoading( true ).payload ).toBe( true );
    });

  });

  describe(`.setErrors`, () => {

    it( `returns types.SET_ERRORS as the type`, () => {
      expect( actions.setErrors( false ).type ).toBe( types.SET_ERRORS );
    });

    it( `returns it's paramater as 'payload'`, () => {
      expect( actions.setErrors( [ 'test' ] ).payload ).toEqual( [ 'test' ] );
    });

    it( `converts it's paramater to an array if one is not passed in`, () => {
      expect( actions.setErrors( false ).payload ).toEqual( [ false ] );
    });

  });

  describe(`.setTokens`, () => {
    it( `returns types.SET_TOKENS as the type`, () => {
      expect( actions.setTokens( '', '' ).type ).toBe( types.SET_TOKENS );
    });

    it( `returns it's first paramater as '.payload.legacy'`, () => {
      expect( actions.setTokens( 'legacy', 'mobile' ).payload.legacy ).toEqual( 'legacy' );
    });

    it( `returns it's second paramater as '.payload.mobile'`, () => {
      expect( actions.setTokens( 'legacy', 'mobile' ).payload.mobile ).toEqual( 'mobile' );
    });

  });

  describe(`.setUser`, () => {

    it( `returns types.SET_USER as the type`, () => {
      expect( actions.setUser( {} ).type ).toBe( types.SET_USER );
    });

    it( `returns it's paramater as 'payload'`, () => {
      const user = { foo: 'bar' };
      expect( actions.setUser( user ).payload ).toEqual( user );
    });

  });

  describe(`.logout`, () => {

    it( `returns types.LOGOUT as the type`, () => {
      expect( actions.logout().type ).toBe( types.LOGOUT );
    });

    it( `has no payload`, () => {
      expect( actions.logout().payload ).not.toBeDefined();
    });

  });
});