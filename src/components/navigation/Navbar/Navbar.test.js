import React from 'react';
import { mount } from 'enzyme';
import { Nav } from 'reactstrap';
import Navbar from './Navbar';
import indexNavbar from './index';

describe('index.js', () => {
  it('exports Navbar component as the default', () => {
    expect( indexNavbar ).toEqual( Navbar );
  });
});

describe("Navbar", () => {
  // BOILERPLATE
  let props, mountedComponent;
  // Component singleton
  const navbar = () => {
    return mountedComponent ? mountedComponent : mountedComponent = mount(
      <Navbar {...props} />
    );
  }
  // clear global variables before each test
  beforeEach(() => {
    props = {};
    mountedComponent = undefined;
  });

  // TESTS
  describe('renders', () => {

    it('a nav with the id of #mashpia-navbar', () => {
      expect( navbar().find('nav#mashpia-navbar').length ).toBe( 1 );
    })

    it('a A tag with the class of .navbar-brand', () => {
      expect( navbar().find('a.navbar-brand').length ).toBe( 1 );
    })

    it('a div with the id of #navbar-title', () => {
      expect( navbar().find('div#navbar-title').length ).toBe( 1 );
    })

    it('a Nav', () => {
      expect( navbar().find( Nav ).length ).toBe( 1 );
    })

  })

  describe('props', () => {

    describe('.title', () => {

      it('has a default value (\'Tzivos Hashem\'', () => {
        expect( navbar().props().title ).toBe( "Tzivos Hashem" );
      })
      
      it('renders as the text of #navbar-title', () => {
        props.title = "Soldiers: View / Edit"
        expect( navbar().find('#navbar-title').text() ).toBe( props.title )
      })

    })

    describe('onClick', () => {
      it('does not have a default value', () => {
        expect( navbar().props().onClick ).toBe( undefined );
      })

      it('is called once when the user presses on .navbar-brand', () => {
        props.onClick = jest.fn(); // mock jest function
        navbar().find( 'a.navbar-brand' ).simulate('click');
        expect( props.onClick ).toHaveBeenCalledTimes( 1 );
      })
    })
  })
})
