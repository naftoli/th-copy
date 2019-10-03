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

  describe( `getParshos()`, () => {
    it( `sends a request to /missions/parshos`, () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.get('*', response );

      operations.getParshos()( dispatchMock );

      expect( mock.called() ).toBe( true );
      expect( fetchMock.lastUrl() ).toBe( `${API_URL}/missions/parshos` );
    });

    it( `dispatches an update to the state when api returns data`, async () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.get('*', response );
      const data = { class_id: 5, user_id: 991234 };

      await operations.getParshos( data )( dispatchMock ).then(() => {
        expect( dispatchMock ).toHaveBeenCalledWith( actions.setParshos( response.data ) );
      });
    });
  });
})