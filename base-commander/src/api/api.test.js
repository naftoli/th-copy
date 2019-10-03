import API, { API_URL, headers, toJSON, parseResponse } from './api';
import fetchMock from 'fetch-mock';
import Cookies from 'universal-cookie';
// allow routes to be overridden
fetchMock.config.overwriteRoutes = true;
// test
const cookies = new Cookies();

describe( `headers`, () => {

  it( `returns an object`, () => {
    expect( typeof headers() ).toBe( 'object' );
  });

  it( `has a key \'Accept\' set to \'application/json\'`, () => {
    expect( headers()['Accept'] ).toBeDefined();
    expect( headers()['Accept'] ).toBe('application/json');
  });

  it( `has a key \'Content-Type\' set to \'application/json; charset=utf-8\'`, () => {
    expect( headers()['Content-Type'] ).toBeDefined();
    expect( headers()['Content-Type'] ).toBe( 'application/json; charset=utf-8' );
  });

  it( `has a key 'login' set to 'cookies.get('login')'`, () => {
    cookies.set( 'login', 'abcd' );
    expect( headers()['login'] ).toBeDefined();
    expect( headers()['login'] ).toBe(cookies.get('login'));
    cookies.remove( 'login' );
  });
});

// describe( `toJSON( response )`, () => {

//   it( `calls \'.text()\' on response`, () => {
//     const response = { text: jest.fn( () => Promise.resolve( `{"foo":"bar"}` ) ) };
//     toJSON( response );

//     expect( response.text ).toHaveBeenCalled();
//   });

//   it( `resolves with the response parsed from json`, () => {
//     const response = { text: jest.fn( () => Promise.resolve( `{"foo":"bar"}` ) ) };
//     expect( toJSON( response ) ).resolves.toEqual( { foo: 'bar' } );
//   })

//   it( `rejects the promise if the response is not valid JSON`, () => {
//     const response = { text: jest.fn( () => Promise.resolve( 'hi' ) ) };
//     expect( toJSON( response ) ).rejects.toEqual( new Error('hi') );
//   });

//   it( `rejects the promise if the response is not valid JSON with HTML tags striped out`, () => {
//     const response = { text: jest.fn( () => Promise.resolve( '<b>hi</b>' ) ) };
//     expect( toJSON( response ) ).rejects.toEqual( new Error('hi') );
//   });
// });

describe( `parseResponse`, () => {
  it( `returns response.data if response.success is true`, () => {
    const res = { success: true, data: 'bla' };
    expect( parseResponse( res ) ).toEqual( res.data );
  });

  it( `rejects response if response.success is false and message is present`, () => {
    const res = { success: false, message: 'hi', data: 'bla' };
    expect( Promise.resolve( parseResponse( res ) ) ).rejects.toEqual( res );
  });
});

describe( `API (default)`, () => {

  describe( `.get`, () => {

    let mock;

    beforeEach(() => {
      mock = fetchMock.reset().getOnce( '*', { data: { foo: 'bar' }, success: true });
    });

    it( `sends a fetch request to the 'url' paramater`, () => {
      return API.get( '/test' ).then( response => {
        expect( mock.called() ).toBe( true );
      });
    });

    it( `returns a promise that resolves to the network response`, () => {
      expect( API.get( '/test' ) ).resolves.toEqual( { foo: 'bar' }  );
    });

    it( `sends the request to API_URL`, () => {
      return API.get( '/test' ).then( response => {
        expect( fetchMock.lastUrl() ).toBe( `${API_URL}/test` );
      });
    });
  });

  describe( `.post`, () => {

    let mock;

    beforeEach(() => {
      mock = fetchMock.reset().postOnce( '*', { data: { foo: 'bar' }, success: true } );
    });

    it( `sends a fetch request to the 'url' paramater`, () => {
      return API.post( '/test' ).then( response => {
        expect( mock.called() ).toBe( true );
      });
    });

    it( `returns a promise that resolves to the network response`, () => {
      expect( API.post( '/test' ) ).resolves.toEqual( { foo: 'bar' }  );
    });

    it( `sends the request to API_URL`, () => {
      return API.post( '/test' ).then( response => {
        expect( fetchMock.lastUrl() ).toBe( `${API_URL}/test` );
      });
    });
  });

  describe( `.delete`, () => {

    let mock;

    beforeEach(() => {
      mock = fetchMock.reset().deleteOnce( '*', { data: { foo: 'bar' }, success: true } );
    });

    it( `sends a fetch request to the 'url' paramater`, () => {
      return API.delete( '/test' ).then( response => {
        expect( mock.called() ).toBe( true );
      });
    });

    it( `returns a promise that resolves to the network response`, () => {
      expect( API.delete( '/test' ) ).resolves.toEqual( { foo: 'bar' }  );
    });

    it( `sends the request to API_URL`, () => {
      return API.delete( '/test' ).then( response => {
        expect( fetchMock.lastUrl() ).toBe( `${API_URL}/test` );
      });
    });
  });
});
