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

  describe( `getSubjects()`, () => {

    it( `sends a GET request to /rewards/subjects`, () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.get('*', response );

      operations.getSubjects()( dispatchMock );

      expect( mock.called() ).toBe( true );
      expect( fetchMock.lastUrl() ).toBe( `${API_URL}/rewards/subjects` );
    });

    it( `dispatches an update to the state when api returns data`, async () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.get('*', response );
      const data = { class_id: 5, user_id: 991234 };

      await operations.getSubjects( data )( dispatchMock ).then(() => {
        expect( dispatchMock ).toHaveBeenCalledWith( actions.setSubjects( response.data ) );
      });
    });

  });
});