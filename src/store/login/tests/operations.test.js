import * as actions from '../actions';
import * as operations from '../operations';
import { API_URL } from 'api/api';
import fetchMock from 'fetch-mock';

describe(`operations`, () => {
  let dispatchMock;

  beforeEach(() => {
    dispatchMock = jest.fn();
  });

  afterEach(() => { fetchMock.restore(); });

  describe( `.login`, () => {

    it( `calls 'dispatch' with 'actions.setLoading(true)'`, () => {
      fetchMock.post('*', { key: `abcd` } );
      operations.login( 'test', 'test' )( dispatchMock );
      expect( dispatchMock ).toHaveBeenCalledWith( actions.setLoading( true ) );
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