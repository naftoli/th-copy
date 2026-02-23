import React, { useState, useRef } from 'react';
import { InputGroup, InputGroupText, Button } from 'reactstrap';
import { FontAwesome } from 'components/ui';
import { lock } from 'img/icons';
import './styles/Password.scss';

export const Password = ({
  name, showIcon = false, required = false, autoComplete,
  size, placeholder, onChange = () => { }, disabled,
  value = '', showToggle = true, tabToggle = false, defaultOpen = false
}) => {

  const [showPassword, setShowPassword] = useState(defaultOpen);
  const passwordRef = useRef(null);

  const togglePassword = () => {
    setShowPassword(!showPassword);
    // use the dom to avoid a re-render issues in previous version, we keep this assumption
    if (passwordRef.current) {
      // In functional component refactor, updating state triggers re-render.
      // The original code was: setState, then callback -> manual DOM update.
      // Here, state update will re-render component.
      // If we want to maintain focus, we can do it in useEffect.
      passwordRef.current.focus();
    }
  }

  // Effect to handle focus if needed after toggle?
  // Actually, standard React behavior is fine unless there's some specific reason.
  // The original code said "if you render based on state it keeps re-rendering in the dom and will not work".
  // This might refer to older React or specific browser behavior with password fields.
  // Using standard state often works fine. But let's replicate the "type" switching.

  const inputType = showPassword ? 'text' : 'password';

  return (
    <InputGroup size={size} className='Password'>
      {showIcon &&
        <div className="input-group-prepend">
          <InputGroupText>
            <img src={lock} alt='lock' />
          </InputGroupText>
        </div>
      }

      <input
        value={value}
        type={inputType}
        disabled={disabled}
        required={required}
        onChange={onChange}
        className='form-control'
        ref={passwordRef}
        name={name || 'password'}
        autoComplete={autoComplete || 'current-password'}
        placeholder={placeholder || 'Password'} />

      {showToggle &&
        <div className="input-group-append">
          <Button outline
            color='primary'
            disabled={disabled}
            className='toggle-password'
            onClick={togglePassword}
            tabIndex={tabToggle ? 0 : -1}>

            <FontAwesome icon={showPassword ? 'eye' : 'eye-slash'} />

          </Button>
        </div>
      }
    </InputGroup>
  );
}
