import React from 'react';
import { mount } from 'enzyme';
import { MemoryRouter, Redirect } from 'react-router-dom';
import { Logout } from './Logout'; // import the compenent not connected to the stores

describe( 'Logout', () => {
  // BOILERPLATE
  let props, mountedComponent;
  const error = console.error;
  // Component singleton
  const login = () => {
    return mountedComponent ? mountedComponent : mountedComponent = mount(
      <MemoryRouter initialEntries={['/test']} initialIndex={2}>
        <Logout {...props} />
      </MemoryRouter>
    );
  }
  // clear global variables before each test
  beforeEach(() => {
    props = { logout: jest.fn() };
    mountedComponent = undefined;
    console.error = jest.fn();
  });

  afterEach(() => {
    console.error = error;
  })

  // TESTS
  it('calls props.logout when rendered', () => {
    login();
    expect( props.logout ).toHaveBeenCalled();
  });

  it('returns a redirect', () => {
    expect( login().find( Redirect ).length ).toBe( 1 );
  });
});
