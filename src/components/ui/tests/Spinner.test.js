import React from 'react';
import Spinner from '../loading';
import { mount } from 'enzyme';

describe('Spinner', () => {
  let mountedSpinner, props;
  const spinner = () => {
    return mountedSpinner ? mountedSpinner : mount( <Spinner { ...props }/> );
  }
  beforeEach(() => {
    mountedSpinner = undefined; props = {}
  });

  it(`renders a div with the class 'spinner-1'`, () => {
    expect( spinner().find('.spinner-1').length ).toBe( 1 );
  });

  it(`has a default prop of size (10)`, () => {
    expect( spinner().props().size ).toBe( 10 );
  });

});
