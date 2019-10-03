import API from 'api/api';
import * as actions from '../actions';
import * as operations from '../operations';

jest.mock( 'api/api' );

describe(`operations`, () => {
  let dispatchMock;

  beforeEach(() => {
    dispatchMock = jest.fn();
  });

  afterEach(() => {
    API.post.mockReset();
  });

  describe( `.login( opts )`, () => {

    const response = { legacy: '5',  mobile: '5',  id: '5' };

    beforeEach(() => {
      API.post.mockResolvedValue( response );
    });

    it( `calls 'dispatch' with 'actions.setLoading(true)'`, () => {
      const data = { username: 5, password: 991234 };

      operations.login( data )( dispatchMock );

      expect( dispatchMock ).toHaveBeenCalledWith( actions.setLoading( true ) );
    });

    it( `sends 1 request to auth/login`, () => {
      const data = { username: 5, password: 991234 };

      operations.login( data )( dispatchMock );

      expect( API.post.mock.calls.length ).toBe( 1 );
      expect( API.post.mock.calls[0][0] ).toBe( `/auth/login` );
    });

    it( `sends the argument as the request body`, () => {
      const data = { username: 5, password: 991234 };

      operations.login( data )( dispatchMock );

      expect( API.post.mock.calls.length ).toBe( 1 );
      expect( API.post.mock.calls[0][1] ).toEqual( data );
    });

    it( `sets the tokens`, async () => {
      const data = { username: 'foo', password: 'bar' };

      await operations.login( data )( dispatchMock ).then(() => {
        // make sure that one post request happened
        expect( API.post.mock.calls.length ).toBe( 1 );
        expect( API.get.mock.calls.length ).toBe( 0 );

        const { legacy, mobile, id } = response;
        let tokenSubject = actions.setTokens( legacy, mobile, id );

        expect( dispatchMock ).toHaveBeenCalledWith( tokenSubject );
      });
    });
  });

  describe( `.createAccount( opts )`, () => {

    const response = {
      tokens: { legacy: '5',  mobile: '5',  id: '5' },
      account: { foo: 'bar' }
    };

    beforeEach(() => {
      API.post.mockResolvedValue( response );
    });

    it( `sends 1 request to auth/new_account`, () => {
      const data = { username: 5, password: 991234 };

      operations.createAccount( data )( dispatchMock );

      expect( API.post.mock.calls.length ).toBe( 1 );
      expect( API.post.mock.calls[0][0] ).toBe( `/auth/new_account` );
    });

    it( `sends the argument as the request body`, () => {
      const data = { username: 5, password: 991234 };

      operations.createAccount( data )( dispatchMock );

      expect( API.post.mock.calls.length ).toBe( 1 );
      expect( API.post.mock.calls[0][1] ).toEqual( data );
    });

    it( `sets the tokens and current_user`, async () => {
      const data = { username: 'foo', password: 'bar' };

      await operations.createAccount( data )( dispatchMock ).then(() => {
        // make sure that one post request happened
        expect( API.post.mock.calls.length ).toBe( 1 );
        expect( API.get.mock.calls.length ).toBe( 0 );

        const { legacy, mobile, id } = response.tokens;
        let tokenSubject = actions.setTokens( legacy, mobile, id );
        let setUserSubject = actions.setUser( response.account );

        expect( dispatchMock ).toHaveBeenCalledWith( tokenSubject );
        expect( dispatchMock ).toHaveBeenCalledWith( setUserSubject );
      });
    });
  });

  describe( `.getCurrentUser()`, () => {
    // sample response data
    const response = {
      username: 'foo', password: 'bar'
    };
    // setup the mock
    beforeEach(() => API.get.mockResolvedValue( response ) );
    // after each test reset the mock
    afterEach(() => API.get.mockReset() );

    it( `calls 'dispatch' with 'actions.setLoading(true)'`, () => {
      operations.getCurrentUser()( dispatchMock );

      expect( dispatchMock ).toHaveBeenCalledWith( actions.setLoading( true ) );
    });

    it( `sends 1 request to auth/current_user`, () => {
      operations.getCurrentUser()( dispatchMock );

      expect( API.get.mock.calls.length ).toBe( 1 );
      expect( API.get.mock.calls[0][0] ).toBe( `/auth/current_user` );
    });

    it( `calls 'dispatch' with 'actions.setUser( response.data )'`, async () => {
      await operations.getCurrentUser()( dispatchMock ).then(() => {

        expect( API.get.mock.calls.length ).toBe( 1 );
        expect( dispatchMock ).toHaveBeenCalledWith( actions.setUser( response ) );
      });
    });
  });

  describe( `.getCurrentUser() - fails`, () => {
    const reason = { message: 'test error message' };
    // before each setup the reject with the reason
    beforeEach(() => API.get.mockRejectedValue( reason ) );
    // after each test reset the mock
    afterEach(() => API.get.mockReset() );

    it( `calls 'dispatch' with 'actions.logout()'`, async () => {
      await operations.getCurrentUser()( dispatchMock ).catch(() => {

        expect( API.get.mock.calls.length ).toBe( 1 );
        expect( dispatchMock ).toHaveBeenCalledWith( actions.logout() );
      });
    });

    it( `rejects with the reason provided`, async () => {
      await operations.getCurrentUser()( dispatchMock ).catch( e => {

        expect( API.get.mock.calls.length ).toBe( 1 );
        expect( e ).toMatchSnapshot();
      });
    });

    it( `does not call 'dispatch' with 'actions.setErrors()'`, async () => {
      await operations.getCurrentUser()( dispatchMock ).catch(() => {

        expect( API.get.mock.calls.length ).toBe( 1 );
        expect( dispatchMock ).not.toHaveBeenCalledWith( actions.setErrors( 'foo' ) );
      });
    });
  });

});
