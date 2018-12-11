import { types, setSubjects, setLoading } from '../actions';

describe( `types`, () => {

  it( `matches snapshot`, () => {
    expect( types ).toMatchSnapshot();
  });

});

describe( `setLoading( loading )`, () => {
  it( `returns types.SET_LOADING as the type`, () => {
    expect( setLoading( true ).type ).toBe( types.SET_LOADING );
  });

  it( `returns it's paramater as 'payload'`, () => {
    expect( setLoading( true ).payload ).toBe( true );
    expect( setLoading( false ).payload ).toBe( false );
  });
});

describe( `setSubjects( subjects )`, () => {
  it( `returns types.SET_SUBJECTS as the type`, () => {
    expect( setSubjects( [ 'a', 'b' ] ).type ).toBe( types.SET_SUBJECTS );
  });

  it( `returns it's paramater as 'payload'`, () => {
    expect( setSubjects( ['1234', 'abcd'] ).payload ).toEqual( ['1234', 'abcd'] );
    expect( setSubjects( false ).payload ).toBe( false );
  });
});
