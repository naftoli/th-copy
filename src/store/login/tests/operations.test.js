import { API_URL } from 'api/api';
import fetchMock from 'fetch-mock';
import * as actions from '../actions';
import * as operations from '../operations';

describe(`operations`, () => {
  let dispatchMock, postMock, getMock;

  const response = { success: true, data: {
    legacy: '5',
    mobile: '5',
    id: '5'
  }}

  beforeEach(() => {
    dispatchMock = jest.fn();

    postMock = fetchMock.post('*', response );
    getMock = fetchMock.get('*', { success: false } );
  });

  afterEach(() => {
    fetchMock.restore();
  });

  describe( `.login( opts )`, () => {

    it( `calls 'dispatch' with 'actions.setLoading(true)'`, () => {
      
      const data = { username: 5, password: 991234 };

      operations.login( data )( dispatchMock );

      expect( dispatchMock ).toHaveBeenCalledWith( actions.setLoading( true ) );
    });

    it( `sends a request to auth/login.php`, () => {
      const data = { username: 5, password: 991234 };

      operations.login( data )( dispatchMock );

      expect( postMock.called() ).toBe( true );
      expect( fetchMock.lastUrl() ).toBe( `${API_URL}/auth/login.php` );
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
});
