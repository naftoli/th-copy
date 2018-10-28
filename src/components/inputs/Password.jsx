import React, { Component } from 'react';
import { InputGroup, InputGroupAddon, Button } from 'reactstrap';
import { FontAwesome } from 'components/ui';
import { lock } from 'img/icons';
import './styles/Password.scss';

export class Password extends Component {

  static defaultProps = {
    required: false,
    value: '',
    onChange: () => {},
    showIcon: false, // are we showing an icon
    showToggle: true, // do we have a toggle
    tabToggle: false, // can they tab to the toggle
    defaultOpen: false, // can we see the password by default
  }
  
  state = {
    show_password: this.props.defaultOpen
  }

  // dom manimpulation works, if you render based on state it keeps re-rendering in the dom and will not work
  passwordRef = React.createRef();

  togglePassword = () => {
    this.setState({
      show_password: !this.state.show_password
    }, () => {
      const password = this.passwordRef.current;
      if ( password ) {
        password.type = this.state.show_password ? 'text' : 'password';
        this.props.tabToggle || password.focus();
      };
    });
  }

  render() {
    const { 
      size, required, value, onChange, 
      showIcon, showToggle, tabToggle, defaultOpen
    } = this.props;
    const inputType = defaultOpen ? 'text' : 'password';

    return (
      <InputGroup size={ size } className='Password'>
        { showIcon && 
          <InputGroupAddon addonType="prepend">
            <img src={lock} alt='lock' width='26' height='26'/>
          </InputGroupAddon>
        }
        <input className='form-control' placeholder='Password' type={ inputType } required={ required }
          onChange={ onChange } value={ value } name='password' ref={ this.passwordRef } />
        { showToggle &&
          <InputGroupAddon addonType="append">
            <Button color='primary' onClick={ this.togglePassword } id='toggle-password' 
              tabIndex={ tabToggle ? 0 : -1 } outline >
              <FontAwesome icon={ this.state.show_password ? 'eye' : 'eye-slash' } />
            </Button>
          </InputGroupAddon>
        }
      </InputGroup>
    );
  }
}