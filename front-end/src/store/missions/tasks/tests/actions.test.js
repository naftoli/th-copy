import {
  types,
  addTask,  setLoading,
  setTasks, updateTask,
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

// * setTasks
describe( `setTasks( tasks )`, () => {
  it( `returns types.SET_TASKS as the type`, () => {
    expect( setTasks( ['1234', 'abcd'] ).type ).toBe( types.SET_TASKS );
  });

  it( `returns it's paramater as 'payload'`, () => {
    expect( setTasks( ['1234', 'abcd'] ).payload ).toEqual( ['1234', 'abcd'] );
    expect( setTasks( false ).payload ).toBe( false );
  });
});

// * addTask
describe( `addTask( task )`, () => {
  it( `returns types.ADD_TASK as the type`, () => {
    expect( addTask( true ).type ).toBe( types.ADD_TASK );
  });

  it( `returns it's paramater as 'payload'`, () => {
    expect( addTask( ['1234', 'abcd'] ).payload ).toEqual( ['1234', 'abcd'] );
    expect( addTask( false ).payload ).toBe( false );
  });
});

// * updateTask
describe( `updateTask( task )`, () => {
  it( `returns types.UPDATE_TASK as the type`, () => {
    expect( updateTask( true ).type ).toBe( types.UPDATE_TASK );
  });

  it( `returns it's paramater as 'payload'`, () => {
    expect( updateTask( ['1234', 'abcd'] ).payload ).toEqual( ['1234', 'abcd'] );
    expect( updateTask( false ).payload ).toBe( false );
  });
});
