import React from 'react';
import { mount } from 'enzyme';
import SidebarDropdown from './SidebarDropdown';
import SidebarItem from './SidebarItem';
import { Collapse } from 'reactstrap';

describe("SidebarDropdown", () => {
  // BOILERPLATE
  let props, mountedComponent;
  // Component singleton
  const sidebarDropdown = () => {
    return mountedComponent ? mountedComponent : mountedComponent = mount(
      <SidebarDropdown {...props} />
    );
  }
  // clear global variables before each test
  beforeEach(() => {
    props = {};
    mountedComponent = undefined;
  });

  // TESTS
  describe("renders", () => {

    it("an LI element as the root DOM element", () =>{
      expect( sidebarDropdown().getDOMNode().tagName ).toBe( "LI" );
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

    describe("w.children", () => {

      it("has default value ([])", () => {
        expect( sidebarDropdown().props().children ).toEqual( [] );
      })

      it("renders a SidebarItem for each child", () => {
        props.children = [
          { label: 'Item 1.1 '},
          { label: 'Item 1.2 '}
        ]
        expect( sidebarDropdown().find(SidebarItem).length ).toEqual( props.children.length );
      })

    })

    describe(".label", () => {
      
      it("has default value ('')", () => {
        expect( sidebarDropdown().props().label ).toEqual( "" );
      })

      it("accepts new values", () => {
        props.label = "Dropdown 1";
        expect( sidebarDropdown().props().label ).toEqual( props.label );
      })

      it("renders value in A tag", () => {
        props.label = "Dropdown 1"
        expect( sidebarDropdown().find('a').text() ).toBe( props.label );
      })

    })

    describe(".icon", () => {
      
      it("has default value (false)", () => {
        expect( sidebarDropdown().props().icon ).toBe( false );
      })

      it("accepts boolean values", () => {
        props.icon = true;
        expect( sidebarDropdown().props().icon ).toBe( true );
      })

      it("accepts and renders element values", () => {
        props.icon = <i id="test-icon" />
        expect( sidebarDropdown().find("#test-icon").length ).toBe( 1 );
      })

    })

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