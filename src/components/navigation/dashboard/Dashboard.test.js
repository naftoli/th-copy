import React from 'react';
import { shallow } from 'enzyme';
import { Dashboard } from './Dashboard';
import { MemoryRouter } from 'react-router';
import { Sidebar, Navbar } from 'components/navigation';

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

      it(`defaults to false`, () => {
        expect( dashboard().state().active ).toBe( false );
      });

      it('toggles the sidebar when the Navbar is clicked', () => {
        expect( dashboard().state().active ).toBe( false );
        dashboard().find( Navbar ).simulate('click')
        expect( dashboard().state().active ).toBe( true );
      });

      it('does not toggle the sidebar when the Navbar is clicked and the screen is above 1024px', () => {
        window.innerWidth = 1025;
        dashboard().find( Navbar ).simulate('click');
        expect( dashboard().state().active ).toBe( true );
      });
      
    });
  });

  describe('lifecylce', () => {
    
    let listen, unmount;
    
    beforeEach(() => {
      listen = jest.fn(); unmount = jest.fn();
      listen.mockReturnValue( unmount );
      props.history = { listen: listen };
    });

    describe('componentDidMount', () => {

      it( 'calls props.history.listen', () => {
        dashboard();
        expect( listen ).toHaveBeenCalled();
      });

      it( 'does not call the return value of props.history.listen', () => {
        dashboard();
        expect( unmount ).not.toHaveBeenCalled();
      });

      it( 'recives a function as a paramater', () => {
        dashboard();
        expect( listen ).toBeCalledWith( expect.any( Function ) );
      });
      
      it( 'sets state.active to false when recived function is called and display is 1024px', () => {
        window.innerWidth = 768;
        dashboard(); listen.mock.calls[0][0]();
        expect( dashboard().state().active ).toBe( false );
      });


      it( 'does nothing when recived function is called and display is greater then 1024px', () => {
        window.innerWidth = 1025;
        dashboard(); listen.mock.calls[0][0]();
        expect( dashboard().state().active ).toBe( false );
      });

    });

    describe('componentWillUnmount', () => {

      it( 'the return value of props.history.listen', () => {
        dashboard().unmount();
        expect( unmount ).toHaveBeenCalled();
      });

    });
  });
});