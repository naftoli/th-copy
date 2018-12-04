import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Button } from 'reactstrap';
import CropperModal from 'components/modals/CropperModal';
import { ButtonBar, Table, InlineSync, FontAwesome } from 'components/ui';
// functions
import getColumns from './includes/columns';
import { isAdmin } from 'functions/login';
import { arrayToCSV, setTitle, canDownload } from 'functions/utils';
// state
import { showError } from 'functions/notifications';
import { getBases, updateBase } from 'store/base/bases/operations';

class BasesPage extends Component {

  state = { 
    modal: { show: false, id: false, src: false } 
  };

  componentDidMount() { 
    setTitle( 'View / Edit Bases' );
    this.loadBases(); 
  }

  loadBases = () => {
    const { login, history, match } = this.props;
    // if we are not an admin, just go straight to our base
    if ( !isAdmin( login.code ) ) { 
      history.replace( `${match.path}/${login.id}` ); 
    }
    this.props.getBases();
  }
  // update base logos from master page
  toggle = () =>
    this.setState({ modal: { ...this.state.modal, show: !this.state.modal.show } });
  // open the modal
  editPicture = id => ({ target }) =>
    this.setState({ modal: { show: true, id, src: target.src } });
  // upload the picture and update the base
  upload = formData => showError(
    this.props.updateBase( this.state.modal.id, formData )
  );
  // update the the base in the background
  updateToggle = ( key, id ) => event => showError(
    this.props.updateBase( id, { [key]: event.target.checked ? 1 : 0 } )
  );

  toCSV = () => {
    const headers = [
      'Base Number', 'Base Name', 'Base City', 'Base State', 'Base Country', 'Soldiers', 
    ];
    // get the data
    const rows = this.props.bases.map( base => [
      base.school_number, base.school_name, base.school_city, base.school_state, base.school_country, base.soldier_count
    ]);
    arrayToCSV( headers, rows, 'bases' );
  }

  render() {
    const { bases, loading, match } = this.props;
    const { modal } = this.state;

    let columns = getColumns( match.path, this.editPicture, this.updateToggle );

    return (
      <div id='BasesPage'>
      
        <ButtonBar>
          <Button color='primary' onClick={ this.loadBases }>
            <InlineSync loading={ loading } /> Refresh
          </Button>
          { canDownload( bases ) &&
            <Button color='primary' onClick={ this.toCSV }>
              <FontAwesome icon='file-download' /> Download Bases (CSV/Excel)
            </Button>
          }
        </ButtonBar>

        <Table 
          data={ bases } 
          columns={ columns } 
          loading={ loading && !bases.length } 
          pageId='BasesPage' />

        <CropperModal 
          fileName='logo' viewMode={0}
          isOpen={ modal.show } src={ modal.src } 
          toggle={ this.toggle } uploadImage={ this.upload } />

      </div>
    );
  }
}

const mapStateToProps = ({ base, login }) => ({
  ...base.bases,
  login: login.current_login
});

const mapDispatchToProps = { getBases, updateBase };

export default connect(
  mapStateToProps, mapDispatchToProps
)( BasesPage );
