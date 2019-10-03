import {
  types, personalize,
  setCampaigns, setTasks, setMissions
} from '../actions';

describe( `types`, () => {

  it( `matches snapshot`, () => {
    expect( types ).toMatchSnapshot();
  });

});

describe( `setCampaigns( campaigns )`, () => {
  it( `returns types.SET_CAMPAIGNS as the type`, () => {
    expect( setCampaigns( ['1234', 'abcd'] ).type ).toBe( types.SET_CAMPAIGNS );
  });

  it( `returns it's paramater as 'payload'`, () => {
    expect( setCampaigns( ['1234', 'abcd'] ).payload ).toEqual( ['1234', 'abcd'] );
    expect( setCampaigns( false ).payload ).toBe( false );
  });
});

describe( `setTasks( subject_id, tasks )`, () => {
  it( `returns types.SET_TASKS as the type`, () => {
    expect( setTasks( 5, [ 'a', 'b' ] ).type ).toBe( types.SET_TASKS );
  });

  it( `returns it's first paramater as 'payload.subject_id'`, () => {
    expect( setTasks( 5, [ 'a', 'b' ] ).payload.subject_id ).toBe( 5 );
    expect( setTasks( 9, [ 'c', 'd' ] ).payload.subject_id ).toBe( 9 );
  });

  it( `returns it's second paramater as 'payload.tasks'`, () => {
    expect( setTasks( 5, [ 'a', 'b' ] ).payload.tasks ).toEqual( [ 'a', 'b' ] );
    expect( setTasks( 9, [ 'c', 'd' ] ).payload.tasks ).toEqual( [ 'c', 'd' ] );
  });
});

describe( `setMissions( subject_id, task, missions )`, () => {
  it( `returns types.SET_MISSIONS as the type`, () => {
    expect( setMissions().type ).toBe( types.SET_MISSIONS );
  });

  it( `returns it's first paramater as 'payload.subject_id'`, () => {
    expect( setMissions( 5, 'task 1', [ 'a', 'b' ] ).payload.task ).toBe( 'task 1' );
    expect( setMissions( 9, 'task 2', [ 'c', 'd' ] ).payload.task ).toBe( 'task 2' );
  });

  it( `returns it's second paramater as 'payload.task'`, () => {
    expect( setMissions( 5, 'task 1', [ 'a', 'b' ] ).payload.task ).toBe( 'task 1' );
    expect( setMissions( 9, 'task 2', [ 'c', 'd' ] ).payload.task ).toBe( 'task 2' );
  });

  it( `returns it's third paramater as 'payload.missions'`, () => {
    expect( setMissions( 5, 'task 1', [ 'a', 'b' ] ).payload.missions ).toEqual( [ 'a', 'b' ] );
    expect( setMissions( 9, 'task 2', [ 'c', 'd' ] ).payload.missions ).toEqual( [ 'c', 'd' ] );
  });
});

describe( `personalize( updates )`, () => {
  it( `returns types.PERSONALIZE as the type`, () => {
    expect( personalize().type ).toBe( types.PERSONALIZE );
  });

  it( `returns it's paramater as 'payload'`, () => {
    expect( personalize( ['1234', 'abcd'] ).payload ).toEqual( ['1234', 'abcd'] );
    expect( personalize( false ).payload ).toBe( false );
  });
});
