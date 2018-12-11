import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Link } from 'react-router-dom';
import { ButtonBar, FontAwesome } from 'components/ui';
import { Row, Col, Input, Button } from 'reactstrap';
// functions
import { toast } from 'react-toastify';
import { updateAuth, removeAuth } from 'store/base/staff/operations';

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
    updateAuth({ admin_id, auth, id, ...this.state })
    .then( () => toast.info( 'Position Updated' ) )
    .catch( error => toast.error( error.message ) );
  }

  remove = () => {
    const { admin_id, auth, id } = this.props;
    this.props.removeAuth({ admin_id, auth, id });
  }

  render() {
    let { role, base, platoon, auth, id } = this.props;
    const { position } = this.state;

    if ( auth === 'class' ){
      platoon = <Link to={`/bm/platoons/${id}`}>{ platoon }</Link>;
    }
    
    return (
      <div className='PositionRow'>
        <Row>
          <Col xs={6} sm={4}>
            <strong>Role</strong>
            <p>{ role }</p>
          </Col>
          <Col xs={6} sm={4}>
            <strong>Base</strong>
            <p>{ base }</p>
          </Col>
          <Col xs={6} sm={4}>
            <strong>Platoon</strong>
            <p>{ platoon }</p>
          </Col>
          <Col xs={6}>
            <label>Position</label>
            <Input value={ position } onChange={ this.onPositionChanged }/>
          </Col>
          <Col xs={12} sm={6}>
            <ButtonBar>
              <Button color='primary' onClick={this.update}>
                <FontAwesome icon='save'/> Update
              </Button>
              <Button color='danger' onClick={this.remove}>
                <FontAwesome icon='trash'/> Remove
              </Button>
            </ButtonBar>
          </Col>
        </Row>
      </div>
    );
  }
}

export default connect( null, { removeAuth })( PositionRow );
