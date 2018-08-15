import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { FontAwesome } from 'components/ui';
import { Row, Col, Input, ButtonGroup, Button } from 'reactstrap';
// functions
import { toast } from 'react-toastify';
import { updateStaffPosition, removeStaffPosition } from 'store/staff/operations';

class PositionRow extends Component {

  state = { position: '' }

  componentDidMount() { this.setState({ position: this.props.position }); }

  componentDidUpdate({ position }) {
    if ( this.props.position !== position )
      this.setState({ position });
  }

  onPositionChanged = ({ target }) => {
    this.setState({ position: target.value })
  }

  update = () => {
    const { admin_id, auth, id } = this.props;
    updateStaffPosition( admin_id, auth, id, this.state.position )
    .then( () => toast.info( 'Position Updated' ) )
    .catch( error => toast.error( error.message ) );
  }

  remove = () => {
    const { admin_id, auth, id } = this.props;
    this.props.removeStaffPosition( admin_id, auth, id )
  }

  render() {
    const { role, base, platoon } = this.props;
    const { position } = this.state;
    return (
      <div className='PositionRow'>
        <Row>
          <Col xs={6} sm={4}>
            <strong>Base</strong>
            <p>{ base }</p>
          </Col>
          <Col xs={6} sm={4}>
            <strong>Platoon</strong>
            <p>{ platoon }</p>
          </Col>
          <Col xs={6} sm={4}>
            <strong>Role</strong>
            <p>{ role }</p>
          </Col>
          <Col xs={6}>
            <label>Position</label>
            <Input value={ position } onChange={ this.onPositionChanged }/>
          </Col>
          <Col xs={12} sm={6}>
            <ButtonGroup>
              <Button color='primary' onClick={this.update}>
                <FontAwesome icon='save'/> Update
              </Button>
              <Button color='danger' onClick={this.remove}>
                <FontAwesome icon='trash'/> Remove
              </Button>
            </ButtonGroup>
          </Col>
        </Row>
      </div>
    );
  }
}

export default connect( null, { removeStaffPosition })( PositionRow );
