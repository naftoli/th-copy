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
      expect( fetchMock.lastUrl() ).toBe( `${API_URL}/core/users` );
    });

    it(`calls dispatch 2 times`, () => {
      fetchMock.get('*', { success: true } );
      return operations.getSoldiers()( dispatchMock ).then( () => {
        expect( dispatchMock ).toHaveBeenCalledTimes( 2 );
      });
    });
  });

  describe( `updateMissions( user_id, subjects )`, () => {
    it( `sends a POST request to /core/users?action=updateMissions`, () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.post('*', response );

      operations.updateMissions( {} )( dispatchMock );

      expect( mock.called() ).toBe( true );
      expect( fetchMock.lastUrl() ).toBe( `${API_URL}/core/users?action=updateMissions` );
    });

    it( `sends the argument as the request body`, () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.post('*', response );
      const data = { user_id: 991234, subjects: [ 'a', 'b', 'c' ] };

      operations.updateMissions( data.user_id, data.subjects )( dispatchMock );

      expect( mock.called() ).toBe( true );
      expect( fetchMock.lastOptions().body ).toEqual( JSON.stringify( data ) );
    });
  });

})