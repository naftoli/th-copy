import React, { Component } from 'react';
import PropTypes from 'prop-types';
// components
import { SaveButton } from 'components/buttons';
import { Select } from 'components/inputs';
import { 
  Modal, ModalHeader, ModalBody, ModalFooter,
  Row, Col, Label, Input
} from 'reactstrap';
// functions
import { findOption } from 'functions/selects';
import { filterUpdates } from 'functions/events';

class TaskModal extends Component {

  static propTypes = {
    loading: PropTypes.bool,
    task: PropTypes.object.isRequired,
    isOpen: PropTypes.bool.isRequired,
    toggle: PropTypes.func.isRequired,
    onSubmit: PropTypes.func.isRequired,
    subjects: PropTypes.array.isRequired
  };

  state = { 
    updates: {},
    saving: false,
  };

  componentDidUpdate({ isOpen }) {
    const { task, subjects } = this.props;
    if (
      !this.state.updates.subject_id && 
      !task.subject_id && subjects.length > 0
    ) this.onUpdate({ subject_id: subjects[0].subject_id });

    if ( !isOpen && this.props.isOpen )
      this.setState({ updates: {} });
  }

  // update state
  onUpdate = updates => {
    updates = filterUpdates( this.props.task, { ...this.state.updates, ...updates } );
    this.setState({ updates });
  };

  // event handlers
  onChange = ({ target }) => { this.onUpdate({ [target.name]: target.value }) }
  onSubjectChange = ({ value }) => { this.onUpdate({ subject_id: value }) }
  onSubmit = ( e ) => {
    e.preventDefault();
    let task = { ...this.props.task, ...this.state.updates };
    this.setState({ saving: true });
    // Promisify the submit function and clear the updates when it is successful
    Promise.resolve( this.props.onSubmit( task ) )
    .then( () => {
      this.setState({ updates: {}, saving: false });
      this.toggle();
    })
    .catch( () => this.setState({ saving: false }) );
  }

  toggle = () => this.props.toggle();

  render(){
    let { isOpen, task, subjects, loading, login } = this.props;
    const { updates, saving } = this.state;

    const editing = !!task.achievement_task_id;

    const updated = Object.keys( this.state.updates ).length > 0;
    task = { ...task, ...updates };
    
    // insitutions can only create tasks in their own campaigns.
    if ( ( !task.base || task.base <= 1 ) && login.code === 'INST' )
      subjects = subjects.filter( subject => subject.inst_id === login.id );

    let subjectOptions = subjects.map( subject => ({
      value: subject.subject_id, label: subject.subject_name
    }));
    let campaign = findOption( subjectOptions, task.subject_id );

    return (
      <Modal isOpen={ isOpen } toggle={ this.toggle } centered id='TaskModal'>

        <ModalHeader toggle={ this.toggle }>
          { editing ? 'Edit' : 'Create'} Task
        </ModalHeader>

        <form onSubmit={ this.onSubmit }>

          <ModalBody>
            <Row>
              <Col xs={ 8 }>

                <Label>Campaign</Label>
                <Select id='campaign'
                  value={ campaign }
                  options={ subjectOptions } 
                  isLoading={ loading }
                  onChange={ this.onSubjectChange } />

              </Col>
              <Col xs={ 4 }>

                <Label htmlFor='miles'>Miles</Label>
                <Input id='miles' name='points' value={ task.points || '' }
                  type="number" min="1" max="99" onChange={ this.onChange } required/>
                <div className='invalid-message'>1 to 99 Miles</div>

              </Col>
              <Col xs={ 12 }>
                {/* TODO, limit to 40 characters */}
                <Label htmlFor='task'>Task</Label>
                <Input id='task' name='task' maxLength={ 100 } required
                  pattern='^.{3,100}$' title='3 to 100 characters'
                  value={ task.task || '' } onChange={ this.onChange }  />
                <div className='invalid-message'>Please enter a valid task name</div>

              </Col>
            </Row>
          </ModalBody>

          <ModalFooter>
            <SaveButton 
              saving={ saving }
              text={ editing ? undefined : 'Create Task' } 
              show={ !editing || updated } />
          </ModalFooter>

        </form>
      </Modal>
    );
  }
}

export default TaskModal;
