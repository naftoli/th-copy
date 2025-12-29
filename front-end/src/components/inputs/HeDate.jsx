import React from "react";
import { ReactJewishDatePicker } from "react-jewish-datepicker";

export const HeDate = ({ onChange }) => {
  return (
    <ReactJewishDatePicker
      value={ new Date() }
      isHebrew
      isRange
      onClick={(start, end) => onChange(start, end)}
    />
  );
}