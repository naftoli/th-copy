import React, { Component } from 'react';

import { TabPane } from 'reactstrap';
import { Prompt } from 'react-router';
import { MedalBoard } from '../includes/MedalBoard';
import { SaveButton } from 'components/buttons/index';

class MedalsTab extends Component {

  state = {
    updates: [], // subject_id => total missions
    saving: false
  }

  onMissionChange = ( subject_id, value ) => {
    const { [subject_id]: subject, ...updates } = this.state.updates;
    
    if ( value !== undefined )
      updates[ subject_id ] = value;

    this.setState({ updates });
  }

  save = () => {
    let { updates, saving } = this.state;
    // return false if we are already saving missions
    if ( saving ) return false;
    // map the updates to a array of objects
    updates = Object.entries( updates )
      .map ( update => ({
        subject_id: update[0],
        missions: parseInt( update[1], 10 )
      }) );
    // update the state
    this.setState({ saving: true });
    // update the mission and the state
    this.props.updateMissions( updates )
    .then( () => this.setState({
      updates: {},
      saving: false
    }));
  }

  render() {
    const { updates, saving } = this.state;

    const update_count = Object.keys( updates ).length;

    return (
      <TabPane id='MedalsTab' tabId= { this.props.tabId }>

        <Prompt 
          when={ update_count > 0 } 
          message="You have unsaved medals changes. Are you sure you want to leave?" />

        <MedalBoard
          board={ this.props.board }
          updates={ this.state.updates }
          onMissionChange={ this.onMissionChange }/>

        <SaveButton show
          saving={ saving }
          onClick={ this.save }
          disabled={ update_count <= 0 || saving }>

          Save updates to { update_count } Campaigns
        </SaveButton>

      </TabPane>
    )
  }
}

export { MedalsTab };
