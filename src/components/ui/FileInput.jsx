import React, { Component } from 'react';
import './styles/FileInput.scss';

export class FileInput extends Component {

  state = {
    text: 'Choose a file'
  }

  fileSelected = () => {
    this.setState({
      text: this.props.inputRef.current.files[0].name
    });
  }

  render(){
    const { inputRef, className } = this.props;
    return (
      <div className='FileInput'>
        <input type="file" id="file" accept=".xls" ref={ inputRef } 
          className={ className } onChange={ this.fileSelected }/>
        <label htmlFor="file">
          <i className="fas fa-upload"></i>&nbsp;
          <strong>{this.state.text}</strong>
          {/* <span className="box__dragndrop"> or drag it here</span>. */}
        </label>
      </div>
    )
  }
}

export default FileInput;
