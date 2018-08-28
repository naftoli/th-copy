import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Col } from 'reactstrap';
import { Link } from 'react-router-dom';
// functions
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { getRegistration } from 'store/home/operations';
// constants
import { LEGACY_URL } from 'components/constants';

class RegistrationWidget extends Component {

  componentDidMount() {
    setTitle( 'Home Page' );
    this.props.getRegistration()
    .catch( error => toast.error( error.message ) );
  }

  render() {
    const { login, data } = this.props;
    const { year, status, soldiers, total, reg_open } = data;
    // x of y soldiers registered
    let soldier_status = status ? `${ total || 0 } of ${ soldiers || 0 }` : 'Loading...';

    return (
      <Col xs={12} sm={6} xl={4}>
        <div id='RegistrationWidget' className='widget'>
          <h2>Registration { year || 5779 }</h2>

          <div>
            <p>
              <strong>Base Status:</strong> 
              { reg_open && <a href={`${LEGACY_URL}/registration.php`}>{ status || 'Loading...' }</a> }
              { !reg_open && <span>{ status || 'Loading...' }</span> }
            </p>
            <p>
              <strong>Soldiers Registered:</strong>
              { login.code === 'BC' &&
                <Link to='/bm/users/registration'>{ soldier_status }</Link>
              }
              { login.code !== 'BC' && <span>{ soldier_status }</span> }
            </p>
          </div>
        </div>
      </Col>
    );
  }
}

const mapStateToProps = ({ login, home }) => ({
  login: login.current_login,
  data: home.registration
})

export default connect( mapStateToProps, { getRegistration } )( RegistrationWidget );
