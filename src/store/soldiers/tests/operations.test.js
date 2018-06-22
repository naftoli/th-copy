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

  describe( `getSoldiers`, () => {

    it( `calls 'dispatch' with 'actions.setLoading(true)'`, () => {
      fetchMock.get('*', { key: `abcd` } );
      operations.getSoldiers()( dispatchMock );
      expect( dispatchMock ).toHaveBeenCalledWith( actions.setLoading( true ) );
    });

    it( `sends a request to core/users.php`, () => {
      const mock = fetchMock.get('*', { key: `abcd` } );
      operations.getSoldiers()( dispatchMock )
      expect( mock.called() ).toBe( true );
      expect( fetchMock.lastUrl() ).toBe( `${API_URL}/core/users.php` );
    });

    it(`calls dispatch 3 times`, () => {
      fetchMock.get('*', { success: false } );
      return operations.getSoldiers()( dispatchMock ).then( () => {
        expect( dispatchMock ).toHaveBeenCalledTimes( 3 );
      });
    });

  });
})