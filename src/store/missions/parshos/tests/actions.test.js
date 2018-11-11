import { types, setLoading, setParshos } from '../actions';

describe( `types`, () => {

  it( `matches snapshot`, () => {
    expect( types ).toMatchSnapshot();
  });

});

describe( `setLoading`, () => {
  it( `returns types.SET_LOADING as the type`, () => {
    expect( setLoading().type ).toBe( types.SET_LOADING );
  });

  it( `returns it's paramater as 'payload'`, () => {
    expect( setLoading( true ).payload ).toBe( true );
    expect( setLoading( false ).payload ).toBe( false );
  });
})

describe( `setParshos`, () => {
  it( `returns types.SET_PARSHOS as the type`, () => {
    expect( setParshos( ['1234', 'abcd'] ).type ).toBe( types.SET_PARSHOS );
  });

  it( `returns it's paramater as 'payload'`, () => {
    expect( setParshos( ['1234', 'abcd'] ).payload ).toEqual( ['1234', 'abcd'] );
    expect( setParshos( false ).payload ).toBe( false );
  });
})
