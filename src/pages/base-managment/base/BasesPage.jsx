import React, { Component } from 'react';
import { connect } from 'react-redux';
// import PropTypes from 'prop-types';
// components
import { Button } from 'reactstrap';
import { Link } from 'react-router-dom';
import CropperModal from 'components/modals/CropperModal';
import { 
  ButtonBar, Table, InlineSync, FontAwesome, Number, ProfilePicture
} from 'components/ui';
// functions
import { isAdmin } from 'functions/login';
import { arrayToCSV, setTitle, canDownload } from 'functions/utils';
// state
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
  toggle = () => this.setState({ modal: { ...this.state.modal, show: !this.state.modal.show } });
  editPicture = id => ({ target }) => this.setState({ modal: { show: true, id, src: target.src } });
  upload = formData => this.props.updateBase( this.state.modal.id, formData );

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

    let columns = [
      {
        Header: 'Logo',  accessor: 'logo',
        Cell: props => <ProfilePicture src={ `/schoolLogos/${props.value}` } className='inline-profile' 
                          onClick={ this.editPicture( props.original.school_id ) }/>,
        className: 'profile-picture', width: 85, sortable: false, filterable: false
      },
      { Header: 'Base Number', accessor: 'school_number',
        Cell: props => <Link to={`${match.path}/${props.original.school_id}`}>{props.value}</Link> },
      { Header: 'Base Name', accessor: 'school_name',
        Cell: props => <Link to={`${match.path}/${props.original.school_id}`}>{props.value}</Link> },
      { Header: 'Base City', accessor: 'school_city' },
      { Header: 'Base State', accessor: 'school_state' },
      { Header: 'Base Country', accessor: 'school_country' },
      { Header: 'CTH', id: 'chayolei', accessor: base => base.chayolei ? 'Yes' : 'No' },
      { Header: 'Chidon', id: 'chidon', accessor: base => base.chidon ? 'Yes' : 'No' },
      { Header: 'WWTC', id: 'tehillim', accessor: base => base.tehillim ? 'Yes' : 'No' },
      { Header: 'Tanya', id: 'tanya', accessor: base => base.tanya ? 'Yes' : 'No' },
      { Header: 'Soldiers', accessor: 'soldier_count', Cell: props => <Number value={props.value}/> },
    ];

    return (
      <div id='BasesPage'>
      
        <ButtonBar>
          {/* <Button onClick={this.toggle} className='btn btn-primary'>
            <FontAwesome icon='plus' /> Create Base
          </Button> */}
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
