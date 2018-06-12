import React from 'react';
import { mount } from 'enzyme';
import { MemoryRouter } from 'react-router';
import { NavLink } from 'react-router-dom';
import SidebarItem from '../SidebarItem';
import SidebarDropdown from '../SidebarDropdown';

describe("SidebarItem", () => {
  // BOILERPLATE
  let props, mountedComponent;
  // Component singleton
  const sidebarItem = () => {
    return mountedComponent ? mountedComponent : mountedComponent = mount(
      <MemoryRouter>
        <SidebarItem {...props} />
      </MemoryRouter>
    ).find( SidebarItem );
  }
  // clear global variables before each test
  beforeEach(() => {
    props = {};
    mountedComponent = undefined;
  });

  // TESTS
  describe('renders - items', () => {

    it('renders a SidebarDropdown', () => {
      props.items = [];
      expect( sidebarItem().find( SidebarDropdown ).length ).toBe( 1 );
    })

  })

  describe('renders - legacy link', () => {
    
    beforeEach(() => {
      props = { label: 'Item 1', legacy: true, path: '/test' }
    });

    it('an LI tag', () => {
      expect( sidebarItem().find('li').length ).toBe( 1 );
    });

    it('an A tag', () => {
      expect( sidebarItem().find('a').length ).toBe( 1 );
    });

  })

  // TODO when react router is setup
  describe('renders - internal (react) link', () => {

    beforeEach(() => {
      props = { label: 'Item 1', path: '/' }
    });

    it('an LI tag', () => {
      expect( sidebarItem().find('li').length ).toBe( 1 );
    })

    it('a NavLink element', () => {
      expect( sidebarItem().find( NavLink ).length ).toBe( 1 );
    });

  })

  describe('props', () => {

    describe('.label', () => {

      it('has a default value (\'\')', () => {
        expect( sidebarItem().props().label ).toBe( '' );
      })

      it('renders as the elements text', () => {
        props.label = 'Item 1';
        expect( sidebarItem().text().trim() ).toBe( props.label );
      })
    
    })

    describe('.icon', () => {
      
      it('has a default value (false)', () => {
        expect( sidebarItem().props().icon ).toBe( false );
      })

      it('renders the provided element in the A tag', () => {
        props.icon = <i id='test-icon' />;
        expect( sidebarItem().find("a > #test-icon").length ).toBe( 1 );
      })

    })

    describe('.items', () => {

      it('has a default value (false)', () => {
        expect( sidebarItem().props().items ).toBe( false );
      })

      it('renders a SidebarDropdown with all it\'s props when this prop is valid', () => {
        props.items = [ { label: "Item 2.1" } ];
        props.test = "fake prop";

        expect( sidebarItem().find( SidebarDropdown ).length ).toBe( 1 );
        expect( sidebarItem().find( SidebarDropdown ).props().items ).toBe( props.items );
        expect( sidebarItem().find( SidebarDropdown ).props().test ).toBe( props.test );
      })

      it('does not render a SidebarDropdown when this prop is not valid', () => {
        expect( sidebarItem().find( SidebarDropdown ).length ).toBe( 0 );
      })

    })

    describe('.legacy', () => {
      it('has a default value(false)', () => {
        expect( sidebarItem().props().legacy ).toBe( false );
      })

      // xit('renders a NavLink tag when set to false');
      // xit('renders an A tag when set to true');
    })

    describe('.path', () => {
      it('has a default value (#)', () => {
        expect( sidebarItem().props().path ).toBe( '#' );
      })
    })
  })
})