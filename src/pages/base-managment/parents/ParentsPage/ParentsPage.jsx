import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import ReactTable from "react-table";
import { Link } from 'react-router-dom';
import { Callout } from 'components/ui';
import { Button, ButtonGroup } from 'reactstrap';
import { InlineSync } from 'components/ui/loading';
// functions
import is from 'is_js';
import { loginStoreChanged } from 'functions/login';
import { arrayToCSV, setTitle } from 'functions/utils';
import { defaultTableProps } from 'functions/tables';
// state
import { getParents } from 'store/parents/operations';
// styles
import './ParentsPage.scss';

class ParentsPage extends Component {

  // load the contents if we do not have any
  componentDidMount(){
    setTitle( 'View/Edit Parents' );
    this.getParents();
  }

  // if the soldier list is emptied while on the page... then refresh it
  componentDidUpdate( { login } ) {
    if ( loginStoreChanged( login ) )
      this.getParents();
  }

  getParents = () => { this.props.getParents() }

  toCSV = () => {
    // convert to escaped, multiline string
    const getChildrenString = ( parent ) => {
      return JSON.stringify( parent.children.map( 
        child => `${child.first} ${child.last}` ).join('; ')
      );
    }
    // CSV headers
    const headers = [ 
      'First', 'Last', 'Username', 'Cell Phone', 'E-mail', 
      'Address', 'City', 'State', 'Zip', 'Country', 'Children'
    ];
    // generate rows
    const rows = this.props.parents.map( parent => [
      parent.first, parent.last, parent.username, parent.cell,
      parent.email, parent.address, parent.city, parent.state,
      parent.zip, parent.country, getChildrenString( parent )
    ]);
    arrayToCSV( headers, rows, 'parents' );
  }

  render() {
    const { parents, loading, login, match } = this.props;

    let columns = [
      { Header: 'First Name', accessor: 'first',
        Cell: props => <Link to={`${match.path}/${props.original.admin_id}`}>{props.value}</Link> },
      { Header: 'Last Name', accessor: 'last',
        Cell: props => <Link to={`${match.path}/${props.original.admin_id}`}>{props.value}</Link> },
      { Header: 'Cell Phone', accessor: 'cell' },
      { Header: 'E-mail Address', accessor: 'email' },
      { Header: 'Children', id: 'children', accessor: parent => parent.children.length },
    ];

    let tableProps  = defaultTableProps( 'ParentsPage', loading );
    tableProps = { 
      ...tableProps, data: parents, columns,
      defaultSorted: [
        { id: "first", desc: false }, 
        { id: "last", desc: false }
      ]
    }

    return (
      <div id='ParentsPage'>
        <Callout title="View / Edit Parents">
          <p>
            Parents are any account with direct access to a soldier via the Parent Portal.
            Please note that for security reasons you cannot edit their accounts or view their passwords once they are created.
          </p>
          <p><strong>To add / remove children please select the First or Last name and have their Serial Number ready.</strong></p>
        </Callout>
        <ButtonGroup style={{ margin: '10px 0px', width: '100%', justifyContent: 'flex-end' }}>
          <Link to={`${match.path}/new`} className="btn btn-primary" role="button">
            <i className="fas fa-plus" /> Add Parent
          </Link>
          <Button color="primary" onClick={ this.getParents }>
            <InlineSync loading={ loading } /> Refresh
          </Button>
          { is.not.edge() && is.not.ie() && is.not.ios() && parents.length > 0 &&
            <Button color="primary" onClick={ this.toCSV }>
              <i className="fas fa-file-download" /> Save Parent List
            </Button>
          }
        </ButtonGroup>
        <ReactTable { ...tableProps } />
      </div>
    )
  }
}

const mapStateToProps = ( { parents, login } ) => ({
  ...parents,
  login: login.current_login
})

const mapDispatchToProps = { getParents }

export default connect( mapStateToProps, mapDispatchToProps )( ParentsPage );
