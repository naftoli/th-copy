import React from 'react';
import { shallow } from 'enzyme';
import { MemoryRouter } from 'react-router';
import SidebarDropdown from '../SidebarDropdown';
import SidebarItem from '../SidebarItem';
import { Collapse } from 'reactstrap';

describe("SidebarDropdown", () => {
  // BOILERPLATE
  let props, mountedComponent;
  // Component singleton
  const sidebarDropdown = () => {
    return mountedComponent ? mountedComponent : mountedComponent = shallow(
      <MemoryRouter>
        <SidebarDropdown {...props} />
      </MemoryRouter>
    ).find(SidebarDropdown).dive();
  }
  // clear global variables before each test
  beforeEach(() => {
    props = {};
    mountedComponent = undefined;
  });

  // TESTS
  describe("renders", () => {

    it("an LI element as the root DOM element", () =>{
      expect( sidebarDropdown().find('li').length ).toBe( 1 );
    })
  
    it("an A tag", () => {
      expect( sidebarDropdown().find('a').length ).toBe( 1 );
    })

    it("a Collapse element", () => {
      expect( sidebarDropdown().find( Collapse ).length ).toBe( 1 );
    })

    it("a ul with child SidebarItems inside", () => {
      expect( sidebarDropdown().find('ul').length ).toBe( 1 );
    })

  })

  describe("props", () => {

    describe(".items", () => {

      it("has default value ([])", () => {
        expect( SidebarDropdown.defaultProps.items ).toEqual( [] );
      })

      it("renders a SidebarItem for each child", () => {
        props.items = [
          { label: 'Item 1.1 '},
          { label: 'Item 1.2 '}
        ]
        expect( sidebarDropdown().find(SidebarItem).length ).toEqual( props.items.length );
      })

    })

    describe(".label", () => {
      
      it("has default value ('')", () => {
        expect( SidebarDropdown.defaultProps.label ).toEqual( "" );
      });

      it("renders value in A tag", () => {
        props.label = "Dropdown 1"
        expect( sidebarDropdown().find('a').text() ).toBe( props.label );
      });

    })

    describe(".icon", () => {
      
      it("has default value (false)", () => {
        expect( SidebarDropdown.defaultProps.icon ).toBe( false );
      });
    });
    
  })

  describe("state", () => {

    describe(".collapse", () => {

      it("has a default value (false)", () => {
        expect( sidebarDropdown().state('collapse') ).toBe( false );
      })

      it("toggles when A tag is clicked", () => {
        sidebarDropdown().find('a').simulate('click');
        expect( sidebarDropdown().state('collapse') ).toBe( true );

        sidebarDropdown().find('a').simulate('click');
        expect( sidebarDropdown().state('collapse') ).toBe( false );
      })

      it("updates the isOpen prop on the Collapse element", () => {
        expect( sidebarDropdown().find( Collapse ).props().isOpen ).toBe( false );

        sidebarDropdown().find('a').simulate('click');
        expect( sidebarDropdown().find( Collapse ).props().isOpen ).toBe( true );
      })

    })

  })
})