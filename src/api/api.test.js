import API, { API_URL, headers, parseResponse } from './api';
import fetchMock from 'fetch-mock';
// allow routes to be overridden
fetchMock.config.overwriteRoutes = true;

describe('headers', () => {

  it('returns an object', () => {
    expect( typeof headers() ).toBe( 'object' );
  });

  it('has a key \'Accept\' set to \'application/json\'', () => {
    expect( headers()['Accept'] ).toBeDefined();
    expect( headers()['Accept'] ).toBe('application/json');
  });

  it('has a key \'Content-Type\' set to \'application/json\'', () => {
    expect( headers()['Content-Type'] ).toBeDefined();
    expect( headers()['Content-Type'] ).toBe('application/json');
  });

  it('has a key \'mobile\' set to \'false\'', () => {
    expect( headers()['mobile'] ).toBeDefined();
    expect( headers()['mobile'] ).toBe('false');
  });
});

describe('parseResponse( response )', () => {

  it('calls \'.json()\' on response', () => {
    const response = { json: jest.fn() };
    parseResponse( response );

    expect( response.json ).toHaveBeenCalled();
  });

  it('returns the result of \'response.json\'', () => {
    const response = { json: jest.fn() };
    response.json.mockReturnValue( 'test' );

    expect( parseResponse( response ) ).toBe( 'test' );
  });
});

describe('API (default)', () => {

  describe('.get', () => {

    let mock;

    beforeEach(() => {
      mock = fetchMock.reset().getOnce( '*', { foo: 'bar' } );
    });

    it('sends a fetch request to the `url` paramater', () => {
      return API.get( '/test' ).then( response => {
        expect( mock.called() ).toBe( true );
      });
    });

    it('returns a promise that resolves to the network response', () => {
      expect( API.get( '/test' ) ).resolves.toEqual( { foo: 'bar' }  );
    });

    it('sends the request to API_URL', () => {
      return API.get( '/test' ).then( response => {
        expect( fetchMock.lastUrl() ).toBe( `${API_URL}/test` );
      });
    });
  });

  describe('.post', () => {

    let mock;

    beforeEach(() => {
      mock = fetchMock.reset().postOnce( '*', { foo: 'bar' } );
    });

    it('sends a fetch request to the `url` paramater', () => {
      return API.post( '/test' ).then( response => {
        expect( mock.called() ).toBe( true );
      });
    });

    it('returns a promise that resolves to the network response', () => {
      expect( API.post( '/test' ) ).resolves.toEqual( { foo: 'bar' }  );
    });

    it('sends the request to API_URL', () => {
      return API.post( '/test' ).then( response => {
        expect( fetchMock.lastUrl() ).toBe( `${API_URL}/test` );
      });
    });
  });

  describe('.delete', () => {

    let mock;

    beforeEach(() => {
      mock = fetchMock.reset().deleteOnce( '*', { foo: 'bar' } );
    });

    it('sends a fetch request to the `url` paramater', () => {
      return API.delete( '/test' ).then( response => {
        expect( mock.called() ).toBe( true );
      });
    });

    it('returns a promise that resolves to the network response', () => {
      expect( API.delete( '/test' ) ).resolves.toEqual( { foo: 'bar' }  );
    });

    it('sends the request to API_URL', () => {
      return API.delete( '/test' ).then( response => {
        expect( fetchMock.lastUrl() ).toBe( `${API_URL}/test` );
      });
    });
  });

});