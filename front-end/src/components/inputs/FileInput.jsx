import React, { useState } from 'react';
import { FontAwesome } from 'components/ui';
import './styles/FileInput.scss';

export const FileInput = ({ inputRef, className, ...props }) => {

  const [text, setText] = useState('Choose a file');

  const fileSelected = () => {
    var files = inputRef.current.files;

    setText(files.length > 0 ? files[0].name : 'Choose a file');

    // Add onChange handler if passed in props (though current code doesn't explicitly use it for anything other than state update)
    if (props.onChange) {
      props.onChange();
    }
  }

  // The original component had onChange={this.fileSelected} on input.
  // And no explicit prop handling for onChange passed from parent, except via ...props spread if it was there?
  // But standard inputs usually have onChange.
  // The original code:
  // <input ... onChange={ this.fileSelected }/>
  // So it completely overrode any onChange prop passed in if spread was used?
  // Wait, `this.props` only had `inputRef` and `className` destructured in render.
  // Assuming no other props are critical or passed directly to input via spread in original render.
  // Actually original render didn't spread props to input. It only used inputRef and className.

  return (
    <div className='FileInput'>
      <input type="file" id="file" accept=".xls" ref={inputRef}
        className={className} onChange={fileSelected} />
      <label htmlFor="file">
        <FontAwesome icon='upload' />&nbsp;
        <strong>{text}</strong>
        {/* <span className="box__dragndrop"> or drag it here</span>. */}
      </label>
    </div>
  )
}
