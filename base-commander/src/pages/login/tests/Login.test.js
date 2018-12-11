import React from 'react';
import { mount } from 'enzyme';
import { Login } from '../Login';
import { Password } from 'components/inputs';
import { MemoryRouter } from 'react-router'; // import the compenent not connected to the store


describe( 'Login', () => {
  // BOILERPLATE
  let props, mountedComponent;
  // Component singleton
  const login = () => {
    return mountedComponent ? mountedComponent : mountedComponent = mount(
      <MemoryRouter>
        <Login {...props} />
      </MemoryRouter>
    ).find( Login );
  }
  // clear global variables before each test
  beforeEach(() => {
    props = {};
    mountedComponent = undefined;
  });

   // TESTS
  describe('renders', () => {
    
    it(`renders one div#login-form`, () => {
      expect( login().find('div#login-form').length ).toBe( 1 );
    });

    it(`renders one input[name='username']`, () => {
      expect( login().find(`input[name='username']`).length ).toBe( 1 );
    });

    it(`renders one one Password Component`, () => {
      expect( login().find( Password ).length ).toBe( 1 );
    });

  });

  describe('state', () => {

    it(`updates state.username when text is entered into input[name='username']`, () => {
      const usernameInput = login().find(`input[name='username']`);
      usernameInput.instance().value = 'test';
      usernameInput.simulate('change');
      expect( login().state().username ).toBe('test');
    });

    it(`updates state.password when text is entered into input[name='password']`, () => {
      const passwordInput = login().find(`input[name='password']`);
      passwordInput.instance().value = 'test';
      passwordInput.simulate('change');
      expect( login().state().password ).toBe('test');
    });

  });

  describe('props', () => {

    describe( '.login', () => {
      
      beforeEach(() => {
        props = { login: jest.fn() };
        props.login.mockRejectedValue({ message: 'server does not exist' });

        login().find(`input[name='username']`).instance().value = 'username';
        login().find(`input[name='username']`).simulate('change');
        login().find(`input[name='password']`).instance().value = 'password';
        login().find(`input[name='password']`).simulate('change');
        login().find('form').simulate('submit');
      });

      it('calls props.login on submit', () => {
        expect( props.login ).toHaveBeenCalled();
      });

      it('calls props.login with { username password }', () => {
        expect( props.login ).toHaveBeenCalledWith({ username: 'username', password: 'password' });
      });
    });
  });
});
