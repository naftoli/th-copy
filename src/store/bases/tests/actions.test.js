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

  describe(`.setBases`, () => {

    it( `returns types.SET_BASES as the type`, () => {
      expect( actions.setBases().type ).toBe( types.SET_BASES );
    });

    it( `returns it's paramater as 'payload'`, () => {
      const bases = [ { foo: 'bar' }, { bar: 'foo' } ];
      expect( actions.setBases( bases ).payload ).toEqual( bases );
    });

  });

  describe(`.updateBases`, () => {

    it( `returns types.UPDATE_BASE as the type`, () => {
      expect( actions.updateBases().type ).toBe( types.UPDATE_BASE );
    });

    it( `returns it's first paramater as 'payload.id'`, () => {
      expect( actions.updateBases( 1 ).payload.id ).toBe( 1 );
    });

    it( `returns it's second paramater as 'payload.updates'`, () => {
      expect( actions.updateBases( 1, { foo: 'bar' } ).payload.updates ).toEqual( { foo: 'bar' } );
    });

  });

});
