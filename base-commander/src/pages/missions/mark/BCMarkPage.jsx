import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Collapse } from 'reactstrap';
import { Callout } from 'components/ui';
import { Label } from 'components/missions';
import MissionsForm from './includes/MissionsForm';
// store and constants
import { setMissions } from 'store/missions/mark/actions';
import { markMission } from 'store/missions/mark/operations';

class BCMarkPage extends Component {

  state = {
    user_id: false
  }

  componentWillUnmount(){
    this.props.setMissions( [] );
  }

  setUserId = user_id => this.setState({ user_id });

  organizeByLabel = ( accumulator, value ) => {
    // if we have hit a new label that we have not seen before
    if ( !accumulator[ value.label_name ] )
      accumulator[ value.label_name ] = [];
    // add the mission to the label
    accumulator[ value.label_name ].push( value );
    return accumulator;
  }

  render() {
    let { loading, markMission, missions } = this.props;

    missions = missions.reduce( this.organizeByLabel, {} );
    const labels = Object.keys( missions );

    return (
      <div id='BCMarkPage'>
        <div className='no-print'>
          <Callout title='Mark Missions'>
            <p><strong>Load the</strong> missions to mark the soldiers missions inline in a mobile-frendly way.</p>
            <p>Press the <strong>Mark Printed Version</strong> button to use the old marking page and mark the sheets as printed.</p>
            <p>Enter a <strong>valid Serial Number</strong> (7xxxxxx) in the serial number box below to quick select the soldier.</p>
          </Callout>

          <MissionsForm 
            user_id={ this.state.user_id }
            onSoldierChange={ this.setUserId }  />
        </div>

        <Collapse isOpen={ !loading } >
          <div id='missions'>
            { labels.map( ( label, index ) => 
              <Label 
                key={ index } 
                label={ label } 
                user_id={ this.state.user_id }
                missions={ missions[ label ] } 
                markMission={ markMission } /> 
            ) }
          </div>
        </Collapse>
      </div>
    );
  }
}

const mapStateToProps = ({ missions }) => {
  return {
    ...missions.mark,
  }
};

const mapDispatchToProps = { setMissions, markMission };

export default connect( mapStateToProps, mapDispatchToProps )( BCMarkPage );
