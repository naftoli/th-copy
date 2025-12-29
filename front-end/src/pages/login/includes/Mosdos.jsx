import React from 'react';
import { Checkbox } from 'components/inputs';

const Mosdos = ({ mosdos, onChange: onChangeProp }) => {

  const onChange = id => event =>
    onChangeProp(id);

  // render nothing if mosdos is not an array
  if (!Array.isArray(mosdos))
    return null;

  return mosdos.map((mosad, index) => (
    <div className='Mosad' key={index}>
      <Checkbox
        checked={mosad.selected}
        onChange={onChange(mosad.id)}>
        {mosad.name}
      </Checkbox>
    </div>
  ));
}

export default Mosdos;