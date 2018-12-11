import React from 'react';
import { mount } from 'enzyme';
import Sidebar from '../Sidebar';
import SidebarItem from '../SidebarItem';
import { MemoryRouter } from 'react-router-dom';

describe("Sidebar", () => {
  // BOILERPLATE
  let props, mountedComponent; // global variables
  // sidebar singleton
  const sidebar = () => {
    return mountedComponent ? mountedComponent : mountedComponent = mount(
      <MemoryRouter>
        <Sidebar {...props} />
      </MemoryRouter>
    ).find( Sidebar );
  }
  // clear global variables before each test
  beforeEach(() => {
    props = {};
    mountedComponent = undefined;
  });

  // TESTS
  it("always renders div#sidebar", () => {
    expect( sidebar().find("div#sidebar").length ).toEqual( 1 );
  })

  it("always renders ul.components", () => {
    expect( sidebar().find("ul.components").length ).toEqual( 1 );
  })

  it("has default props", () => {
    expect( sidebar().props().menu ).toEqual( [] )
    expect( sidebar().props().active ).toBe( false )
  })

  it("allows us to set props", () => {
    props.active = true;
    expect( sidebar().props().active ).toBe( true )
  })


  describe( "props.active", () => {
    it("adds the class active to #sidebar when true", () => {
      props.active = true;
      expect( sidebar().find("div#sidebar").hasClass('active') ).toBe( true );
    })

    it("does not add the class active to #sidebar when false", () => {
      props.active = false;
      expect( sidebar().find("div#sidebar").hasClass('active') ).toBe( false );
    })
  })

  describe( "props.menu", () => {
    
    it("renders a SidebarItem with each menu item", () => {
      props.menu = [
        { label: "item 1" },
        { label: "item 2" }
      ]

      expect( sidebar().find( SidebarItem ).length ).toBe( props.menu.length );
    })
  })

})