import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import ReactTable from "react-table";
import { Callout, FontAwesome } from 'components/ui';
import { Link } from 'react-router-dom';
import { Button, ButtonGroup } from 'reactstrap';
import { InlineSync } from 'components/ui/loading';
// functions
import is from 'is_js';
import { loginStoreChanged } from 'functions/login';
import { arrayToCSV, setTitle, canDownload } from 'functions/utils';
import { filter, scrollToTop } from 'functions/tables';
import { getPlatoons } from 'store/platoons/operations';
// styles
import './PlatoonsPage.scss';

export class PlatoonsPage extends Component {

  // load the contents if we do not have any
  componentDidMount(){
    setTitle( 'View/Edit Platoons' );
    this.getPlatoons();
  }

  // if the soldier list is emptied while on the page... then refresh it
  componentDidUpdate( { login } ) {
    if ( loginStoreChanged( login ) )
      this.getPlatoons();
  }

  getPlatoons = () => { this.props.getPlatoons(); }

  toCSV = () => {
    const headers = [ 'Grade', 'Subject', 'Teacher', 'Cell Phone', 'E-mail', '# of Soldiers', '# of Staff', 'Base' ];
    const rows = this.props.platoons.map( platoon => [
      platoon.class_grade, platoon.class_sub, platoon.teacher, platoon.cell,
      platoon.email, platoon.soldier_count, platoon.staff_count, platoon.school_name
    ]);
    arrayToCSV( headers, rows, 'platoons' );
  }

  render() {
    const { platoons, loading, login, match } = this.props;

    let columns = [
      { Header: 'Grade', accessor: 'class_grade',
        Cell: props => <Link to={`${match.path}/${props.original.class_id}`}>{props.value}</Link> },
      { Header: 'Sub', accessor: 'class_sub',
        Cell: props => <Link to={`${match.path}/${props.original.class_id}`}>{props.value}</Link> },
      { Header: 'Teacher', accessor: 'teacher', 
        Cell: props => <Link to={`${match.path}/${props.original.class_id}`}>{props.value}</Link> },
      { Header: '# of Soldiers', accessor: 'soldier_count' },
      { Header: '# of Staff', accessor: 'staff_count' },
    ];
    if ( ['HQ', 'CKIDS-ADMIN'].includes(login.code) ) {
      columns.push( { Header: 'Base', accessor: 'school_name' } );
    }

    const onChange = scrollToTop('PlatoonsPage');
    const tableProps = {
      data: platoons, columns,
      className: "-striped -highlight",
      filterable: true, defaultFilterMethod: filter,
      minRows: is.mobile() || is.tablet() ? 10 : 15,
      noDataText: loading ? 'Loading...' : 'No Data',
      onPageChange: onChange, onFilteredChange: onChange,
      defaultPageSize: is.mobile() || is.tablet() ? 50 : 100,
    }

    return (
      <div id='PlatoonsPage'>
        <Callout title="View / Edit Platoons">
          <p>Platoons are the official Tzivos Hashem Lingo for your classes.</p>
          <p><strong>To connect a Staff member to a Platoon go to the edit page by clicking on the Grade, Subject or Teacher.</strong></p>
        </Callout>
        <ButtonGroup style={{ margin: '10px 0px', width: '100%', justifyContent: 'flex-end' }}>
          <Link to={`${match.path}/new`} className="btn btn-primary" role="button">
            <FontAwesome icon='plus' /> Add Platoon
          </Link>
          <Link to={`${match.path}/transition`} className="btn btn-primary" role="button">
            <FontAwesome icon='users' /> Platoon Transition
          </Link>
          <Button color="primary" onClick={ this.getPlatoons }>
            <InlineSync loading={ loading } /> Refresh
          </Button>
          { canDownload( platoons ) &&
            <Button color="primary" onClick={ this.toCSV }>
              <FontAwesome icon='file-download' /> Download Platoons (CSV/Excel)
            </Button>
          }
        </ButtonGroup>
        <ReactTable { ...tableProps } />
      </div>
    )
  }
}

const mapStateToProps = ( { platoons, login } ) => ({
  ...platoons,
  login: login.current_login
})

const mapDispatchToProps = { getPlatoons }

export default connect( mapStateToProps, mapDispatchToProps )( PlatoonsPage );
