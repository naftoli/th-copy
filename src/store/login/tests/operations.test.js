import { API_URL } from 'api/api';
import fetchMock from 'fetch-mock';
import * as actions from '../actions';
import * as operations from '../operations';

describe(`operations`, () => {
  let dispatchMock;

  beforeEach(() => {
    dispatchMock = jest.fn();
  });

  afterEach(() => {
    fetchMock.restore();
  });

  describe( `.login( opts )`, () => {
    let postMock, getMock;

    const response = { success: true, data: {
      legacy: '5',  mobile: '5',  id: '5'
    }}

    beforeEach(() => {
      postMock = fetchMock.post('*', response );
      getMock = fetchMock.get('*', { success: false } );
    });

    it( `calls 'dispatch' with 'actions.setLoading(true)'`, () => {
      
      const data = { username: 5, password: 991234 };

      operations.login( data )( dispatchMock );

      expect( dispatchMock ).toHaveBeenCalledWith( actions.setLoading( true ) );
    });

    it( `sends a request to auth/login`, () => {
      const data = { username: 5, password: 991234 };

      operations.login( data )( dispatchMock );

      expect( postMock.called() ).toBe( true );
      expect( fetchMock.lastUrl() ).toBe( `${API_URL}/auth/login` );
    });

    it( `sends the argument as the request body`, () => {
      const data = { username: 5, password: 991234 };

      operations.login( data )( dispatchMock );

      expect( postMock.called() ).toBe( true );
      expect( fetchMock.lastOptions().body ).toEqual( JSON.stringify( data ) );
    });

    it( `sets the tokens and calls getCurrentUser`, async () => {
      const data = { class_id: 5, user_id: 991234 };

      await operations.login( data )( dispatchMock ).then(() => {

        expect( postMock.called() ).toBe( true );
        expect( getMock.called() ).toBe( true );

        const { legacy, mobile, id } = response.data;
        let tokenSubject = actions.setTokens( legacy, mobile, id );

        expect( dispatchMock ).toHaveBeenCalledWith( tokenSubject );
      });
    });
  });

  describe( `.getCurrentUser()`, () => {
    let getMock;

    const response = { success: true, data: {
      username: 'foo', password: 'bar'
    }}

    beforeEach(() => {
      getMock = fetchMock.get('*', response );
    });

    it( `calls 'dispatch' with 'actions.setLoading(true)'`, () => {
      operations.getCurrentUser()( dispatchMock );

      expect( dispatchMock ).toHaveBeenCalledWith( actions.setLoading( true ) );
    });

    it( `sends a request to auth/current_user`, () => {
      operations.getCurrentUser()( dispatchMock );

      expect( getMock.called() ).toBe( true );
      expect( fetchMock.lastUrl() ).toBe( `${API_URL}/auth/current_user` );
    });

    it( `calls 'dispatch' with 'actions.setUser( response.data )'`, async () => {
      await operations.getCurrentUser()( dispatchMock ).then(() => {
        expect( getMock.called() ).toBe( true );
        expect( dispatchMock ).toHaveBeenCalledWith( actions.setUser( response.data ) );
      });
    });
  });

  describe( `.getCurrentUser() - fails`, () => {
    let getMock;

    const response = { success: false }

    it( `calls 'dispatch' with 'actions.logout()'`, async () => {
      const getMock = fetchMock.get('*', { success: false } );

      await operations.getCurrentUser()( dispatchMock ).catch(() => {
        expect( getMock.called() ).toBe( true );
        expect( dispatchMock ).toHaveBeenCalledWith( actions.logout() );
      });
    });

    it( `rejects with the reason`, async () => {
      const getMock = fetchMock.get('*', { success: false } );

      await operations.getCurrentUser()( dispatchMock ).catch( e => {
        expect( getMock.called() ).toBe( true );
        expect( e ).toMatchSnapshot();
      });
    });

    describe( `calls 'dispatch' with 'actions.setErrors()'`, () => {
      it( `uses response.message when present`, async () => {
        const getMock = fetchMock.get('*', { success: false, message: 'foo' } );

        await operations.getCurrentUser()( dispatchMock ).catch(() => {
          expect( getMock.called() ).toBe( true );
          expect( dispatchMock ).toHaveBeenCalledWith( actions.setErrors( 'foo' ) );
        });
      });
    });
  });

});
