import React, { Component } from 'react';
import classnames from 'classnames';

export class Form extends Component {

  static defaultProps = {
    validateAfterSubmit: false
  }

  state = { submitted: false };

  formRef = React.createRef();

  componentDidMount() { this.updateValid() }
  componentDidUpdate() { this.updateValid() }

  updateValid = () => {
    if ( // make sure we have the form and a callback
      ( !this.formRef.current || !this.props.onValidChange ) ||
      // if validateAfterSubmit is true then we will not call the validate function until the form is submitted once.
      ( this.props.validateAfterSubmit && !this.state.submitted )
    ) return false;
    // check the validity of the form and call the callback
    return this.props.onValidChange( this.formRef.current.checkValidity() );
  }

  onSubmit = e => {
    this.setState({ submitted: true })
    this.props.onSubmit( e );
  }

  render() {
    const { children, onValidChange, validateAfterSubmit, ...props } = this.props;
    return (
      <form
          {...props}
          ref={ this.formRef }
          onSubmit={ this.onSubmit }>
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