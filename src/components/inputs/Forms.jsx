import React, { Component } from 'react';

export class Form extends Component {

  formRef = React.createRef();

  componentDidMount() { this.updateValid() }
  componentDidUpdate() { this.updateValid() }

  updateValid = () => {
    if ( this.formRef.current )
      this.props.onValidChange( this.formRef.current.checkValidity() );
  }

  render() {
    const { children, ...props } = this.props;
    return (
      <form {...props} ref={ this.formRef }>
        { children }
      </form>
    );
  }
}