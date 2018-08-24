import React, { Component } from 'react';
import PropTypes  from 'prop-types';
// Components
import Cards from 'react-credit-cards';
import { FontAwesome, InlineSync } from 'components/ui';
import { Input, Row, Col, Label, Button } from 'reactstrap';
// Functions
import Payment from 'payment';
import classnames from 'classnames';
// styles
import './CCForm.scss';

class CCForm extends Component {

  static propTypes = {
    onChange: PropTypes.func.isRequired,
    value: PropTypes.object.isRequired,
    show: PropTypes.bool,
    onSubmit: PropTypes.func
  }

  static defaultProps = {
    show: true,
    value: {}
  }

  state = { focused: '', loading: false };
  formRef = React.createRef();

  componentDidMount() {
    const form = this.formRef.current;
    if ( form ) {
      Payment.formatCardNumber( form.querySelector('input[name="number"]'), 16 );
      Payment.formatCardExpiry( form.querySelector('input[name="expiry"]') );
      Payment.formatCardCVC( form.querySelector('input[name="cvc"]') );
    }
  }

  handleInputFocus = ({ target }) => {
    this.setState({ focused: target.name });
  };
  // clean up the input as it is entered
  handleInputChange = ({ target }) => {
    let { value, onChange } = this.props;
    // update the correct section
    value[target.name] = target.value;
    
    onChange( value );
  };

  submit = ( e ) => {
    e && e.preventDefault();
    // only submit if we are loading
    if ( !this.state.loading ) {
      this.setState({ loading: true });
      Promise.resolve( this.props.onSubmit() ) // support promises and non-promises
      // if we do not remount the component on adding a card ( remove the loading screen ) then add this back.
      // .then(() => this.setState({ loading: false }))
      .catch(() => this.setState({ loading: false }));
    }
  }
  
  render() {
    const { value, onSubmit } = this.props;
    const { loading } = this.state;
    const { name, number, expiry, cvc } = value;

    const inputProps = {
      onChange: this.handleInputChange,
      onFocus: this.handleInputFocus,
      required: !!onSubmit
    }
    const classNames = classnames('CCForm', {
      'show': this.props.show,
      'has-add': !!onSubmit
    });
    // get the text for the button
    let buttonText = <span><FontAwesome icon='plus'/> Add</span>;
    if ( loading ) buttonText = <InlineSync loading />;

    return (
      <div className={ classNames } ref={ this.formRef }>
        <form onSubmit={ this.submit } autoComplete='on'>
          <Row>
            <Col xs='12'>
              <Label>Card Number</Label>
              <Input type='tel' name='number' value={ number || '' } {...inputProps}
                placeholder='4725 9182 9976 7854' autocompletetype="cc-number" />
            </Col>
            <Col xs={ 7 }>
              <Label>Expiration</Label>
              <Input type='tel' name='expiry' value={ expiry || '' } {...inputProps}
                placeholder='MM / YY' autocompletetype="cc-exp" />
            </Col>
            <Col xs={ 5 }>
              <Label>CVC</Label>
              <Input type='tel' name='cvc' value={ cvc || '' } placeholder='CVC' {...inputProps}
                autocompletetype="cc-csc" autoComplete="off" />
            </Col>
            { onSubmit &&
              <Col xs={ 12 } className='save'>
                <Button color='primary' onFocus={ this.handleInputFocus } name='number'>
                  { buttonText }
                </Button>
              </Col>
            }
          </Row>
        </form>
        <div id='preview'>
          <Cards 
            cvc={ cvc || '' }
            name={ name || '' }
            focused={ this.state.focused }
            number={ number ? number.replace(/ /g, '') : '' }
            expiry={ expiry ? expiry.replace(/ |\//g, '') : '' } />
        </div>
      </div>
    );
  }
}

export default CCForm;
