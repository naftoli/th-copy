import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Callout } from 'components/ui';
import { Step1, Step2, Step3, Deploy } from './steps';
// functions
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { 
  getUsers, changePlatoon, removeFromBase, transitionPlatoons 
} from 'store/platoons/platoon_transition';
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
    setTitle( 'Platoon Transition' );
    this.setupPage(); 
  }

  setupPage = () => {
    const { code, id } = this.props.login
    if ( code === 'BC' ) this.setState({
      to: { ...this.state.to, school_id: id },
      from: { ...this.state.from, school_id: id }
    }, this.getSoldiers ); // set the state and get the first round of soldiers
  }

  // update an id in `to` or `from`
  selectChange = ( section ) => ( id ) => ( option ) => {
    const updated = Object.assign({}, this.state[section], { [id]: option && option.value })
    this.setState({ [section]: updated });
  }

  // get the soldiers we can transition
  getSoldiers = () => {
    this.setState({ loading: true, selection: [] });
    getUsers( this.state.from )
    .then( soldiers => this.setState({ soldiers, loading: false }) )
    .catch( error => {
      this.setState({ soldiers: [], loading: false });
      toast.error( error.message );
    });
  }
  // Move to new base
  move = () => {
    changePlatoon({ user_ids: this.state.selection, ...this.state.to })
    .then( this.getSoldiers )
    .catch( error => toast.error( error.message ) );
  }
  // Discharge from Tzivos Hashem
  discharge = () => {
    if ( window.confirm('Are you sure you want to do this? Once you deploy this transition these soldiers will be deleted and cannot be recovered.') ) {
      removeFromBase( this.state.selection )
      .then( this.getSoldiers )
      .catch( error => toast.error( error.message ) );
    }
  }
  // Deploy the transition
  transition = () => {
    transitionPlatoons()
    .then( ({ rowCount }) => toast.info( `${ Math.floor( rowCount / 2 ) } Soldiers Transitioned` ) )
    .then( this.getSoldiers )
    .catch( error => toast.error( error.message ) );
  }

  // Selection functions
  updateSelection = selection => this.setState({ selection });

  render() {
    const { login } = this.props;
    const { from, to, soldiers, selection, loading } = this.state;

    return (
      <div id='PlatoonTransitionPage'>
        <Callout title='Platoon Transition Instructions'>
          <p><strong>Platoon Transition allows you to setup a large scale transition for multiple soldiers in all bases your account has access to.</strong></p>
          <p>To setup this transition use steps 1-3 in order to move soldiers from one platoon to another.</p>
          <p><strong>Press the button below to deploy all your transitions whenever you like!</strong></p>
        </Callout>

        <Deploy onDeploy={ this.transition } />

        <Step1 { ...from }
          login={ login }
          loading={ loading }
          onSubmit={ this.getSoldiers }
          selectChange={ this.selectChange('from') } />

        <Step2 
          soldiers={ soldiers }
          selection={ selection } 
          updateSelection={ this.updateSelection }
          loading={ loading } />

        <Step3 { ...to } 
          selectChange={ this.selectChange('to') }
          selection={ selection } 
          move={ this.move }
          discharge={ this.discharge } />

      </div>
    );
  }
}

const mapStateToProps = ({ login }) => ({
  login: login.current_login
})

export default connect( mapStateToProps )( PlatoonTransitionPage );
