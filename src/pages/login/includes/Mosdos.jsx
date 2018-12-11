import React from 'react';
import { Checkbox } from 'components/inputs';

class Mosdos extends React.Component {

  onChange = id => event =>
    this.props.onChange( id );

  render() {
    let { mosdos } = this.props;
    // render nothing if mosdos is not an array
    if ( !Array.isArray( mosdos ) )
      return null;

    return mosdos.map( ( mosad, index ) => (
      <div className='Mosad' key={ index }>
        <Checkbox
          checked={ mosad.selected }
          onChange={ this.onChange( mosad.id ) }>
        { mosad.name }
        </Checkbox>
      </div>
    ));
  }
}

export default Mosdos;