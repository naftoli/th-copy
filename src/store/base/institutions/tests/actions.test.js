import * as actions from '../actions';
// get the types
const types = actions.types;

describe(`types`, () => {

  it(`matches snapshot`, () =>{
    expect( types ).toMatchSnapshot();
  });
  
});

describe(`actions`, () => {

  describe(`.setLoading`, () => {
    it( `matches snapshot`, () => {
      expect( actions.setLoading( false ) ).toMatchSnapshot();
      expect( actions.setLoading( true ) ).toMatchSnapshot();
    });
  });

  describe(`.setInstitutions`, () => {
    it( `matches snapshot`, () => {
      expect( actions.setInstitutions( [{ foo: 'bar' }] ) ).toMatchSnapshot();
    });
  });

});