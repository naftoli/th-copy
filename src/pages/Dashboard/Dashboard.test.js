import React from 'react';
import { mount } from 'enzyme';
import Dashboard from './Dashboard';

import Sidebar from 'components/navigation/Sidebar';

describe("Dashboard", () => {
  // BOILERPLATE
  let props, mountedComponent, initialWidth;
  // Component singleton
  const dashboard = () => {
    return mountedComponent ? mountedComponent : mountedComponent = mount(
      <Dashboard {...props} />
    );
  }
  // clear global variables before each test
  beforeEach(() => {
    props = {};
    mountedComponent = undefined;
    initialWidth = window.innerWidth;
  });

  afterEach(() => {
    window.innerWidth = initialWidth;
  });

  // TESTS
  describe('renders', () => {

    it(`renders one div with the id #dashboard`, () => {
      expect( dashboard().find('#dashboard').length ).toBe( 1 );
    });

    it(`renders one div with the id #dashboard-body`, () => {
      expect( dashboard().find('#dashboard-body').length ).toBe( 1 );
    });

    it(`renders one div with the id #dashboard-content`, () => {
      expect( dashboard().find('#dashboard-content').length ).toBe( 1 );
    });

    it(`renders one Sidebar component`, () => {
      expect( dashboard().find( Sidebar ).length ).toBe( 1 );
    });

    it(`renders it's children`, () => {
      const Sample = () => <div id="sample"></div>;
      const tmp_dashboard = mount( <Dashboard><Sample/></Dashboard> );
      expect( tmp_dashboard.find( Sample ).length ).toBe( 1 );
    });
  });

  describe('state', () => {
    
    describe('.active', () => {

      it(`defaults to false if device width is <= 768px`, () => {
        window.innerWidth = 768;
        expect( dashboard().state().active ).toBe( false );
      });

      it(`defaults to false if device width is > 768px`, () => {
        expect( dashboard().state().active ).toBe( true );
      });

      it(`toggles when a user presses the sidebar ('.navbar-brand')`, () => {
        expect( dashboard().state().active ).toBe( true );
        dashboard().find( '.navbar-brand' ).simulate('click');
        expect( dashboard().state().active ).toBe( false );
      });

      it(`does not toggle if the device width is > 1024px`, () => {
        window.innerWidth = 1080;
        expect( dashboard().state().active ).toBe( true );
        dashboard().find( '.navbar-brand' ).simulate('click');
        expect( dashboard().state().active ).toBe( true );
      });
    })
  });
})