import API from 'api/api';
import * as actions from '../actions';
import * as operations from '../operations';

jest.mock( 'api/api' );

describe(`operations`, () => {
  let dispatchMock;

  beforeEach(() => {
    dispatchMock = jest.fn();
  });

  afterEach(() => {
    API.get.mockReset();
    API.delete.mockReset();
  });

  describe( `.getMiles()`, () => {

    beforeEach(() => {
      API.get.mockResolvedValue({ miles: 50 });
    });

    it( `sends 1 GET request to /rewards/miles`, () => {

      operations.getMiles()( dispatchMock );

      expect( API.get.mock.calls.length ).toBe( 1 );
      expect( API.get.mock.calls[0][0] ).toBe( `/rewards/miles` );
    });

  });

  describe( `.deleteUnusedCards( delete_to )`, () => {

    const delete_to = '2018-12-19T15:02:18-05:00';

    beforeEach(() => {
      API.delete.mockResolvedValue({ miles: 50 });
    });

    it( `sends 1 DELETE request to /rewards/achievement_cards`, () => {
      // call the function
      operations.deleteUnusedCards( delete_to )( dispatchMock );

      expect( API.delete.mock.calls.length ).toBe( 1 );
      expect( API.delete.mock.calls[0][0] ).toBe( `/rewards/achievement_cards` );
    });

    it( `sends the argument as delete_to`, () => {
      // call the function
      operations.deleteUnusedCards( delete_to )( dispatchMock );

      expect( API.delete.mock.calls.length ).toBe( 1 );
      expect( API.delete.mock.calls[0][1] ).toEqual({ delete_to });
    });

  });

});
