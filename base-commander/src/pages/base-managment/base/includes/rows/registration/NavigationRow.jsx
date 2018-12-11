import React, { Component } from 'react';
// components
import { Row, Col, Button } from 'reactstrap';
import { FontAwesome } from 'components/ui/Icons';

export class NavigationRow extends Component {

  render () {

    const { back, next, nextDisabled, children } = this.props;

    let nextFunction;
    if ( typeof next === 'function' )
      nextFunction = next;
    
    return (
      <Row className='NavigationRow'>
        <Col sm={ 6 }>
          { back &&
            <Button color='primary' onClick={ back }>
              <FontAwesome icon='arrow-left' /> Back
            </Button>
          }
        </Col>

        <Col sm={ 6 }>
          { next &&
            <Button color='primary' onClick={ nextFunction } disabled={ nextDisabled }>
              Next Step <FontAwesome icon='arrow-right' />
            </Button>
          }

          { children }
        
        </Col>
      </Row>
    );
  }
}
