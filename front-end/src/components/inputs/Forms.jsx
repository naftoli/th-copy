import React, { useState, useRef, useEffect } from 'react';
import classnames from 'classnames';

export const Form = ({
  children, onValidChange, validateAfterSubmit = false, ...props
}) => {

  const [submitted, setSubmitted] = useState(false);
  const formRef = useRef(null);

  const updateValid = () => {
    if (
      (!formRef.current || !onValidChange) ||
      (validateAfterSubmit && !submitted)
    ) return false;

    return onValidChange(formRef.current.checkValidity());
  }

  // componentDidMount and componentDidUpdate logic
  useEffect(() => {
    updateValid();
  }); // Run on every render to mimic componentDidUpdate + Mount

  const handleSubmit = e => {
    setSubmitted(true);
    if (props.onSubmit) {
      props.onSubmit(e);
    }
  }

  return (
    <form
      className={classnames({ 'was-validated': submitted }, props.className)}
      {...props}
      ref={formRef}
      onSubmit={handleSubmit}>
      {children}
    </form>
  );
}

export const Label = ({ className, children, ...props }) => {
  // combine full-width with the existing classnames
  const classNames = classnames({
    'full-width': true,
    [className]: !!className
  });

  return (
    <label className={classNames} {...props}>
      {children}
    </label>
  );
}