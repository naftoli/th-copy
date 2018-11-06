import React, { Component } from 'react';
import classnames from 'classnames';

export class Form extends Component {

  formRef = React.createRef();

  componentDidMount() { this.updateValid() }
  componentDidUpdate() { this.updateValid() }

  updateValid = () => {
    if ( this.formRef.current && this.props.onValidChange )
      this.props.onValidChange( this.formRef.current.checkValidity() );
  }

  render() {
    const { children, onValidChange, ...props } = this.props;
    return (
      <form {...props} ref={ this.formRef }>
        { children }
      </form>
    );
  }
}

export const Label = ({ className, children, ...props }) => {
  // combine full-width with the existing classnames
  const classNames = classnames({
    'full-width': true,
    [ className ]: !!className
  });
  
  return (
    <label className={ classNames } { ...props }>
      { children }
    </label>
  );
}