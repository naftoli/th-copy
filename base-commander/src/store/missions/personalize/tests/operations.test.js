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

  describe( `getCampaigns( data )`, () => {
    it( `sends a request to /missions/personalize?action=getCampaigns`, () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.post('*', response );

      operations.getCampaigns( {} )( dispatchMock );

      expect( mock.called() ).toBe( true );
      expect( fetchMock.lastUrl() ).toBe( `${API_URL}/missions/personalize?action=getCampaigns` );
    });

    it( `sends the argument as the request body`, () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.post('*', response );
      const data = { class_id: 5, user_id: 991234 };

      operations.getCampaigns( data )( dispatchMock );

      expect( mock.called() ).toBe( true );
      expect( fetchMock.lastOptions().body ).toEqual( JSON.stringify( data ) );
    });

    it( `dispatches an update to the state when api returns data`, async () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.post('*', response );
      const data = { class_id: 5, user_id: 991234 };

      await operations.getCampaigns( data )( dispatchMock ).then(() => {
        expect( dispatchMock ).toHaveBeenCalledWith( actions.setCampaigns( response.data ) );
      });
    });
  });

  describe( `getTasks( subject_id, data )`, () => {
    it( `sends a request to /missions/personalize?action=getTasks`, () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.post('*', response );

      operations.getTasks( 5, {} )( dispatchMock );

      expect( mock.called() ).toBe( true );
      expect( fetchMock.lastUrl() ).toBe( `${API_URL}/missions/personalize?action=getTasks` );
    });

    it( `sends the subject_id and data arguments combined as the request body`, () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.post('*', response );
      const data = { class_id: 5, user_id: 991234 };

      operations.getTasks( 5, data )( dispatchMock );

      expect( mock.called() ).toBe( true );
      expect( fetchMock.lastOptions().body ).toEqual(
        JSON.stringify( { ...data, subject_id: 5 } )
      );
    });

    it( `dispatches an update to the state when api returns data`, async () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.post('*', response );
      const data = { class_id: 5, user_id: 991234 };

      await operations.getTasks( 5, data )( dispatchMock ).then(() => {
        expect( dispatchMock ).toHaveBeenCalledWith(
          actions.setTasks( 5, response.data )
        );
      });
    });
  });

  describe( `getMissions( subject_id, task, data )`, () => {
    it( `sends a request to /missions/personalize?action=getMissions`, () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.post('*', response );

      operations.getMissions( 5, 'Hello', {} )( dispatchMock );

      expect( mock.called() ).toBe( true );
      expect( fetchMock.lastUrl() ).toBe( `${API_URL}/missions/personalize?action=getMissions` );
    });

    it( `sends the subject_id and data arguments combined as the request body`, () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.post('*', response );
      const data = { class_id: 5, user_id: 991234 };

      operations.getMissions( 5, 'Hello', data )( dispatchMock );

      expect( mock.called() ).toBe( true );
      expect( fetchMock.lastOptions().body ).toEqual(
        JSON.stringify( { ...data, subject_id: 5, task: 'Hello' } )
      );
    });

    it( `dispatches an update to the state when api returns data`, async () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.post('*', response );
      const data = { class_id: 5, user_id: 991234 };

      await operations.getMissions( 5, 'Hello', data )( dispatchMock ).then( () => {
        expect( dispatchMock ).toHaveBeenCalledWith(
          actions.setMissions( 5, 'Hello', response.data )
        );
      });
    });
  });

  describe( `personalize( updates, data )`, () => {
    it( `sends a POST request to /missions/personalize`, () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.post('*', response );

      operations.personalize( {}, {} )( dispatchMock );

      expect( mock.called() ).toBe( true );
      expect( fetchMock.lastUrl() ).toBe( `${API_URL}/missions/personalize` );
    });

    it( `combines updates and data for the post request`, () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.post('*', response );
      const data = { class_id: 5, user_id: 991234 };
      const updates = { subject_id: 14, enrolled: false };

      operations.personalize( updates, data )( dispatchMock );

      expect( mock.called() ).toBe( true );
      expect( fetchMock.lastOptions().body ).toEqual(
        JSON.stringify( { ...data, updates } )
      );
    });

    it( `dispatches an update to the state before making the request to the API`, () => {
      const response = { success: true, data: [ 'a', 'b' ] };
      const mock = fetchMock.post('*', response );
      const data = { class_id: 5, user_id: 991234 };
      const updates = { subject_id: 14, enrolled: false };

      operations.personalize( updates, data )( dispatchMock );

      expect( mock.called() ).toBe( true );
      expect( dispatchMock ).toHaveBeenCalledWith( actions.personalize( updates ) );
    });
  });
})