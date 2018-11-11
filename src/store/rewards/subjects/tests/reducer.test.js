import reducer from '../reducer';
import * as actions from '../actions';

const initialState = reducer( undefined, {} );

describe( 'reducer', () => {

  it(`state.loading defaults to false`, () => {
    expect( initialState.loading ).toBe( false );
  });

  it(`state.subjects defaults to '[]'`, () => {
    expect( initialState.subjects ).toEqual( [] );
  });

  describe(`supported actions`, () => {
    
    it(`actions.setLoading: \tupdates state.loading`, () => {
      const subject = reducer( initialState, actions.setLoading( true ) );
  
      expect( subject.loading ).toBe( true );
      expect( initialState.loading ).toBe( false );
    });
  
    it(`actions.setSubjects: \tupdates state.subjects`, () => {
      const subjects = [ { subject_id: 5 }, { subject_id: 9 } ];
      const subject = reducer( initialState, actions.setSubjects( subjects ) );
  
      expect( subject.subjects ).toEqual( subjects );
      expect( initialState.subjects ).toEqual( [] );
    });
  
    it(`actions.setSubjects: \tupdates state.loading to 'false'`, () => {
      const subjects = [ { subject_id: 5 }, { subject_id: 9 } ];
      const subject = reducer( { loading: true }, actions.setSubjects( subjects ) );
  
      expect( subject.loading ).toEqual( false );
    });
  });
});
