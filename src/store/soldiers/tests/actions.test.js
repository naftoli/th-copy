import * as actions from '../actions';
import * as types from '../types';

describe(`actions`, () => {

  describe(`.setLoading`, () => {

    it( `returns types.SET_LOADING as the type`, () => {
      expect( actions.setLoading().type ).toBe( types.SET_LOADING );
    });

    it( `returns it's paramater as 'payload'`, () => {
      expect( actions.setLoading( true ).payload ).toBe( true );
      expect( actions.setLoading( false ).payload ).toBe( false );
    });

  });

  describe(`.setSoldiers`, () => {

    it( `returns types.SET_SOLDIERS as the type`, () => {
      expect( actions.setSoldiers().type ).toBe( types.SET_SOLDIERS );
    });

    it( `returns it's paramater as 'payload'`, () => {
      const users = [ { foo: 'bar' }, { bar: 'foo' } ];
      expect( actions.setSoldiers( users ).payload ).toEqual( users );
    });

  });

});
