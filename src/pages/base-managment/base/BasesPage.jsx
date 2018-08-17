import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Link } from 'react-router-dom';
import { Table, Callout } from 'components/ui';
// functions
import { loginStoreChanged, isAdmin } from 'functions/login';
// state
import { getBases } from 'store/bases/operations';

class BasesPage extends Component {

  static propTypes = {};

  componentDidMount() { this.loadBases(); }

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
      { Header: 'Base #', accessor: 'school_number',
        Cell: props => <Link to={`${match.path}/${props.original.school_id}`}>{props.value}</Link> },
      { Header: 'Base Name', accessor: 'school_name',
        Cell: props => <Link to={`${match.path}/${props.original.school_id}`}>{props.value}</Link> },
      { Header: 'Base City', accessor: 'school_city' },
      { Header: 'Base State', accessor: 'school_state' },
      { Header: 'Base Country', accessor: 'school_country' },
      { Header: 'Soldiers', accessor: 'soldier_count' },
    ];

    return (
      <div id='BasesPage'>
        <Callout title='View Soldiers'>
          Click a Soldier's name or serial number to view and edit their account.<br/>
          Click on a Soldier's profile picture to edit or replace it.
        </Callout>

        <Table 
          columns={columns} 
          data={bases} 
          loading={loading} 
          pageId='BasesPage' />
          
        <pre>
          { JSON.stringify( this.props, null, 2 ) }
        </pre>
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
