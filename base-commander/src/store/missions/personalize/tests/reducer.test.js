import reducer from '../reducer';
import * as actions from '../actions';

const initialState = reducer( undefined, {} );

describe( 'reducer', () => {

  it(`state defaults to '[]'`, () => {
    expect( initialState ).toEqual( [] );
  });

  it(`actions.setCampaigns: updates the state`, () => {
    const campaigns = [ { subject_id: 5 }, { subject_id: 9 } ];
    expect( reducer( initialState, actions.setCampaigns( campaigns ) ) ).toEqual( campaigns );
    expect( initialState ).toEqual( [] );
  });

  it(`actions.setTasks: updates the campaigns on the campaign with the correct subject_id`, () => {
    const campaigns = [ { subject_id: 5 }, { subject_id: 9 } ];
    const tasks = [ { task: 'Do Homework'} , { task: 'Wash Dishes' } ];

    const subject = reducer( campaigns, actions.setTasks( 5, tasks ) );
    // take a snapshot test
    expect( subject ).toMatchSnapshot();
    expect( campaigns ).toMatchSnapshot();
    // test the first campaign
    expect( campaigns[0].tasks ).toBe( undefined );
    expect( subject[0].tasks ).toEqual( tasks );
    // test the second campaign
    expect( campaigns[1].tasks ).toBe( undefined );
    expect( subject[1].tasks ).toBe( undefined );
  });

  it(`actions.setMissions: updates the missions on campaign.tasks with the correct subject_id and task`, () => {
    const campaigns = [
      { subject_id: 5, tasks: [ { task: 'Do Homework'} ] },
      { subject_id: 9, tasks: [ { task: 'Do Homework'} ]  }
    ];
    const missions = [ { name: "אבות ובנים" }, { name: "Avos Ubanim", } ];

    const subject = reducer( campaigns, actions.setMissions( 5, 'Do Homework', missions ) );
    // take a snapshot test
    expect( subject ).toMatchSnapshot();
    expect( campaigns ).toMatchSnapshot();
    // test the first campaign
    expect( campaigns[0].tasks[0].missions ).toBe( undefined );
    expect( subject[0].tasks[0].missions ).toEqual( missions );
    // test the second campaign
    expect( campaigns[1].tasks[0].missions ).toBe( undefined );
    expect( subject[1].tasks[0].missions ).toBe( undefined );
  });

  describe(`actions.personalize:`, () => {
    // state which we will test on
    const state = [
      { subject_id: 5, enrolled: false, tasks: [
        { task: 'Do Homework', enrolled: false, missions: [
          { name: "Avos Ubanim", enrolled: false }
        ]}
      ]},
      { subject_id: 9, enrolled: false, tasks: [
        { task: 'Do Homework', enrolled: false, missions: [
          { name: "Avos Ubanim", enrolled: false }
        ]}
      ]}
    ];

    it( `updates subjects`, () => {
      const opts = { level: 'campaign', subject_id: 5, enrolled: true }
      const subject = reducer( state, actions.personalize( opts ) );

      expect( state[0].enrolled ).toBe( false );
      expect( subject[0].enrolled ).toBe( true );
      expect( subject[1].enrolled ).toBe( false );
    });

    it( `updates tasks`, () => {
      const opts = { level: 'task', subject_id: 5, task: 'Do Homework', enrolled: true }
      const subject = reducer( state, actions.personalize( opts ) );

      // check that it does not update the subject
      expect( subject[0].enrolled ).toBe( false );
      // check that it does update the correct task
      expect( state[0].tasks[0].enrolled ).toBe( false );
      expect( subject[0].tasks[0].enrolled ).toBe( true );
      // does not edit other tasks
      expect( subject[1].tasks[0].enrolled ).toBe( false );
    });
    
    it(`updates missions`, () => {
      const opts = {
        level: 'mission',     subject_id: 5,  enrolled: true,
        task: 'Do Homework',  mission: "Avos Ubanim"
      }
      const subject = reducer( state, actions.personalize( opts ) );

      // check that it does not update the subject or task
      expect( subject[0].enrolled ).toBe( false );
      expect( subject[0].tasks[0].enrolled ).toBe( false );

      // check that it does update the correct mission
      expect( state[0].tasks[0].missions[0].enrolled ).toBe( false );
      expect( subject[0].tasks[0].missions[0].enrolled ).toBe( true );

      // does not edit other tasks
      expect( subject[1].tasks[0].missions[0].enrolled ).toBe( false );
    });
  });
});
