import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
// components
import {
  Modal, ModalHeader, ModalBody, ModalFooter, 
  Row, Col
} from 'reactstrap';
import { SaveButton } from 'components/buttons';
import { StorePrize } from 'components/ui';
// rows
import { PrizeRow } from './rows/PrizeRow';
// functions
import { toast } from 'react-toastify';
import { makeCancelable } from 'functions/utils';
// import { isAdmin, isBC } from 'functions/login';
import { filterUpdates } from 'functions/events';
import { getPrize, updatePrize } from 'store/rewards/prizes/operations';

class PrizeModal extends Component {

  static defaultProps = {
    prize: PropTypes.object,
    login: PropTypes.object,
    isOpen: PropTypes.bool,
    toggle: PropTypes.func,
    onSubmit: PropTypes.func
  }

  static defaultProps = {
    prize: {}
  }

  state = {
    saving: false,
    updates: {}
  }

  componentDidUpdate({ prize }) {
    if ( prize !== this.props.prize )
      this.setState({ updates: {} });
  }

  onUpdate = updates => {
    updates = filterUpdates( this.props.prize, { ...this.state.updates, ...updates } );
    this.setState({ updates });
  }

  // update the prize
  submit = event => {
    event.preventDefault();

    const { prize_id } = this.props.prize;

    this.setState({ saving: true });

    let promise;
    if ( prize_id )
      promise = this.props.updatePrize( prize_id, this.state.updates );
    else {
      debugger;
    }
    
    promise.then( () => {
      this.props.toggle();
      this.setState({ saving: false, updates: {} }) 
    })
    .catch( e => toast.error( e.message ) );
  }

  render() {
    let { updates, saving } = this.state;
    let { prize, login, toggle, isOpen } = this.props;
    const updated = Object.keys( updates ).length > 0;

    prize = { ...prize, ...updates };

    return (
      <Modal isOpen={ isOpen } toggle={ toggle } centered id='PrizeModal'>
        <ModalHeader toggle={ toggle }>Prize</ModalHeader>
        <form onSubmit={ this.submit }>
          <ModalBody>
            
            <PrizeRow
              { ...prize }
              login={ login } 
              onUpdate={ this.onUpdate } />

            {/* <pre>
              { JSON.stringify( this.state, null, 2 ) }
            </pre> */}

          </ModalBody>
          <ModalFooter>

            <SaveButton show={ !prize.prize_id || updated } saving={ saving } />
          
          </ModalFooter>
        </form>
      </Modal>
    );
  }
}

const mapStateToProps = ( state ) => ({
  login: state.login.current_login
});

const mapDispatchToProps = {
  getPrize, updatePrize
};

export default connect( mapStateToProps, mapDispatchToProps )( PrizeModal );
