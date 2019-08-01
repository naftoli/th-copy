import React, { Component } from 'react';
// components
import { Row, Col, Button } from 'reactstrap';
import { BaseSelect, PlatoonSelect } from 'components/inputs';
import { ButtonBar, FontAwesome } from 'components/ui';
import { connect } from 'react-redux';
import { addBase } from 'store/base/bases/operations';

// const Step3 = ({ 
//   school_id, class_id, selectChange, 
//   selection, move, discharge
// }) => {
class Step3 extends Component {

  componentDidMount(){
    if ( this.props.bases.length ) {
      this.props.addBase( 612 );      
    }
  }

  // render page
  render() {
    const { school_id, class_id, selectChange, selection, move } = this.props;

    return (
      <div id='step-3'>
        <p className="title">Step 3: Select Transition for { selection.length } Soldiers</p>
        <b>Please Note: The transition WILL NOT TAKE EFFECT until you click the BLUE BUTTON IN STEP 4, AFTER CLICKING ON THE BLUE BUTTON IN THIS STEP</b>
        <Row>
          <Col sm={6} xl={4}>
            <label>To Base</label>
            <BaseSelect value={ school_id } fetchAll 
              onChange={ selectChange('school_id') } />
          </Col>
          <Col sm={6} xl={4}>
            <label>To Platoon</label>
            <PlatoonSelect value={ class_id } schoolId={ school_id } 
              onChange={ selectChange('class_id') } />
          </Col>
          <Col sm={12} xl={4}>
            <ButtonBar>
              <Button color='primary' onClick={ move }>
                <FontAwesome icon="exchange-alt" />{' '}
                Transition (Move) Soldiers
              </Button>
              {/* <Button color='danger' onClick={ discharge }>
                <FontAwesome icon="trash-alt" />{' '}
                Discharge (Delete) Soldiers 
              </Button> */}
            </ButtonBar>
          </Col>
        </Row>
      </div>
    );
  }
}

const mapStateToProps = ( { base } ) => ({
  ...base.bases,
})

export default connect( mapStateToProps, { addBase } )( Step3 );
