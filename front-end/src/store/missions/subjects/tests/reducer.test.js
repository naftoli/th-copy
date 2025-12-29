import reducer from '../reducer';
import * as actions from '../actions';

const initialState = reducer( undefined, {} );

describe( 'reducer', () => {

  it(`state.loading defaults to {}`, () => {
    expect( initialState.loading ).toEqual( {} );
  });

  it(`state.subjects defaults to '[]'`, () => {
    expect( initialState.subjects ).toEqual( [] );
  });

  describe(`supported actions`, () => {
    
    it(`actions.setLoading: \tupdates state.loading`, () => {
      const subject = reducer( initialState, actions.setLoading( 'foo', true ) );
  
      expect( subject.loading.foo ).toBe( true );
      expect( initialState.loading.foo ).not.toBeDefined();
    });
  
    it(`actions.setSubjects: \tupdates state.subjects`, () => {
      const subjects = [ { subject_id: 5 }, { subject_id: 9 } ];
      const subject = reducer( initialState, actions.setSubjects( subjects ) );
  
      expect( subject.subjects ).toEqual( subjects );
      expect( initialState.subjects ).toEqual( [] );
    });
  
    it(`actions.setSubjects: \tupdates state.loading.subjects to 'false'`, () => {
      const subjects = [ { subject_id: 5 }, { subject_id: 9 } ];
      const subject = reducer( { loading: {} }, actions.setSubjects( subjects ) );
  
      expect( subject.loading.subjects ).toBe( false );
    });

    it(`actions.setLabels: \tupdates state.labels`, () => {
      const labels = [ { subject_id: 5 }, { subject_id: 9 } ];
      const subject = reducer( initialState, actions.setLabels( labels ) );
  
      expect( subject.labels ).toEqual( labels );
      expect( initialState.labels ).toEqual( [] );
    });
  
    it(`actions.setLabels: \tupdates state.loading.labels to 'false'`, () => {
      const labels = [ { subject_id: 5 }, { subject_id: 9 } ];
      const subject = reducer( { loading: {} }, actions.setLabels( labels ) );
  
      expect( subject.loading.labels ).toBe( false );
    });

  });
});
