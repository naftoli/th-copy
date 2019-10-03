import * as actions from '../actions';
// get the types
const types = actions.types;

describe(`types`, () => {
  it(`matches snapshot`, () =>{
    expect( types ).toMatchSnapshot();
  });
});

describe(`actions`, () => {

  describe(`.setMiles`, () => {
    it( `returns types.SET_MILES as the type`, () => {
      expect( actions.setMiles().type ).toBe( types.SET_MILES );
    });

    it( `returns it's paramater as 'payload'`, () => {
      expect( actions.setMiles( true ).payload ).toBe( true );
    });
  });
});
