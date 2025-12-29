import React, { useState, useEffect } from 'react';
import PropTypes from 'prop-types';
// components
import MissionList from './MissionList';
import { SaveButton } from 'components/buttons';
import { Modal, ModalHeader, ModalBody, ModalFooter } from 'reactstrap';
// functions
import { arrayMove } from 'react-sortable-hoc';

const CustomizeModal = ({
  type = 'daily',
  isOpen,
  toggle,
  missions: propsMissions,
  customizeGrid
}) => {
  const [missions, setMissions] = useState([]);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    setMissions(propsMissions);
  }, [isOpen, propsMissions]);

  const toggleMission = grid_id => e => {
    // update the mission to have the disabled match the oposite of the checked status from the input
    const updatedMissions = missions.map(
      mission => mission.grid_id === grid_id ? { ...mission, disabled: !e.target.checked } : mission
    );
    setMissions(updatedMissions); // and update the state
  }

  const onSubmit = e => {
    e.preventDefault();
    setSaving(true);

    Promise.resolve(customizeGrid(missions))
      .then(() => setSaving(false));
  }

  // sort the items
  const onSortEnd = ({ oldIndex, newIndex }) => {
    setMissions(prev => arrayMove(prev, oldIndex, newIndex));
  };

  return (
    <Modal isOpen={isOpen} toggle={toggle} centered id='CustomizeModal'>

      <ModalHeader toggle={toggle}>
        Customize {type} grid
      </ModalHeader>

      <form onSubmit={onSubmit}>

        <ModalBody>

          <MissionList
            useDragHandle
            missions={missions}
            onSortEnd={onSortEnd}
            toggleMission={toggleMission} />

        </ModalBody>

        <ModalFooter>

          <SaveButton saving={saving} />

        </ModalFooter>

      </form>
    </Modal>
  );
}

CustomizeModal.propTypes = {
  type: PropTypes.string,
  isOpen: PropTypes.bool.isRequired,
  toggle: PropTypes.func.isRequired,
  missions: PropTypes.array.isRequired,
};

export default CustomizeModal;
