import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Button } from 'reactstrap';
import { Link } from 'react-router-dom';
import { InlineSync } from 'components/ui/loading';
import { ButtonBar, Table, Callout, FontAwesome } from 'components/ui';
// modals
import NewParentModal from './NewParentModal';
// functions
import { toast } from 'react-toastify';
import { arrayToCSV, setTitle, canDownload } from 'functions/utils';
// state
import { getParents } from 'store/parents/operations';

class ParentsPage extends Component {

  state = { showModal: false }

  // load the contents if we do not have any
  componentDidMount(){
    setTitle( 'Parents' );
    if ( this.props.parents.length === 0 ) {
      this.getParents();
    }
  }

  getParents = () => {
    this.props.getParents()
    .catch( error => toast.error( error.message ) )
  }
  toggle = () => this.setState({ showModal: !this.state.showModal });

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
    const { parents, loading, match } = this.props;

    let columns = [
      { Header: 'First Name', accessor: 'first',
        Cell: props => <Link to={`${match.path}/${props.original.admin_id}`}>{props.value}</Link> },
      { Header: 'Last Name', accessor: 'last',
        Cell: props => <Link to={`${match.path}/${props.original.admin_id}`}>{props.value}</Link> },
      { Header: 'Username', accessor: 'username',
        Cell: props => <Link to={`${match.path}/${props.original.admin_id}`}>{props.value}</Link> },
      { Header: 'Cell Phone', accessor: 'cell' },
      { Header: 'E-mail Address', accessor: 'email' },
      { Header: 'Children', id: 'children', accessor: parent => parent.children.length },
    ];

    return (
      <div id='ParentsPage'>
        <Callout title='View Parent Accounts'>
          <p>
            Parents are any account with direct access to a soldier via the Parent Portal.
            Please note that for security reasons you cannot edit their accounts or view their passwords once they are created.
          </p>
          <p><strong>To add / remove children please select the First or Last name and have their Serial Number ready.</strong></p>
        </Callout>
        
        <ButtonBar style={{ margin: '10px 0px', width: '100%', justifyContent: 'flex-end' }}>
          <Button onClick={this.toggle} className='btn btn-primary'>
            <FontAwesome icon='plus' /> Create Parent Account
          </Button>
          <Button color='primary' onClick={ this.getParents }>
            <InlineSync loading={ loading } /> Refresh
          </Button>
          { canDownload( parents ) &&
            <Button color='primary' onClick={ this.toCSV }>
              <FontAwesome icon='file-download' /> Download Parents (CSV/Excel)
            </Button>
          }
        </ButtonBar>

        <Table 
          data={ parents }
          columns={ columns }
          loading={ loading && !parents.length }
          pageId='ParentsPage'
          defaultSorted={[ { id: "first", desc: false }, { id: "last", desc: false } ]}
          />

        <NewParentModal 
          isOpen={ this.state.showModal } 
          toggle={ this.toggle } 
          />
      </div>
    )
  }
}

const mapStateToProps = ( { parents, login } ) => ({
  parents: parents.parents,
  loading: parents.loading,
  login: login.current_login
})

const mapDispatchToProps = { getParents }

export default connect( mapStateToProps, mapDispatchToProps )( ParentsPage );
