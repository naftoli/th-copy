import React from 'react';
import { mount } from 'enzyme';
import { Login } from './Login'; // import the compenent not connected to the store

describe( 'Login', () => {
  // BOILERPLATE
  let props, mountedComponent;
  // Component singleton
  const login = () => {
    return mountedComponent ? mountedComponent : mountedComponent = mount(
      <Login {...props} />
    );
  }
  // clear global variables before each test
  beforeEach(() => {
    props = {};
    mountedComponent = undefined;
  });

   // TESTS
  describe('renders', () => {
    
    it(`renders in one #login-page`, () => {
      expect( login().find('#login-page').length ).toBe( 1 );
    });

    it(`renders one form#login-form`, () => {
      expect( login().find('form#login-form').length ).toBe( 1 );
    });

    it(`renders one input#username`, () => {
      expect( login().find('input#username').length ).toBe( 1 );
    });

    it(`renders one one input#password`, () => {
      expect( login().find('input#password').length ).toBe( 1 );
    });

  });

  describe('state', () => {

    it('updates state.username when text is entered into input#username', () => {
      const usernameInput = login().find('input#username');
      usernameInput.instance().value = 'test';
      usernameInput.simulate('change');
      expect( login().state().username ).toBe('test');
    });

    it('updates state.password when text is entered into input#password', () => {
      const passwordInput = login().find('input#password');
      passwordInput.instance().value = 'test';
      passwordInput.simulate('change');
      expect( login().state().password ).toBe('test');
    });

    it('updates state.show_password when button#toggle-password is clicked', () => {
      expect( login().state().show_password ).toBe( false );
      login().find('button#toggle-password').simulate('click');
      expect( login().state().show_password ).toBe( true );
    });

  });

  describe('props', () => {
    describe( '.login', () => {
      beforeEach(() => {
        props = { login: jest.fn() };
        login().find('input#username').instance().value = 'username';
        login().find('input#username').simulate('change');
        login().find('input#password').instance().value = 'password';
        login().find('input#password').simulate('change');
        login().find('form#login-form').simulate('submit');
      });

      it('calls props.login on submit', () => {
        expect( props.login ).toHaveBeenCalled();
      });

      it('calls props.login with the username and password', () => {
        expect( props.login ).toHaveBeenCalledWith( 'username', 'password' );
      });
      
    });
  });
})