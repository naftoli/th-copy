import API, { API_URL, headers, toJSON } from './api';
import fetchMock from 'fetch-mock';
import Cookies from 'universal-cookie';
// allow routes to be overridden
fetchMock.config.overwriteRoutes = true;
// test
const cookies = new Cookies();

describe('headers', () => {

  it('returns an object', () => {
    expect( typeof headers() ).toBe( 'object' );
  });

  it('has a key \'Accept\' set to \'application/json\'', () => {
    expect( headers()['Accept'] ).toBeDefined();
    expect( headers()['Accept'] ).toBe('application/json');
  });

  it('has a key \'Content-Type\' set to \'application/json; charset=utf-8\'', () => {
    expect( headers()['Content-Type'] ).toBeDefined();
    expect( headers()['Content-Type'] ).toBe('application/json; charset=utf-8');
  });

  it(`has a key 'login' set to 'cookies.get('login')'`, () => {
    cookies.set( 'login', 'abcd' );
    expect( headers()['login'] ).toBeDefined();
    expect( headers()['login'] ).toBe(cookies.get('login'));
    cookies.remove( 'login' );
  });
});

describe('toJSON( response )', () => {

  it('calls \'.json()\' on response', () => {
    const response = { json: jest.fn() };
    toJSON( response );

    expect( response.json ).toHaveBeenCalled();
  });

  it('returns the result of \'response.json\'', () => {
    const response = { json: jest.fn() };
    response.json.mockReturnValue( 'test' );

    expect( toJSON( response ) ).toBe( 'test' );
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