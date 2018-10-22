import React, { Component } from 'react';
import { connect } from 'react-redux';
// componets
import { Callout } from 'components/ui';
import { TabContent, Nav } from 'reactstrap';
import { NavigationTab } from 'components/navigation';
// tabs
import DailyTab from './tabs/DailyTab';
import WeeklyTab from './tabs/WeeklyTab';
import TehillimTab from './tabs/TehillimTab';

import { markTask, getGrid } from 'store/missions/grid/operations';

class TeacherMarkPage extends Component {

  state = {
    activeTab: 2
  }

  toggle = activeTab => this.setState({ activeTab });

  render() {
    const { activeTab } = this.state;
    const { daily, weekly, tehillim } = this.props.grid;
    const { markTask, getGrid } = this.props;

    const navProps = { onClick: this.toggle, activeTab };
    const tabProps = { markTask, getGrid };

    return (
      <div id='TeacherMarkPage'>
        <Callout title='Mark Missions'>
          <p>The missions below are organized by how often they happen and displayed in an easy to mark grid for your convenience.</p>
          <p><strong>Please Note:</strong> Soldier's missions may be marked by their parents and base commanders as well.</p>
        </Callout>

        <Nav tabs>
          
          <NavigationTab tab={ 1 } icon='cloud-sun' { ...navProps }>
            Daily
          </NavigationTab>

          <NavigationTab tab={ 2 } icon='calendar' { ...navProps }>
            Weekly
          </NavigationTab>

          <NavigationTab tab={ 3 } icon='book' { ...navProps }>
            Tehillim
          </NavigationTab>

        </Nav>

        <TabContent activeTab={ activeTab }>

          <DailyTab tabId={ 1 } { ...daily } { ...tabProps } />

          <WeeklyTab tabId={ 2 } { ...weekly } { ...tabProps } />

          <TehillimTab tabId={ 3 } { ...tehillim } { ...tabProps } />

        </TabContent>
      </div>
    );
  }
}

const mapStateToProps = ({ missions }) => {
  return {
    grid: missions.grid
  }
};

const mapDispatchToProps = {
  markTask, getGrid
};

export default connect( mapStateToProps, mapDispatchToProps )( TeacherMarkPage );
