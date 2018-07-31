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

  describe(`.setPlatoons`, () => {

    it( `returns types.SET_PLATOONS as the type`, () => {
      expect( actions.setPlatoons().type ).toBe( types.SET_PLATOONS );
    });

    it( `returns it's paramater as 'payload'`, () => {
      const platoons = [ { foo: 'bar' }, { bar: 'foo' } ];
      expect( actions.setPlatoons( platoons ).payload ).toEqual( platoons );
    });

  });

  describe(`.updatePlatoon`, () => {

    it( `returns types.UPDATE_PLATOON as the type`, () => {
      expect( actions.updatePlatoon().type ).toBe( types.UPDATE_PLATOON );
    });

    it( `returns it's first paramater as 'payload.id'`, () => {
      expect( actions.updatePlatoon( 1 ).payload.id ).toBe( 1 );
    });

    it( `returns it's second paramater as 'payload.updates'`, () => {
      expect( actions.updatePlatoon( 1, { foo: 'bar' } ).payload.updates ).toEqual( { foo: 'bar' } );
    });

  });

});
