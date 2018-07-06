import React, { Component } from 'react';
import './styles/FileInput.scss';

export class FileInput extends Component {

  render(){
    return (
      <div className='FileInput'>
        <input type="file" id="file" accept=".xls"/>
        <label htmlFor="file">
          <i className="fas fa-upload"></i>&nbsp;
          <strong>Choose a file</strong>
          {/* <span className="box__dragndrop"> or drag it here</span>. */}
        </label>
      </div>
    )
  }
}

export default FileInput;
