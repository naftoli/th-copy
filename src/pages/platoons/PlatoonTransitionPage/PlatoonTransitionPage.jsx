import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Callout } from 'components/ui';
import { Step1, Step2, Step3, Step4 } from './steps';
// functions
import { getUsers } from 'store/platoons/platoon_transition';
import { loginStoreChanged } from 'functions/login';
// styles
import './PlatoonTransitionPage.scss';

class PlatoonTransitionPage extends Component {

  state = {
    from: { school_id: false, class_id: false },
    to: { school_id: false, class_id: false },
    soldiers: [],
    selection: [],
    loading: false,
  }

  componentDidMount() { 
    this.setupPage(); 
  }

  componentDidUpdate({ login }) {
    if ( loginStoreChanged( login ) ) this.setupPage();
  }

  setupPage = () => {
    const { code, id } = this.props.login
    if ( code === 'BC' ) this.setState({
      to: { ...this.state.to, school_id: id },
      from: { ...this.state.from, school_id: id }
    });
  }

  // update an id in `to` or `from`
  selectChange = ( section ) => ( id ) => ( option ) => {
    const updated = Object.assign({}, this.state[section], { [id]: option && option.value })
    this.setState({ [section]: updated });
  }

  // get the soldiers we can transition
  getSoldiers = () => {
    this.setState({ loading: true, });
    getUsers( this.state.from )
    .then( soldiers => this.setState({ soldiers, loading: false }) );
  }

  render() {
    const { from, to, soldiers, selection, loading } = this.state;

    return (
      <div id='PlatoonTransitionPage'>
        <Callout title='Platoon Transition Instructions'>
          <p><strong>Platoon Transition allows you to setup a large scale transition for multiple soldiers in all bases your account has access to.</strong></p>
          <p>To setup this transition use steps 1-3 in order to move soldiers from one platoon to another.</p>
          <p>When you have finished seting up the transition you can make it live anytime using step 4.</p>
        </Callout>

        <Step1 { ...from }
          selectChange={ this.selectChange('from') } 
          onSubmit={ this.getSoldiers }
          loading={ loading } />

        <Step2 
          soldiers={ soldiers }
          selection={ selection } />

        <Step3 { ...to } 
          selectChange={ this.selectChange('to') } />

        <Step4 />

        <p className="title">Debug</p>
        <pre>{ JSON.stringify( this.state, null, 2 ) }</pre>
      </div>
    );
  }
}

const mapStateToProps = ({ login }) => ({
  login: login.current_login
})

export default connect( mapStateToProps )( PlatoonTransitionPage );
