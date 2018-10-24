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
import CustomizeModal from './includes/CustomizeModal';
// functions
import { toast } from 'react-toastify';
import { markTask, getGrid } from 'store/missions/grid/operations';
import { setMissions } from 'store/missions/grid/actions';

class TeacherMarkPage extends Component {

  state = {
    activeTab: 1,
    modalOpen: false,
    modalType: 'daily'
  }

  toggleTab = activeTab => this.setState({ activeTab });

  getGrid = ( type, date ) => {
    return this.props.getGrid( type, date )
    .catch( e => toast.error( e.message ) );
  }

  customizeGrid = ( missions ) => {
    const { modalType: type } = this.state;
    this.closeModal();
    this.props.setMissions( type, missions );
  }

  markTask = ( type, user_ids, grid_id, date, mark ) => {
    return this.props.markTask( type, user_ids, grid_id, date, mark )
    .catch( e => toast.error( e.message ) );
  }

  openModal = modalType => () => this.setState(
    { modalOpen: !this.state.modalOpen, modalType }
  );

  closeModal = () => this.setState(
    { modalOpen: !this.state.modalOpen }
  );

  render() {
    const { login, grid } = this.props;
    const { daily, weekly, tehillim } = grid;
    const { activeTab, modalOpen, modalType } = this.state;

    const navProps = { onClick: this.toggleTab, activeTab };
    const tabProps = { 
      markTask: this.markTask,
      getGrid: this.getGrid, 
      openModal: this.openModal
    };

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

        <CustomizeModal
          type={ modalType }
          isOpen={ modalOpen }
          toggle={ this.closeModal } 
          missions={ grid[ modalType ].missions }
          customizeGrid={ this.customizeGrid } />
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
  markTask, getGrid,
  setMissions
};

export default connect( mapStateToProps, mapDispatchToProps )( TeacherMarkPage );
