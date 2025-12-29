import React, { useState, useEffect } from 'react';
import PropTypes from 'prop-types';
// components
import { SaveButton } from 'components/buttons';
import { Checkbox, RewardSubjectSelect } from 'components/inputs';
import {
  Modal, ModalHeader, ModalBody, ModalFooter,
  Row, Col, Label, Input, FormFeedback
} from 'reactstrap';
// functions
import { filterUpdates, onInputChange } from 'functions/events';
import { isHQ } from 'functions/login';

const TaskModal = (props) => {
  const { task, isOpen, toggle, onSubmit: submitTask, subjects, login } = props;

  const [updates, setUpdates] = useState({});
  const [saving, setSaving] = useState(false);
  const [touched, setTouched] = useState({});

  const onBlur = (field) => () => setTouched(prev => ({ ...prev, [field]: true }));

  // Initialize
  useEffect(() => {
    if (isOpen) {
      // Auto select subject if not set
      if (!task.subject_id && subjects.length > 0) {
        setUpdates({ subject_id: subjects[0].subject_id });
      } else {
        setUpdates({});
      }
      setTouched({});
    }
  }, [isOpen, task.subject_id, subjects]);

  const handleUpdate = (newUpdates) => {
    const filtered = filterUpdates(task, { ...updates, ...newUpdates });
    setUpdates(filtered);
  };

  const onChange = (e) => {
    const { name, value, type, checked } = e.target;
    if (type === 'checkbox') {
      handleUpdate({ [name]: checked });
    } else {
      handleUpdate({ [name]: value });
    }
  };

  const onSubjectChange = ({ value }) => handleUpdate({ subject_id: value });

  const handleSubmit = (e) => {
    e.preventDefault();
    const finalTask = { ...task, ...updates };
    setSaving(true);

    Promise.resolve(submitTask(finalTask))
      .then(() => {
        setSaving(false);
        setUpdates({});
        toggle();
      })
      .catch(() => setSaving(false));
  };

  const editing = !!task.achievement_task_id;
  const currentTask = { ...task, ...updates };
  const updated = Object.keys(updates).length > 0;

  // Permissions
  let filter;
  if ((!currentTask.base || currentTask.base <= 1) && !isHQ(login.code)) {
    filter = subject => subject.inst_id === 0 || subject.inst_id === login.inst_id;
  }

  // Validation
  const isValidTaskName = /^.{3,75}$/.test(currentTask.task || '');
  const isValidMiles = currentTask.points >= 1 && currentTask.points <= 1000;

  return (
    <Modal isOpen={isOpen} toggle={toggle} centered id='TaskModal'>
      <ModalHeader toggle={toggle}>
        {editing ? 'Edit' : 'Create'} Task
      </ModalHeader>

      <form onSubmit={handleSubmit}>
        <ModalBody>
          <Row>
            <Col xs={8}>
              <Label>Campaign</Label>
              <RewardSubjectSelect
                filter={filter}
                value={currentTask.subject_id}
                onChange={onSubjectChange} />
            </Col>
            <Col xs={4}>
              <Label htmlFor='miles'>Miles</Label>
              <Input id='miles' name='points' value={currentTask.points || ''}
                type='number' min='1' max='1000' onChange={onChange} required
                onBlur={onBlur('points')}
                invalid={touched.points && !isValidMiles}
              />
              <FormFeedback>1 to 1,000 Miles</FormFeedback>
            </Col>

            <Col xs={12}>
              <Checkbox
                id='auction_only_points'
                name='auction_only_points'
                checked={!!currentTask.auction_only_points}
                onChange={onChange}
              />
              <Label htmlFor='auction_only_points'>Auction Only Miles</Label>
            </Col>

            <Col xs={12}>
              {/* TODO, limit to 40 characters */}
              <Label htmlFor='task'>Task</Label>
              <Input id='task' name='task' maxLength={75} required
                // pattern='^.{3,75}$' title='3 to 75 characters' // Native validation
                value={currentTask.task || ''} onChange={onChange}
                onBlur={onBlur('task')}
                invalid={touched.task && !isValidTaskName}
              />
              <FormFeedback>Please enter a valid task name (3-75 chars)</FormFeedback>
            </Col>
          </Row>
        </ModalBody>

        <ModalFooter>
          <SaveButton
            saving={saving}
            text={editing ? undefined : 'Create Task'}
            show={!editing || updated} />
        </ModalFooter>

      </form>
    </Modal>
  );
};

TaskModal.propTypes = {
  task: PropTypes.object.isRequired,
  isOpen: PropTypes.bool.isRequired,
  toggle: PropTypes.func.isRequired,
  onSubmit: PropTypes.func.isRequired,
  subjects: PropTypes.array.isRequired
};

export default TaskModal;
