import { types, actions, operations } from '../actions';
import { API_URL } from 'api/api';
import fetchMock from 'fetch-mock';

describe(`types`, () => {

  it(`.SET_LOADING = 'login/set_loading'`, () => {
    expect( types.SET_LOADING ).toBe( 'login/set_loading' );
  });

  it(`.SET_ERRORS = 'login/set_errors'`, () => {
    expect( types.SET_ERRORS ).toBe( 'login/set_errors' );
  });

  it(`.SET_TOKENS = 'login/set_tokens'`, () => {
    expect( types.SET_TOKENS ).toBe( 'login/set_tokens' );
  });

  it(`.SET_USER = 'login/set_user'`, () => {
    expect( types.SET_USER ).toBe( 'login/set_user' );
  });

});

describe(`actions`, () => {

  describe(`.loading`, () => {

    it( `returns types.SET_LOADING as the type`, () => {
      expect( actions.loading().type ).toBe( types.SET_LOADING );
    });

    it( `returns it's paramater as 'payload'`, () => {
      expect( actions.loading( true ).payload ).toBe( true );
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

  describe(`.tokens`, () => {
    it( `returns types.SET_TOKENS as the type`, () => {
      expect( actions.tokens( '', '' ).type ).toBe( types.SET_TOKENS );
    });

    it( `returns it's first paramater as '.payload.legacy'`, () => {
      expect( actions.tokens( 'legacy', 'mobile' ).payload.legacy ).toEqual( 'legacy' );
    });

    it( `returns it's second paramater as '.payload.mobile'`, () => {
      expect( actions.tokens( 'legacy', 'mobile' ).payload.mobile ).toEqual( 'mobile' );
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
});

describe(`actions`, () => {
  let dispatchMock;

  beforeEach(() => {
    dispatchMock = jest.fn();
  });

  afterEach(() => { fetchMock.restore(); });

  describe( `.login`, () => {

    it( `calls 'dispatch' with 'actions.loading(true)'`, () => {
      fetchMock.post('*', { key: `abcd` } );
      operations.login( 'test', 'test' )( dispatchMock );
      expect( dispatchMock ).toHaveBeenCalledWith( actions.loading( true ) );
    });

    it( `sends a request to auth/login.php`, () => {
      const mock = fetchMock.post('*', { key: `abcd` } );
      operations.login( 'test', 'test' )( dispatchMock )
      expect( mock.called() ).toBe( true );
      expect( fetchMock.lastUrl() ).toBe( `${API_URL}/auth/login.php` );
    });

    it(`calls dispatch 4 times on a successfull login`, () => {
      fetchMock.post('*', { success: true, data: { legacy: 'legacy', mobile: 'mobile' } } );
      return operations.login( 'test', 'test' )( dispatchMock ).then( () => {
        expect( dispatchMock ).toHaveBeenCalledTimes( 4 );
      });
    });

    it(`calls dispatch 3 times on a un-successfull login`, () => {
      fetchMock.post('*', { success: false } );
      return operations.login( 'test', 'test' )( dispatchMock ).then( () => {
        expect( dispatchMock ).toHaveBeenCalledTimes( 3 );
      });
    });

  });
})