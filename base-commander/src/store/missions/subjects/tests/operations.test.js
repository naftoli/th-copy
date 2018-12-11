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

    it( `sends a GET request to /missions/subjects`, () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.get('*', response );

      operations.getSubjects()( dispatchMock );

      expect( mock.called() ).toBe( true );
      expect( fetchMock.lastUrl() ).toBe( `${API_URL}/missions/subjects` );
    });

    it( `dispatches a setSubjects update to the state when api returns data`, async () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.get('*', response );

      await operations.getSubjects()( dispatchMock ).then(() => {
        // expect the API to have been called
        expect( mock.called() ).toBe( true );
        // expect the dispatch to be called with the correct object
        expect( dispatchMock ).toHaveBeenCalledWith( actions.setSubjects( response.data ) );
      });
    });

  });

  describe( `getLabels()`, () => {

    it( `sends a GET request to /missions/labels`, () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.get('*', response );

      operations.getLabels()( dispatchMock );

      expect( mock.called() ).toBe( true );
      expect( fetchMock.lastUrl() ).toBe( `${API_URL}/missions/labels` );
    });

    it( `dispatches a setLabels update to the state when api returns data`, async () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.get('*', response );

      await operations.getLabels()( dispatchMock ).then(() => {
        // expect the API to have been called
        expect( mock.called() ).toBe( true );
        // expect the correct dispatch
        expect( dispatchMock ).toHaveBeenCalledWith( actions.setLabels( response.data ) );
      });
    });

  });
});