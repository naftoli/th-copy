import React, { Component } from 'react';
import { connect } from 'react-redux';
import { 
  Modal, ModalHeader, ModalBody,
  ModalFooter,  Row,  Col
} from 'reactstrap';
import { SaveButton } from 'components/buttons/index';
import { MissionTypeSelect } from 'components/selects';
import { 
  NameRow, DobCol, ProfileRow, BasePlatoonRow 
} from '../components';

const initialSoldier = {
  first: '', last: '', first_he: '',
  last_he: '',  gender: 'M'
}

class NewSoldierModal extends Component {

  state = {
    saving: false,
    soldier: { ...initialSoldier },
  }

  componentDidMount() {
    this.setupSoldier();
  }

  toggle = () => {
    this.props.toggle();
    this.setupSoldier();
  }

  // handle updates to the form
  updateSoldier = updates => this.setState({
    soldier: { ...this.state.soldier, ...updates }
  });
  // when an input changes, update the soldier in the state
  onInputChange = ({ target }) =>
    this.updateSoldier({ [target.id]: target.value });
  // only the dob is changed at the moment
  onDateChange = date =>
    this.updateSoldier({
      dob: date && date.format('YYYY-MM-DD HH:mm:ss')
    });
  // selects do not provide a key by default
  onSelectChange = key => option =>
    this.updateSoldier({ [key]: option.value });

  // setup the soldier based on the current login
  setupSoldier = () => {
    const { code, school_id, class_id } = this.props.login;
    if ( code === 'BC' )
      return this.setState({ soldier: { ...initialSoldier, school_id }});
    if ( code === 'TEACHER' )
      return this.setState({ soldier: { ...initialSoldier, school_id, class_id } });
    // reset the soldier by default
    return this.setState({ soldier: { ...initialSoldier } });
  }

  onSubmit = e => {
    e.preventDefault();
    const { mobile_pic } = this.props.image;

    const soldier = { ...this.state.soldier, mobile_pic };
    this.setState({ saving: true });

    Promise.resolve( this.props.onSubmit( soldier ) )
      .then( () => this.setState({ saving: false }) );
  }

  render() {
    const { saving, soldier } = this.state;
    const { login, isOpen, image, editPicture } = this.props;

    return (
      <Modal centered 
        isOpen={ isOpen }
        id='NewSoldierModal'
        toggle={ this.toggle }>
        
        <ModalHeader toggle={ this.toggle }>
          Create Soldier
        </ModalHeader>
        
        <form onSubmit={ this.onSubmit }>
          <ModalBody>

            <ProfileRow
              src={ image.profilePicture }
              gender={ soldier.gender }
              onImageClick={ editPicture }
              onGenderChange={ this.onInputChange } />

            <NameRow
              soldier={ soldier }
              onChange={ this.onInputChange } />

            <Row>
              <DobCol
                dob={ soldier.dob }
                onChange={ this.onDateChange } />

              <Col sm='6'>
                <label htmlFor='mission_type'>Mission Type</label>
                <MissionTypeSelect
                  required id='mission_type'
                  gender={ soldier.gender }
                  value={ soldier.school_type_id }
                  onChange={ this.onSelectChange( 'school_type_id' ) } />
              </Col>
            </Row>

            <BasePlatoonRow
              required
              code={ login.code }
              classId={ soldier.class_id }
              schoolId={ soldier.school_id }
              onChange={ this.onSelectChange } />

          </ModalBody>

          <ModalFooter>
            <SaveButton show saving={ saving } />
          </ModalFooter>
        </form>
      </Modal>
    )
  }
}

const mapStateToProps = ({ login }) => ({
  login: login.current_login
})

export default connect(
  mapStateToProps, // mapDispatchToProps
)( NewSoldierModal )
