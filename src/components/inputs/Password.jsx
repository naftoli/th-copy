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
    showIcon: false,
    showToggle: true,
    tabToggle: false,
  }

  state = {
    show_password: false
  }

  passwordRef = React.createRef();

  togglePassword = () => {
    this.setState({
      show_password: !this.state.show_password
    }, () => {
      // update the feild without re-rendering the component ( If we have access to the dom )
      const password = this.passwordRef.current;
      if ( password ) {
        password.type = this.state.show_password ? 'text' : 'password';
        password.focus();
      };
    });
  }

  render() {
    const { size, required, value, onChange, showIcon, showToggle, tabToggle } = this.props;

    return (
      <InputGroup size={ size } className='Password'>
        { showIcon && 
          <InputGroupAddon addonType="prepend">
            <img src={lock} alt='lock' width='26' height='26'/>
          </InputGroupAddon>
        }
        <input className='form-control' placeholder='Password' type='password' required={required}
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