import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
// components
import { Modal, ModalHeader, ModalBody, ModalFooter } from 'reactstrap';
import { SaveButton } from 'components/buttons';
// rows
import { PrizeForm } from './PrizeForm';
// functions
import { toast } from 'react-toastify';
// import { makeCancelable } from 'functions/utils';
import { filterUpdates } from 'functions/events';
import { updatePrize, createPrize } from 'store/rewards/prizes/operations';

class PrizeModal extends Component {

  static defaultProps = {
    prize: PropTypes.object,
    login: PropTypes.object,
    isOpen: PropTypes.bool,
    toggle: PropTypes.func,
    onSubmit: PropTypes.func,
    editPicture: PropTypes.func
  }

  static defaultProps = {
    prize: {}
  }

  state = {
    saving: false,
    updates: {}
  }

  componentDidUpdate({ prize }) {
    // clear the state on a new prize
    if ( prize.prize_id !== this.props.prize.prize_id ) {
      this.setState({ updates: {} });
    // however if just the image was updated (not the prize id)
    } else if ( prize.image !== this.props.prize.image && this.state.updates.image ) {
      this.setState({ updates: { 
        ...this.state.updates, 
        image: this.props.prize.image, 
        image_id: this.props.prize.image_id 
      }});
    }
  }

  onUpdate = updates => {
    updates = filterUpdates( this.props.prize, { ...this.state.updates, ...updates } );
    this.setState({ updates });
  }

  onImageEdit = ( e ) => {
    this.props.editPicture( this.props.prize.prize_id )( e );
  }

  // update the prize
  submit = event => {
    event.preventDefault();
    const { prize_id, prize_name } = this.props.prize;

    if ( !prize_name && !this.state.updates.prize_name )
      return toast.error('You must enter a prize name');

    this.setState({ saving: true });

    // handle create or edit
    let promise;
    if ( prize_id )
      promise = this.props.updatePrize( prize_id, this.state.updates );
    else
      promise = this.props.createPrize( { ...this.props.prize, ...this.state.updates } );

    // handle create and update the same
    promise.then( () => {
      this.props.toggle();
      this.setState({ saving: false, updates: {} }) 
    })
    .catch( e => {
      this.setState({ saving: false} );
      toast.error( e.message )
    });
  }

  render() {
    let { updates, saving } = this.state;
    let { prize, login, toggle, isOpen, templates } = this.props;
    const updated = Object.keys( updates ).length > 0;

    prize = { ...prize, ...updates };

    const editing = !!prize.prize_id;

    templates = templates.map( template => ({
      label: template.prize_name, value: template.prize_name, ...template
    }))

    return (
      <Modal isOpen={ isOpen } toggle={ toggle } centered id='PrizeModal'>
        <ModalHeader toggle={ toggle }>{ editing ? 'Edit' : 'Create' } Prize</ModalHeader>
        <form onSubmit={ this.submit }>
          <ModalBody>
            
            <PrizeForm
              prize={ prize }
              login={ login }
              editing={ editing }
              templates={ templates }
              onUpdate={ this.onUpdate } 
              onImageEdit={ this.onImageEdit } />

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
  login: state.login.current_login,
  templates: state.rewards.prizes.templates
});

const mapDispatchToProps = {
  updatePrize, createPrize
};

export default connect( mapStateToProps, mapDispatchToProps )( PrizeModal );
