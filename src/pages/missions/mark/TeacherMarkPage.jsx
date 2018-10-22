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
// functions
import { toast } from 'react-toastify';
import { markTask, getGrid } from 'store/missions/grid/operations';

class TeacherMarkPage extends Component {

  state = {
    activeTab: 1
  }

  toggle = activeTab => this.setState({ activeTab });

  getGrid = ( type, date ) => {
    return this.props.getGrid( type, date )
    .catch( e => toast.error( e.message ) );
  }

  markTask = ( type, user_ids, grid_id, date, mark ) => {
    return this.props.markTask( type, user_ids, grid_id, date, mark )
    .catch( e => toast.error( e.message ) );
  }

  render() {
    const { login } = this.props;
    const { activeTab } = this.state;
    const { daily, weekly, tehillim } = this.props.grid;

    const navProps = { onClick: this.toggle, activeTab };
    const tabProps = { markTask: this.markTask, getGrid: this.getGrid };

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

          { login.modules.tehillim && 
            <NavigationTab tab={ 3 } icon='book' { ...navProps }>
              Tehillim
            </NavigationTab>
          }

        </Nav>

        <TabContent activeTab={ activeTab }>

          <DailyTab tabId={ 1 } { ...daily } { ...tabProps } />

          <WeeklyTab tabId={ 2 } { ...weekly } { ...tabProps } />

          { login.modules.tehillim && 
            <TehillimTab tabId={ 3 } { ...tehillim } { ...tabProps } />
          }

        </TabContent>
      </div>
    );
  }
}

const mapStateToProps = ({ missions, login }) => {
  return {
    grid: missions.grid,
    login: login.current_login
  }
};

const mapDispatchToProps = {
  markTask, getGrid
};

export default connect( mapStateToProps, mapDispatchToProps )( TeacherMarkPage );
