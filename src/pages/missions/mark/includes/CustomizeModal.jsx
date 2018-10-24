import React, { Component } from 'react';
import PropTypes from 'prop-types';
// components
import MissionList from './MissionList';
import { SaveButton } from 'components/buttons';
import { Modal, ModalHeader, ModalBody, ModalFooter } from 'reactstrap';
// functions
import { arrayMove } from 'react-sortable-hoc';

class CustomizeModal extends Component {

  static propTypes = {
    type: PropTypes.string,
    isOpen: PropTypes.bool.isRequired,
    toggle: PropTypes.func.isRequired,
    missions: PropTypes.array.isRequired,
  };

  static defaultProps = {
    type: 'daily'
  }

  state = {
    missions: [],
    saving: false
  };

  componentDidUpdate({ isOpen }){
    if ( isOpen !== this.props.isOpen )
      this.setState({ missions: this.props.missions });
  }

  toggleMission = grid_id => e => {
    // update the mission to have the disabled match the oposite of the checked status from the input
    const missions = this.state.missions.map( 
      mission => mission.grid_id === grid_id ? { ...mission, disabled: !e.target.checked } : mission
    );
    this.setState({ missions }); // and update the state
  }

  onSubmit = e => {
    e.preventDefault();
    this.setState({ saving: true });

    Promise.resolve( this.props.customizeGrid( this.state.missions ) )
    .then( () => this.setState({ saving: false }) );
  }
  // sort the items
  onSortEnd = ({oldIndex, newIndex}) => {
    this.setState({
      missions: arrayMove( this.state.missions, oldIndex, newIndex),
    });
  };

  render(){
    let { isOpen, toggle, type } = this.props;
    let { missions, saving } = this.state;

    return (
      <Modal isOpen={ isOpen } toggle={ toggle } centered id='CustomizeModal'>

        <ModalHeader toggle={ toggle }>
          Customize { type } grid
        </ModalHeader>

        <form onSubmit={ this.onSubmit }>

          <ModalBody>

            <MissionList
              useDragHandle
              missions={ missions }
              onSortEnd={this.onSortEnd} 
              toggleMission={ this.toggleMission } />

          </ModalBody>

          <ModalFooter>

            <SaveButton saving={ saving } />

          </ModalFooter>

        </form>
      </Modal>
    );
  }
}

export default CustomizeModal;
