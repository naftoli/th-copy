import {
  types,        setLoading,
  setSubjects,  setLabels
} from '../actions';

describe( `types`, () => {

  it( `matches snapshot`, () => {
    expect( types ).toMatchSnapshot();
  });

});

describe( `setLoading( loading )`, () => {
  it( `returns types.SET_LOADING as the type`, () => {
    expect( setLoading( true ).type ).toBe( types.SET_LOADING );
  });

  it( `returns it's 1st paramater paramater as 'payload.type'`, () => {
    expect( setLoading( 'labels', false ).payload.type ).toBe( 'labels' );
    expect( setLoading( 'subjects', true ).payload.type ).toBe( 'subjects' );
  });

  it( `returns it's 2nd paramater paramater as 'payload.loading'`, () => {
    expect( setLoading( 'labels', false ).payload.loading ).toBe( false );
    expect( setLoading( 'subjects', true ).payload.loading ).toBe( true );
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

describe( `setLabels( labels )`, () => {
  it( `returns types.SET_LABELS as the type`, () => {
    expect( setLabels( [ 'a', 'b' ] ).type ).toBe( types.SET_LABELS );
  });

  it( `returns it's paramater as 'payload'`, () => {
    expect( setLabels( ['1234', 'abcd'] ).payload ).toEqual( ['1234', 'abcd'] );
    expect( setLabels( false ).payload ).toBe( false );
  });
});
