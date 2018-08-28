import React, { Component } from 'react';
import PropTypes from 'prop-types';
// components
import { Row, Col } from 'reactstrap';
import { Link } from 'react-router-dom';
import { FontAwesome, Spinner, ProfilePicture } from 'components/ui';
// functions
import { toast } from 'react-toastify';
// constants

const Birthday = ({ profilePicture, user_id, name, class_id, platoon }) => {
  return (
    <Row className='Birthday'>
      <Col xs={3} xl={2}>
        <ProfilePicture src={ profilePicture } />
      </Col>
      <Col xs={9}>
        <Link to={`/bm/users/${user_id}`}>{ name }</Link><br/>
        <Link to={`/bm/platoons/${class_id}`}>{ platoon }</Link>
      </Col>
    </Row>
  )
}

export class BirthdaysWidget extends Component {

  static propTypes = {
    refresh: PropTypes.func.isRequired,
    // birthdays: PropTypes.object.isRequired
  }

  static defaultProps = {
    birthdays: {},
  }

  componentDidMount() {
    this.props.refresh()
    .catch( error => toast.error( error.message ) );
  }

  render() {
    let { birthdays } = this.props;

    let content;

    if ( birthdays === false ) {
      content = <Spinner size={5} />
    } else if ( Object.keys( birthdays ).length === 0 ) {
      content = (
        <div className='no-data'>
          <FontAwesome icon='birthday-cake' />
          <p>No Upcoming Birthdays</p>
        </div>
      );
    } else {
      content = (
        <div className='birthdays'>
          { Object.entries( birthdays ).map( ( date, index ) => ( // for each date
            <div className='date' key={ index }>
              <h3>{ date[0] }</h3>
              { date[1].map( ( soldier, index ) => <Birthday { ...soldier } key={ index } /> ) }
            </div>
          )) }
        </div>
      )
    }

    return (
      <Col xs={12} sm={6}>
        <div id='BirthdaysWidget' className='widget'>
          <h2>Upcoming Birthdays</h2>
          { content }
        </div>
      </Col>
    );
  }
}
