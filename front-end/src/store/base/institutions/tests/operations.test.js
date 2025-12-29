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
    API.post.mockReset();
  });

  describe( `.getInstitutions( opts )`, () => {

    const response = [{ foo: 'bar' }];

    beforeEach(() => {
      API.get.mockResolvedValue( response );
    });

    it( `calls 'dispatch' with 'actions.setLoading( true )'`, () => {
      operations.getInstitutions()( dispatchMock );

      expect( dispatchMock ).toHaveBeenCalledWith( actions.setLoading( true ) );
    });

    it( `sends 1 request to auth/login`, () => {
      operations.getInstitutions()( dispatchMock );

      expect( API.get.mock.calls.length ).toBe( 1 );
      expect( API.get.mock.calls[0][0] ).toBe( `/core/institutions` );
    });

    it( `calls 'dispatch' with 'actions.setInstitutions( response )'`, async () => {
      await operations.getInstitutions()( dispatchMock );

      expect( dispatchMock ).toHaveBeenCalledWith( actions.setInstitutions( response ) );
    });

  });

});
