import React, { Component, Fragment } from 'react';
import API from "../../../../../api/api";
// components
import { Input, Row, Col, TabPane } from 'reactstrap';
import { Callout } from 'components/ui';
import { NavigationRow } from '../rows/registration/NavigationRow';
import { Form, Radio } from "components/inputs";

export class PlatoonsTab extends Component {

  state = { platoons: [] }

  componentDidMount() {
    API.get( `/core/platoons` )
      .then(platoons => {
        this.setState({ platoons: platoons })
      })
  }

  setValue = (id, key, value) => {
    const platoons = this.state.platoons.map(platoon => {
      if (platoon.class_id !== id) return platoon
      else {
        const pl = {}
        if (key.includes('updated')) pl['updated'] = value
        else pl[key] = value
        return {...platoon, ...pl}
      }
    })
    this.setState({ platoons })
  }

  save = elem => {
    elem.preventDefault()
    for (let platoon of this.state.platoons) {
      API.patch('/core/platoons?id=' + platoon.class_id, platoon)
    }
    this.props.onSubmit( elem )
  }

  render() {
    const { tabId, back, onValidChange } = this.props
    const gradeTitleCss = {
      borderBottom: '1px solid #3e6dc4',
      fontSize: '1.2em',
      paddingLeft: '0.5em',
      marginTop: '0.5em',
      marginBottom: '0.5rem',
      fontFamily: 'Arial,Helvetica,sans-serif'
    }

    return (
      <TabPane tabId={ tabId }>

        <Form
          validateAfterSubmit
          onSubmit={ this.save }
          onValidChange={ onValidChange }>
          <p className='title'>
            Platoons
          </p>

          <Callout color="warning">
            Please review the platoons that you have and update the teachers information so that we can be in touch
            with them throughout the year.
          </Callout>

          {this.state.platoons.map(platoon =>
            <Fragment>
              <p style={ gradeTitleCss }>
                { platoon.class_grade }
                { platoon.class_sub ? '-' + platoon.class_sub : '' }
              </p>
              <Row>
                <Col sm={6}>
                  <label>Teacher Name</label>
                  <Input required name='class_teacher' defaultValue={ platoon.teacher } onChange={ e => this.setValue(platoon.class_id, e.target.name, e.target.value) } />
                </Col>

                <Col sm={6}>
                  <label>Teacher Phone</label>
                  <Input required name='cell' type='tel' defaultValue={ platoon.cell } onChange={ e => this.setValue(platoon.class_id, 'cell', e.target.value) }
                         title='separate multiple email addresses with a comma' // one or more valid phone numbers
                    //pattern='^(((\+[0-9]{1,3}[0-9 ]{9,})|((?:1 |\()?[0-9]{3}(?: |\) |-)?[0-9]{3}(?: |-)?[0-9]{4}))[,;])*((\+[0-9]{1,3}[0-9 ]{9,})|((?:1 |\()?[0-9]{3}(?: |\) |-)?[0-9]{3}(?: |-)?[0-9]{4}))$'
                  />
                  <div className='invalid-message'>separate multiple email addresses with a comma</div>
                </Col>

                <Col sm={6}>
                  <label>Teacher E-Mail</label>
                  <Input required name='email' defaultValue={ platoon.email } onChange={ e => this.setValue(platoon.class_id, e.target.name, e.target.value) }
                         title='1 or more valid E-mail addresses (, or ; seperated)'
                    // one or more emails with a , or ; at the end
                         pattern='^(\s?[^\s,]+@[^\s,]+\.[^\s,]+\s?[,;])*(\s?[^\s,]+@[^\s,]+\.[^\s,]+)$'
                  />
                  <div className='invalid-message'>1 or more valid E-mail addresses (, or ; seperated)</div>
                </Col>

                <Col sm={6}>
                  <label>Updated for 5783</label><br />
                  <Radio
                    required
                    name={ platoon.class_id + '_updated' }
                    value='1'
                    className='form-check-input'
                    checked={ parseInt(platoon.updated, 10) === 1 }
                    onChange={ e => this.setValue(platoon.class_id, e.target.name, e.target.value) }> This Teacher IS NOW BEING UPDATED for 5783 </Radio><br />
                  <Radio
                    required
                    name={ platoon.class_id + '_updated' }
                    value='0'
                    className='form-check-input'
                    checked={ parseInt(platoon.updated, 10) === 0 }
                    onChange={ e => this.setValue(platoon.class_id, e.target.name, e.target.value) }> This Teacher IS NOT YET UPDATED for 5783 </Radio>
                </Col>
              </Row>
            </Fragment>
          )}

          <NavigationRow back={ back } next />
        </Form>
      </TabPane>
    )
  }
}

