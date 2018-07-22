import checkLogin from '../checkLogin';
import Cookies from 'universal-cookie';
import fetchMock from 'fetch-mock';
const cookies = new Cookies();

xdescribe( 'checkLogin', () => {
  let dispatchMock;
  beforeEach(() => {
    dispatchMock = jest.fn();
    fetchMock.get( '*' , { success: false } );
  });
  afterEach(() => {
    cookies.remove('admin_auth');
    cookies.remove('admin');
    fetchMock.restore();
  })

  it(`calls dispatch if it finds a legacy token`, () => {
    cookies.set('admin_auth', 'legacy');
    checkLogin( dispatchMock );
    expect( dispatchMock ).toHaveBeenCalled();
  });

  it(`does not call dispatch if it cannot find a legacy token`, () => {
    checkLogin(dispatchMock);
    expect( dispatchMock ).not.toHaveBeenCalled();
  });

  it(`calls dispatch with a type of 'login/set_tokens'`, () => {
    cookies.set('admin_auth', 'legacy');
    checkLogin(dispatchMock);
    expect( dispatchMock.mock.calls[0][0].type ).toBe( 'login/set_tokens' );
  });

  it(`recives the legacy token in the payload`, () => {
    cookies.set('admin_auth', 'legacy');
    checkLogin(dispatchMock);
    expect( dispatchMock.mock.calls[0][0].payload.legacy ).toBe( 'legacy' );
  });


  it(`recives the mobile token in the payload if present`, () => {
    cookies.set('admin_auth', 'legacy');
    cookies.set('admin', 'mobile');
    checkLogin(dispatchMock);
    expect( dispatchMock.mock.calls[0][0].payload.legacy ).toBe( 'legacy' );
  });
});