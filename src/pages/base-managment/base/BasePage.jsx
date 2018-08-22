import React, { Component } from 'react';
import { connect } from 'react-redux';
// import PropTypes from 'prop-types';
// components
import { Prompt } from 'react-router';
import { FontAwesome, Spinner } from 'components/ui';
import { NavigationTab } from 'components/navigation';
import { TabContent, TabPane, Nav, Button, Collapse } from 'reactstrap';
// tabs
import { BaseTab, PaymentsTab, ShippingTab, SettingsTab } from './tabs';
// state
import { getBase, updateBase } from 'store/bases/operations';
// functions
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { filterUpdates } from 'functions/events';
import { loginStoreChanged, isAdmin } from 'functions/login';

const SaveButton = props => (
  <Button color='primary' {...props}>
    <FontAwesome icon='save'/> Save Changes
  </Button>
);

const ErrorButton = props => (
  <Button color='danger' role='button' disabled {...props}>
    <FontAwesome icon='exclamation-circle'/> Cannot Save Invalid Information. 
      Please Check <strong>All</strong> Tabs.
  </Button>
);

class BasesPage extends Component {

  static propTypes = {};

  state = {
    base: {}, // the current base
    updates: {}, // the updates we have done
    activeTab: 1, // currently visiable tab
    loading: true // loading base or not
  }

  formRef = React.createRef();

  /********************************* PAGE LIFECYCLE *********************************/
  // update the base on mount
  componentDidMount() {
    this.loadBase()
  }
  // or on login change
  componentDidUpdate({ login }) {
    if ( loginStoreChanged( login ) ) this.loadBase();
  }
  // load the base from the API
  loadBase = () => {
    const { login, history, match } = this.props;
    let school_id = parseInt( match.params.id, 10 );
    // if we are on the wrong base, fix the URL
    if ( !isAdmin( login.code ) && school_id !== login.id ) {
      this.setState({ loading: true, updates: {} }, () => { // clear any updates before navigating away...
        history.replace( match.path.replace(':id([0-9]+)', login.id) );
      });
      school_id = login.id; // and load the correct school
    } else this.setState({ loading: true, updates: {} });
    // load the final base
    this.props.getBase( school_id )
      .then( base => {
        setTitle( `View / Edit Base #${base.school_number}` );
        this.setState({ base, loading: false })
      })
      .catch( error => toast.error( error.message ) );
  }

  /********************************* EVENT HANDLERS *********************************/
  toggle = activeTab => () => this.setState({ activeTab });
  // input changed
  onUpdate = updates => {
    updates = filterUpdates( this.state.base, { ...this.state.updates, ...updates } );
    this.setState({ updates });
  };
  // save the changes to the base
  saveChanges = event => {
    event && event.preventDefault();
    this.props.updateBase( this.state.base.school_id, this.state.updates )
    .then( base => this.setState({ base, updates: {} }) )
  }
  

  render() {
    let { loading, base, updates, activeTab } = this.state;
    base = { ...base, ...updates };
    // is the form updated and valid
    const updated = Object.keys( updates ).length > 0;

    let saveButton = <SaveButton />;

    if ( loading ) return <Spinner />;

    return (
      <div id='BasePage'>
        <Prompt when={ updated } message="You have unsaved changes. Are you sure you want to leave?" />
        <Nav tabs>
          <NavigationTab active={activeTab === 1} onClick={this.toggle(1)}>
            Base <FontAwesome icon='school'/>
          </NavigationTab>
          <NavigationTab active={activeTab === 2} onClick={this.toggle(2)}>
            Settings <FontAwesome icon='sliders-h'/>
          </NavigationTab>
          <NavigationTab active={activeTab === 3} onClick={this.toggle(3)}>
            Shipping <FontAwesome icon='shipping-fast'/>
          </NavigationTab>
          <NavigationTab active={activeTab === 4} onClick={this.toggle(4)}>
            Payments <FontAwesome icon='credit-card'/>
          </NavigationTab>
          <NavigationTab active={activeTab === 5} onClick={this.toggle(5)}>
            Debug <FontAwesome icon='bug'/>
          </NavigationTab>
        </Nav>
        <form onSubmit={ this.saveChanges } ref={ this.formRef }>
          <TabContent activeTab={ activeTab }>
            <TabPane tabId={1}>
              <BaseTab base={ base } onUpdate={ this.onUpdate } />
            </TabPane>
            <TabPane tabId={2}>
              <SettingsTab base={ base } onUpdate={ this.onUpdate } />
            </TabPane>
            <TabPane tabId={3}>
              <ShippingTab base={ base } onUpdate={ this.onUpdate } />
            </TabPane>
            <TabPane tabId={4}>
              <PaymentsTab profile={ base.customerProfile } />
            </TabPane>
            <TabPane tabId={5}>
              <pre>{ JSON.stringify( this.state, null, 2 ) }</pre>
            </TabPane>
          </TabContent>
          <Collapse isOpen={ updated } id='save'>
            { saveButton }
          </Collapse>
        </form>
      </div>
    );
  }
}

const mapStateToProps = ({ login }) => ({
  login: login.current_login
});

export default connect( mapStateToProps, { getBase, updateBase } )( BasesPage );
