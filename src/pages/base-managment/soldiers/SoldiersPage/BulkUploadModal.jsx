import React, { Component } from 'react';
// components
import { FileInput } from 'components/inputs'; 
import { Spinner, Callout } from 'components/ui';
import { Modal, ModalHeader, ModalBody, ModalFooter, Button } from 'reactstrap';

export class BulkUploadModal extends Component {

  static defaultProps = {
    isOpen: false, centered: true,
    toggle: () => {}
  }

  state = { loading: false, errors: [] }

  inputRef = React.createRef();

  toggleLoading = ( loading ) => {
    this.setState( { loading: loading } )
  }

  upload = () => {
    const file = this.inputRef.current.files[0];
    if ( !file ) return this.props.upload( false );
    const formData = new FormData();
    formData.append( 'users', file, file.name );
    this.setState( { loading: true } );
    // upload data and handle result
    this.props.upload( formData )
    .then( () => this.setState( { loading: false } ) )
    .catch( error => {
      let errors = error.data;
      if ( !Array.isArray( errors ) ) { errors = [ errors ]; }
      this.setState({ loading: false, errors });
    });
  }

  render() {
    const { isOpen, centered, toggle } = this.props;
    const { loading, errors } = this.state;

    return (
      <Modal isOpen={isOpen} centered={centered} toggle={toggle} id='bulk-upload-modal'>
        <ModalHeader toggle={toggle}>Upload Soldier List</ModalHeader>
        <ModalBody>
          { errors.length === 0 && 
            <Callout color='primary' title='Directions:'>
              <p id='warning'>
                Warning! Do not add any Soldiers that are switching from another Tzivos Hashem base. 
                Please contact <a href='cth@tizvoshashem.org'>Headquarters</a> with a list of names, dob's and base's they came from to be transferred to your base. 
                Leaving them in your spreadsheet will reset them back to a private.
              </p>
              <ol style={{ paddingLeft: '0px' }}>
                <li>Download the <a href='//mashpia.com/students.xls' target='_href'>spreadsheet</a> (Excel/.xls)</li>
                <li>Enter all information into spreadsheet.<br/>
                  <strong>
                    Please Note: You MUST have the First Name, Last Name, 
                    First Name Hebrew, Last name Hebrew, English Date of Birth, Gender, 
                    and Mission Type fields of each student filled out.
                  </strong>
                </li>
                <li>Upload spreadsheet into system using the file input below.</li>
                <li>
                  If there are errors in your spreadsheet they will replace this instructions box. 
                  Please correct these errors and re-upload using the file input below when you are ready.
                </li>
              </ol>
              <p>
                <strong>Please understand that all soldiers in your spreadsheet will be added to the system.
                  <em> We do not, and will not, prevent you from creating duplicate accounts.</em></strong>
              </p>
            </Callout>
          } { errors.length > 0 && !loading &&
            <Callout color='danger' title='Error Details:' icon='fas fa-exclamation-triangle'>
              { errors.map( (error, index) => <p key={index}>{error}</p> ) }
              <p><strong>Please correct the above errors and re-upload your spreadsheet</strong></p>
            </Callout>
          } { loading && <Spinner size='5' /> }
          { !loading && <FileInput inputRef={ this.inputRef }/>}
        </ModalBody>
        { !loading && 
          <ModalFooter>
              <Button color='primary' onClick={this.upload}>Upload Spreadsheet</Button>
          </ModalFooter>
        }
      </Modal>
    );
  }
}

export default BulkUploadModal;
