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

  describe( `getTasks()`, () => {

    it( `sends a GET request to /missions/tasks`, () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.get('*', response );

      operations.getTasks()( dispatchMock );

      expect( mock.called() ).toBe( true );
      expect( fetchMock.lastUrl() ).toBe( `${API_URL}/missions/tasks` );
    });

    it( `dispatches a setTasks update to the state when api returns data`, async () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.get('*', response );

      await operations.getTasks()( dispatchMock ).then(() => {
        // expect the API to have been called
        expect( mock.called() ).toBe( true );
        // expect the dispatch to be called with the correct object
        expect( dispatchMock ).toHaveBeenCalledWith( actions.setTasks( response.data ) );
      });
    });
  });

  describe( `createTask( task )`, () => {

    it( `sends a POST request to /missions/tasks`, () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.post('*', response );

      operations.createTask( {} )( dispatchMock );

      expect( mock.called() ).toBe( true );
      expect( fetchMock.lastUrl() ).toBe( `${API_URL}/missions/tasks` );
    });

    it( `sends the argument as the request body`, () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.post('*', response );
      const task = { class_id: 5, user_id: 991234 };

      operations.createTask( task )( dispatchMock );

      expect( mock.called() ).toBe( true );
      expect( fetchMock.lastOptions().body ).toEqual( JSON.stringify( task ) );
    });

    it( `dispatches a addTask update to the state when api returns data`, async () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.post('*', response );
      const task = { class_id: 5, user_id: 991234 };

      await operations.createTask( task )( dispatchMock ).then(() => {
        // expect the API to have been called
        expect( mock.called() ).toBe( true );
        // expect the dispatch to be called with the correct object
        expect( dispatchMock ).toHaveBeenCalledWith( actions.addTask( response.data ) );
      });
    });
  });

  describe( `updateTask( task )`, () => {

    it( `sends a PATCH request to /missions/tasks`, () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.patch('*', response );

      operations.updateTask( {} )( dispatchMock );

      expect( mock.called() ).toBe( true );
      expect( fetchMock.lastUrl() ).toBe( `${API_URL}/missions/tasks` );
    });

    it( `sends the argument as the request body`, () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.patch('*', response );
      const task = { class_id: 5, user_id: 991234 };

      operations.updateTask( task )( dispatchMock );

      expect( mock.called() ).toBe( true );
      expect( fetchMock.lastOptions().body ).toEqual( JSON.stringify( task ) );
    });

    it( `dispatches a actions.updateTask update to the state when api returns data`, async () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.patch('*', response );
      const task = { class_id: 5, user_id: 991234 };

      await operations.updateTask( task )( dispatchMock ).then(() => {
        // expect the API to have been called
        expect( mock.called() ).toBe( true );
        // expect the dispatch to be called with the correct object
        expect( dispatchMock ).toHaveBeenCalledWith( actions.updateTask( response.data ) );
      });
    });
  });

});
