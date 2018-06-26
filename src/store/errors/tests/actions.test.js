import * as actions from '../actions';
import * as types from '../types';

describe(`actions`, () => {

  describe(`.addError`, () => {
    it( `returns types.ADD_ERROR as the type`, () => {
      expect( actions.addError().type ).toBe( types.ADD_ERROR );
    });

    it( `returns it's paramater as payload.message`, () => {
      expect( actions.addError('hello world').payload.message ).toBe('hello world')
    })

    it( `generates a uuid as payload.id`, () => {
      const uuidPattern = /^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/g; //v1 UUID
      expect( actions.addError('hello world').payload.id ).toBeDefined();
      expect( uuidPattern.test(
        actions.addError('hello world').payload.id
      )).toBe( true );
    })
  });

  describe(`.clearError`, () => {

    it( `returns types.CLEAR_ERROR as the type`, () => {
      expect( actions.clearError().type ).toBe( types.CLEAR_ERROR );
    });

    it( `returns it's paramater as 'payload'`, () => {
      expect( actions.clearError( 1 ).payload ).toBe( 1 );
      expect( actions.clearError( 5 ).payload ).toBe( 5 );
    });

  });
});