import React, { Component } from 'react';
import PropTypes from 'prop-types';
// components
import { Col } from 'reactstrap';
import { Link } from 'react-router-dom';
import { NumberDisplay } from 'components/ui';
// functions
import { toast } from 'react-toastify';
// constants

export class RegistrationWidget extends Component {

  static propTypes = {
    refresh: PropTypes.func.isRequired,
    data: PropTypes.object.isRequired,
    login: PropTypes.object.isRequired
  }

  componentDidMount() {
    this.props.refresh()
    .catch( error => toast.error( error.message ) );
  }

  render() {
    const { login, data } = this.props;
    const { year, status, soldiers, total, reg_open } = data;
    // x of y soldiers registered
    let soldier_status = 'Loading...';
    if ( status ) {
      soldier_status = <span><NumberDisplay value={ total || 0 } /> of <NumberDisplay value={ soldiers || 0 } /></span>;
    }

    return (
      <Col xs={12} sm={6} xl={4}>
        <div id='RegistrationWidget' className='widget'>
          <h2>Registration { year || 5779 }</h2>

          <div>
            <p>
              <strong>Base Status:</strong> 
              { reg_open && <Link to='/register'>{ status || 'Loading...' }</Link> }
              { !reg_open && <span>{ status || 'Loading...' }</span> }
            </p>
            <p>
              <strong>Soldiers Registered:</strong>
              { login.code === 'BC' &&
                <Link to='/bm/soldiers/registration'>{ soldier_status }</Link>
              }
              { login.code !== 'BC' && <span>{ soldier_status }</span> }
            </p>
          </div>
        </div>
      </Col>
    );
  }
}
