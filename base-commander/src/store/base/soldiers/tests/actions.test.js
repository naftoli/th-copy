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

  describe(`.updateSoldier`, () => {

    it( `returns types.UPDATE_SOLDIER as the type`, () => {
      expect( actions.updateSoldier().type ).toBe( types.UPDATE_SOLDIER );
    });

    it( `returns it's first paramater as 'payload.id'`, () => {
      expect( actions.updateSoldier( 1 ).payload.id ).toBe( 1 );
    });

    it( `returns it's second paramater as 'payload.updates'`, () => {
      expect( actions.updateSoldier( 1, { foo: 'bar' } ).payload.updates ).toEqual( { foo: 'bar' } );
    });

  });

});
