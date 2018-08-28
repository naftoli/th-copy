import React, { Component } from 'react';
import PropTypes from 'prop-types';
// components
import { Row, Col } from 'reactstrap';
import { Link } from 'react-router-dom';
import { FontAwesome, Spinner, ProfilePicture } from 'components/ui';
// functions
import { toast } from 'react-toastify';
// constants

const Soldier = ({ profilePicture, user_id, name, rank_ord, class_id, platoon }) => {
  return (
    <Row className='Soldier'>
      <Col xs={3} xl={2}>
        <ProfilePicture src={ profilePicture } rank={ rank_ord } />
      </Col>
      <Col xs={9} xl={10}>
        <Link to={`/bm/users/${user_id}`}>{ name }</Link><br/>
        <Link to={`/bm/platoons/${class_id}`}>{ platoon }</Link>
      </Col>
    </Row>
  )
}

export class PromotionsWidget extends Component {

  static propTypes = {
    refresh: PropTypes.func.isRequired,
    // promotions: PropTypes.object.isRequired
  }

  static defaultProps = {
    promotions: {},
  }

  componentDidMount() {
    this.props.refresh()
    .catch( error => toast.error( error.message ) );
  }

  render() {
    let { promotions } = this.props;

    let content;

    if ( promotions === false ) {
      content = <Spinner size={5} />
    } else if ( Object.keys( promotions ).length === 0 ) {
      content = (
        <div className='no-data'>
          <FontAwesome icon='medal' />
          <p>No Recent Promotions</p>
        </div>
      );
    } else {
      content = (
        <div className='promotions'>
          { Object.entries( promotions ).map( ( date, index ) => ( // for each date
            <div className='date' key={ index }>
              <h3>{ date[0] }</h3>
              { date[1].map( ( soldier, index ) => <Soldier { ...soldier } key={ index } /> ) }
            </div>
          )) }
        </div>
      )
    }

    return (
      <Col xs={12} sm={6}>
        <div id='PromotionsWidget' className='widget'>
          <h2>Recent Promotions</h2>
          { content }
        </div>
      </Col>
    );
  }
}
