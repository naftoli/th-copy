import React from 'react';
import { shallow } from 'enzyme';
import Dashboard from './Dashboard';
import { MemoryRouter } from 'react-router';
import Sidebar from 'components/navigation/Sidebar';
import Navbar from 'components/navigation/Navbar/Navbar';

describe("Dashboard", () => {
  // BOILERPLATE
  let props, mountedComponent, initialWidth, children;
  // Component singleton
  const dashboard = () => {
    return mountedComponent ? mountedComponent : mountedComponent = shallow(
      <MemoryRouter>
        <Dashboard {...props}>{ children }</Dashboard>
      </MemoryRouter>
    ).find( Dashboard ).dive();
  }
  // clear global variables before each test
  beforeEach(() => {
    props = {};
    children = undefined;
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
      children = <Sample />;
      expect( dashboard().find( Sample ).length ).toBe( 1 );
    });
  });

  describe('state', () => {
    
    describe('.active', () => {

      it(`defaults to false if device width is <= 768px`, () => {
        window.innerWidth = 768;
        expect( dashboard().state().active ).toBe( false );
      });

      it(`defaults to true if device width is > 768px`, () => {
        expect( dashboard().state().active ).toBe( true );
      });

      it('toggles the sidebar when the Navbar is clicked', () => {
        dashboard().find( Navbar ).simulate('click')
        expect( dashboard().state().active ).toBe( false )
      });

      it('does not toggle the sidebar when the Navbar is clicked and the screen is above 1024px', () => {
        window.innerWidth = 1025;
        dashboard().find( Navbar ).simulate('click');
        expect( dashboard().state().active ).toBe( true );
      });
      
    });
  });
});