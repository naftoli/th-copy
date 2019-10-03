import reducer from '../reducer';
import * as actions from '../actions';

const initialState = reducer( undefined, {} );

describe( 'reducer', () => {

  it(`state.loading defaults to {}`, () => {
    expect( initialState.loading ).toEqual( {} );
  });

  it(`state.tasks defaults to '[]'`, () => {
    expect( initialState.tasks ).toEqual( [] );
  });

  describe(`supported actions`, () => {
    
    it(`actions.setLoading: \tupdates state.loading`, () => {
      const subject = reducer( initialState, actions.setLoading( 'foo', true ) );
  
      expect( subject.loading.foo ).toBe( true );
      expect( initialState.loading.foo ).not.toBeDefined();
    });

    describe( `actions.setTasks`, () => {

      it(`updates state.tasks`, () => {
        const tasks = [ { subject_id: 5 }, { subject_id: 9 } ];
        const subject = reducer( initialState, actions.setTasks( tasks ) );
    
        expect( subject.tasks ).toEqual( tasks );
        expect( initialState.tasks ).toEqual( [] );
      });
    
      it(`updates state.loading.tasks to 'false'`, () => {
        const tasks = [ { subject_id: 5 }, { subject_id: 9 } ];
        const subject = reducer( initialState, actions.setTasks( tasks ) );
    
        expect( subject.loading.tasks ).toBe( false );
        expect( initialState.loading.tasks ).not.toBeDefined();
      });

    });

    describe( `actions.addTask`, () => {

      it(`updates state.tasks`, () => {
        const tasks = [ { subject_id: 5 }, { subject_id: 9 } ];
        const state = reducer( initialState, actions.setTasks( tasks ) );
        const subject = reducer( state, actions.addTask( { subject_id: 12 } ) );
        // check that it puts it in the array
        expect( state.tasks.length ).toBe( 2 );
        expect( subject.tasks.length ).toBe( 3 );
        // check that it pushes it to the beginning
        expect( state.tasks[0].subject_id ).toBe( 5 );
        expect( subject.tasks[0].subject_id ).toBe( 12 );
        // check that we did not touch the initial state
        expect( initialState.tasks ).toEqual( [] );
      });
    
      it(`updates state.loading.saving to 'false'`, () => {
        const subject = reducer( initialState, actions.addTask( { subject_id: 9 } ) );
    
        expect( subject.loading.saving ).toBe( false );
        expect( initialState.loading.saving ).not.toBeDefined();
      });

    });

    describe( `actions.updateTask`, () => {

      it(`updates state.tasks`, () => {
        const tasks = [
          { grid_id: 5, lang_id: 1 },
          { grid_id: 9, lang_id: 1 }
        ];
        const state = reducer( initialState, actions.setTasks( tasks ) );
        const subject = reducer( state, actions.updateTask(
          { grid_id: 9, lang_id: 1, update: true }
        ));
        // check that it does not change the length of the array
        expect( subject.tasks.length ).toBe( 2 );
        // check that it pushes it to the beginning
        expect( state.tasks[1].update ).not.toBeDefined();
        expect( subject.tasks[1].update ).toBe( true );
        // check that we did not touch the initial state
        expect( initialState.tasks ).toEqual( [] );
      });
    
      it(`updates state.loading.updating to 'false'`, () => {
        const subject = reducer( initialState, actions.updateTask( { subject_id: 9 } ) );
    
        expect( subject.loading.updating ).toBe( false );
        expect( initialState.loading.updating ).not.toBeDefined();
      });
    });

  });
});
