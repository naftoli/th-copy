import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Prompt } from 'react-router';
import { TabContent, Nav  } from 'reactstrap';
import { Redirect } from 'react-router-dom';
import { NavigationTab } from 'components/navigation';
import { FontAwesome, LoadingScreen } from 'components/ui';
// tabs
import { PlatoonTab, TeachersTab } from './tabs';
// functions
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { getPlatoon, updatePlatoon } from 'store/platoons/operations';
import { removeAuth, createAuth } from 'store/staff/operations';
import { filterUpdates } from 'functions/events';

export class PlatoonPage extends Component {
  // initial state
  state = {
    platoon: {}, updates: {}, 
    loading: true, activeTab: 2,
  }

  // load the contents if we do not have any
  componentDidMount(){
    setTitle( 'Platoon' );
    this.getPlatoon();
  }

  // non-destructivly update the state
  onUpdate = ( update ) => {
    const updates = filterUpdates( this.state.platoon, { ...this.state.updates, ...update } );
    this.setState({ updates });
  }
  // toggle tabs
  toggle = activeTab => () => this.setState({ activeTab });

  // Networking
  getPlatoon = () => {
    const { match, getPlatoon } = this.props;
    this.setState({ loading: true });
    getPlatoon( match.params.id )
    .then( platoon => { this.setState({ platoon, loading: false }); })
    .catch( error => {
      toast.error( error.message );
      this.setState({ platoon: undefined }); }
    );
  }
  // save platoon
  save = ( event ) => {
    event && event.preventDefault();
    const { updates, platoon } = this.state;
    this.props.updatePlatoon( platoon.class_id, updates )
    .then( platoon => this.setState({ platoon, updates: {} }) );
  }

  render() {
    let { platoon, loading, updates, activeTab } = this.state;

    if ( platoon === undefined ) 
      return <Redirect to='/platoons' />;
    
    if ( loading )
      return <LoadingScreen  Callout />

    const updated = Object.keys( this.state.updates ).length > 0;
    platoon = { ...platoon, ...updates };

    return (
      <div id='PlatoonPage'>
        <Prompt when={ updated } message="You have unsaved changes. Are you sure you want to leave?" />
        <Nav tabs>
          <NavigationTab active={activeTab === 1} onClick={this.toggle( 1 )}>
            Platoon <FontAwesome icon='chalkboard-teacher'/>
          </NavigationTab>
          <NavigationTab active={activeTab === 2} onClick={this.toggle( 2 )}>
            Teacher Accounts <FontAwesome icon='users'/>
          </NavigationTab>
        </Nav>
        <TabContent activeTab={ activeTab }>

          <PlatoonTab 
            tabId={ 1 }
            platoon={ platoon } 
            updated={ updated }
            onUpdate={ this.onUpdate } 
            onSubmit={ this.save }/>
          
          <TeachersTab
            tabId={ 2 }
            platoon={ platoon }
            refresh={ this.getPlatoon }
            createAuth={ this.props.createAuth }
            removeAuth={ this.props.removeAuth } />

        </TabContent>

      </div>
    )
  }
}

const mapStateToProps = ({ login }) => ({
  login: login.current_login
})

const mapDispatchToProps = { 
  getPlatoon, updatePlatoon, 
  removeAuth, createAuth
}

export default connect( mapStateToProps, mapDispatchToProps )( PlatoonPage );
