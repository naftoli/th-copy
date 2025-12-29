import React, { useRef } from 'react';
// components
import ReactSelect from 'react-select';
import makeAnimated from 'react-select/lib/animated';
import ReactSelectCreatable from 'react-select/lib/Creatable';

const withDefaultProps = (SelectComponent) => {
  return ({ required, id, value, values, selected, isDisabled, ...props }) => {

    const selectRef = useRef(null);

    const getValue = () => {
      if (value && value.value !== undefined)
        return value.value;

      if (value !== undefined && value !== null)
        return value;

      if (values !== undefined && values !== null)
        return values;

      if (selected !== undefined && selected !== null)
        return selected;

      return ''
    };

    const enableRequired = required && !isDisabled;

    return (
      <div className='Select' style={{ flexGrow: 1 }}>
        <SelectComponent
          openMenuOnFocus
          menuPlacement='auto'
          ref={selectRef}
          components={makeAnimated()}
          classNamePrefix='react-select'
          id={enableRequired ? undefined : id}

          value={value || selected}
          {...props} />

        {enableRequired &&
          <input
            required
            id={id}
            tabIndex={-1}
            autoComplete='off'
            onChange={() => { }}
            value={getValue()}
            style={{
              opacity: 0, width: '100%',
              height: 0, position: 'absolute'
            }}
            onFocus={() =>
              selectRef.current &&
              selectRef.current.focus()} />
        }
      </div>
    );
  }
}

export const Select = withDefaultProps(ReactSelect);
export const Creatable = withDefaultProps(ReactSelectCreatable);