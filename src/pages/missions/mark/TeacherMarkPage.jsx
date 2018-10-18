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

class TeacherMarkPage extends Component {

  state = {
    activeTab: 1
  }

  toggle = activeTab => this.setState({ activeTab });

  render() {
    const { activeTab } = this.state;

    const navProps = { onClick: this.toggle, activeTab };

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

          <DailyTab tabId={ 1 } />

          <WeeklyTab tabId={ 2 } />

          <TehillimTab tabId={ 3 } />

        </TabContent>
      </div>
    );
  }
}

const mapStateToProps = () => {
  return {}
};

const mapDispatchToProps = {};

export default connect( mapStateToProps, mapDispatchToProps )( TeacherMarkPage );
