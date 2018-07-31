// find an option in an array of options by value
export const findOption = ( options, value ) => {
  return options.find( option => option.value === value )
};

// function to get the mission_type_options variable;
export const missionTypeOptions = ( gender ) => {
  const offset = gender === 'F' ? 1 : 0;
  let mission_type_options = [
    { value:  2 + offset, label: 'Chabad' },
    { value: 12 + offset, label: 'Frum' },
    { value: 22 + offset, label: 'C-Kids' }
  ];
  return mission_type_options;
}