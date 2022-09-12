import './includes/register.scss';
// main imports
import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// sub pages
import { LockedError } from 'pages/errors';
import { Nav, TabContent } from 'reactstrap';
import { LoadingScreen } from 'components/ui';
import { NavigationTabs } from 'components/navigation';
import { ShippingTab } from './includes/tabs/ShippingTab';
import { BaseTab } from './includes/tabs/registration/BaseTab';
import { ModulesTab } from './includes/tabs/registration/ModulesTab';
import { PaymentTab } from './includes/tabs/registration/PaymentTab';
import { SettingsTab } from './includes/tabs/registration/SettingsTab';
import { PlatoonsTab } from "./includes/tabs";
// functions
import memoize from 'memoize-one';
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { isBC, isBlank } from 'functions/login';
import { validateCC } from 'functions/validations';
import { showError } from 'functions/notifications';
import { getCurrentUser } from 'store/login/operations';
import { onInputChange, onCheckboxChange } from 'functions/events';
import { getTotal, getCart } from './includes/tabs/registration/functions';
import { getBase, getDefaults, registerBase, updateBase } from 'store/base/bases/operations';
import axios from "axios";

class RegistrationPage extends Component {
  // initial state
  state = {
    cc: {},       base: {},
    valid: {},    terms: false,
    activeTab: 1, currentTab: 1,
    discount: 0
  }
  // props
  static propTypes = {
    login: PropTypes.object.isRequired,
    getBase: PropTypes.func.isRequired,
    getDefaults: PropTypes.func.isRequired,
    registerBase: PropTypes.func.isRequired,
  }

  // load the state on mount if we can
  componentDidMount() {
    setTitle('Base Registration');
    // if we are a BC, load up the base we are registering.
    const { login } = this.props;
    let promise = this.props.getDefaults;
    // load the base if we can
    if ( isBC( login.code, true ) ) {
      promise = this.props.getBase;
    }

    showError( // show error messages
      promise( login.id ) // load the base and update the state
      .then( base => {
        this.onUpdate( base )
        console.log(base)
      } )
          .then(this.checkDiscount())
    );
  }

  checkDiscount() {
    const url = "https://mashpia.com/ajax/checkForDiscount.php?school_id=" + this.props.login.school_id
    axios.get(url)
      .then(result => {
        console.log(result)
        const data = result.data
        if (data.discount) {
          this.setState({ discount: data.discount })
        }
      })
      .catch(error => console.log(error))
  }

  // prevent memory leaks?
  componentWillUnmount(){
    this.onUpdate = console.log;
  }
  
  // update the base that is in the state
  onUpdate = updates => {
    this.setState({ base: { ...this.state.base, ...updates } });
  }

  // * event handlers
  onChange = onInputChange( this.onUpdate );
  onCheckboxChange = onCheckboxChange( this.onUpdate );

  // update the state in the last tab
  onStateUpdate = key => val => this.setState({ [key]: val });

  // update the validity of the forms
  updateValid = tab => status => {
    if ( this.state.valid[tab] !== status ) {
      this.setState({ valid: { ...this.state.valid, [tab]: status } })
    }
  }

  // update the current active tab
  toggleTab = activeTab => {
    this.setState({ activeTab })
  }

  // next tab
  back = e => {
    e && e.preventDefault();
    this.toggleTab( this.state.activeTab - 1 );
  }

  // check if we reached a tab yet
  tabDisabled = tab => {
    return this.state.currentTab < tab;
  }

  // handle when a tab is submitted
  submitTab = memoize( tabId => e => {
    e.preventDefault();
    
    // update base with info
    this.props.updateBase( this.props.login.id, this.state.base ); // not working? need to ask Menachem

    const nextTab = tabId + 1;
    // set the active tab
    this.setState({ activeTab: nextTab });
    // go to the next tab if we have not been there before
    if ( this.state.currentTab < nextTab ) {
      this.setState({ currentTab: nextTab });
    }
    // scroll to the top of the page
    document.querySelector('#dashboard-content').scrollTop = 0;
  });

  register = () => {
    const { base, cc, terms, valid, discount } = this.state;
    // calculate the total registration cost
    let promise;
    // get the cart and total
    const cart = getCart( base );
    const total = getTotal( base, true, discount );
    
    // check if all valid props are set to true.
    const allValid = Object.keys( valid ).every( k =>  valid[k] );
    // if we have any invalid tabs, return an error
    if ( !allValid ) {
      return toast.error('Invalid options. Please check tabs with Exclamation Mark');
    }
    // make sure they agree to the terms and conditions
    if ( !terms ) {
      return toast.error('You must accept the Terms and Conditions.');
    }

    // if the total is greater then 0
    if ( total > 0 ) {
      promise = validateCC( cc )
        .then( cc => this.props.registerBase({ base, cc, cart, total, discount }) );
    // otherwise just register the base
    } else {
      promise = this.props.registerBase({ base, total });
    }

    // set the state
    this.setState({ registering: true });
    // reload the user data once the registration is compleate
    return promise.then( this.props.getCurrentUser )
      // and redirect to the homepage
      .then( () => this.props.history.replace('/') )
      // show any erros and get rid of the loading page
      .catch( e => {
        this.setState({ registering: false });
        toast.error( e.message )
      });
  }

  // render the page
  render() {
    const { code } = this.props.login;
    const { cc, base, activeTab, valid, terms, registering, discount } = this.state;
    // everyone but a base commander and a blank account get the locked error screen
    if ( !isBC( code, true ) && !isBlank( code ) ) {
      return <LockedError />;
    }
  
    if ( registering ) {
      return <LoadingScreen />
    }

    const navProps = { activeTab, onClick: this.toggleTab };
    const tabs = [
      { tab: 1, ...navProps, icon: 'school',    title: 'Base Information', valid: valid.base },
      { tab: 2, ...navProps, icon: 'truck',     title: 'Shipping', disabled: this.tabDisabled( 2 ), valid: valid.shipping },
      { tab: 3, ...navProps, icon: 'school',    title: 'Platoons', disabled: this.tabDisabled( 3 ) },
      { tab: 4, ...navProps, icon: 'tasks',     title: 'Modules', disabled: this.tabDisabled( 4 ),  valid: valid.modules },
      { tab: 5, ...navProps, icon: 'sliders-h', title: 'Settings',  disabled: this.tabDisabled( 5 ) },
      { tab: 6, ...navProps, icon: 'file-invoice', title: 'Payment', disabled: this.tabDisabled( 6 ) },
    ];

    return (
      <div id='RegisterBasePage'>
        <h2>Tzivos Hashem Base Registration</h2>

        <hr />

        <Nav tabs>
          <NavigationTabs tabs={ tabs } />
        </Nav>

        <TabContent activeTab={ activeTab }>

          <BaseTab 
            tabId={ 1 }
            base={ base }
            onUpdate={ this.onUpdate }
            onChange={ this.onChange }
            onSubmit={ this.submitTab( 1 ) }
            onValidChange={ this.updateValid('base') } />

          <ShippingTab
            required
            tabId={ 2 }
            base={ base }
            back={ this.back } 
            onUpdate={ this.onUpdate }
            onSubmit={ this.submitTab( 2 ) }
            onValidChange={ this.updateValid('shipping') } />

          <PlatoonsTab
              tabId={ 3 }
              back={ this.back }
              onSubmit={ this.submitTab( 3 ) }
              onValidChange={ this.updateValid('platoons') } />

          <ModulesTab
            tabId={ 4 }
            base={ base }
            back={ this.back }
            onChange={ this.onCheckboxChange }
            onSubmit={ this.submitTab( 4 ) }
            onValidChange={ this.updateValid('modules') } />

          <SettingsTab
            tabId={ 5 }
            base={ base }
            back={ this.back }
            onUpdate={ this.onUpdate }
            onSubmit={ this.submitTab( 5 ) } />

          <PaymentTab
            cc={ cc }
            tabId={ 6 }
            base={ base }
            back={ this.back }
            terms={ terms }
            discount={ discount }
            register={ this.register }
            onStateUpdate={ this.onStateUpdate } />
        </TabContent>
      </div>
    );
  }
}

const mapStateToProps = ({ login }) => {
  return {
    login: login.current_login
  }
}

const mapDispatchToProps = {
  getBase, getDefaults, registerBase, getCurrentUser, updateBase
}

export default connect( mapStateToProps, mapDispatchToProps )( RegistrationPage );
