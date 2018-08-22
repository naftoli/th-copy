import React, { Component } from 'react';
import { connect } from 'react-redux';
// import PropTypes from 'prop-types';
// components
import { Link } from 'react-router-dom';
import { Button, ButtonGroup } from 'reactstrap';
import { Table, InlineSync, FontAwesome, Number } from 'components/ui';
// functions
import { loginStoreChanged, isAdmin } from 'functions/login';
import { arrayToCSV, setTitle, canDownload } from 'functions/utils';
// state
import { getBases } from 'store/bases/operations';

class BasesPage extends Component {

  static propTypes = {};

  componentDidMount() { 
    setTitle( 'View / Edit Bases' );
    this.loadBases(); 
  }

  componentDidUpdate({ login }) {
    if ( loginStoreChanged( login ) ) this.loadBases();
  }

  loadBases = () => {
    const { login, history, match } = this.props;
    // if we are not an admin, just go straight to our base
    if ( !isAdmin( login.code ) ) { 
      history.replace( `${match.path}/${login.id}` ); 
    }
    this.props.getBases();
  }

  render() {
    const { bases, loading, match } = this.props;

    let columns = [
      { Header: 'Base Number', accessor: 'school_number',
        Cell: props => <Link to={`${match.path}/${props.original.school_id}`}>{props.value}</Link> },
      { Header: 'Base Name', accessor: 'school_name',
        Cell: props => <Link to={`${match.path}/${props.original.school_id}`}>{props.value}</Link> },
      { Header: 'Base City', accessor: 'school_city' },
      { Header: 'Base State', accessor: 'school_state' },
      { Header: 'Base Country', accessor: 'school_country' },
      { Header: 'Soldiers', accessor: 'soldier_count', Cell: props => <Number value={props.value}/> },
    ];

    return (
      <div id='BasesPage'>
      
        <ButtonGroup>
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
        </ButtonGroup>

        <Table 
          data={ bases } 
          columns={ columns } 
          loading={ loading && !bases.length } 
          pageId='BasesPage' />

      </div>
    );
  }
}

const mapStateToProps = ({ bases, login }) => ({
  ...bases,
  login: login.current_login
});

const mapDispatchToProps = { getBases };

export default connect(
  mapStateToProps, mapDispatchToProps
)( BasesPage );
