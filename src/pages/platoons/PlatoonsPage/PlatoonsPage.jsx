import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import ReactTable from "react-table";
import { Callout } from 'components/ui';
import { Link } from 'react-router-dom';
import { Button, ButtonGroup } from 'reactstrap';
// functions
import is from 'is_js';
import { loginChanged } from 'functions/login';
import { arrayToCSV, setTitle } from 'functions/utils';
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
    if ( loginChanged( this.props.login, login ) )
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
    const { platoons, loading, login } = this.props;

    let columns = [
      { Header: 'Grade', accessor: 'class_grade',
        Cell: props => <Link to={`/platoons/${props.original.class_id}`}>{props.value}</Link> },
      { Header: 'Subject', id: 'subject', accessor: platoon => platoon.class_sub || ' - ',
        Cell: props => <Link to={`/platoons/${props.original.class_id}`}>{props.value}</Link> },
      { Header: 'Teacher', accessor: 'teacher', 
        Cell: props => <Link to={`/platoons/${props.original.class_id}`}>{props.value}</Link> },
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
      onPageChange: onChange, onFilteredChange: onChange,
      minRows: 15, defaultPageSize: is.mobile() || is.tablet() ? 50 : 100,
    }

    return (
      <div id='PlatoonsPage'>
        <Callout title="View / Edit Platoons">
          <p>Platoons are the official Tzivos Hashem Lingo for your classes.</p>
          <p><strong>To connect a Staff member to a Platoon go to the edit page by clicking on the Grade, Subject or Teacher.</strong></p>
        </Callout>
        <ButtonGroup style={{ margin: '10px 0px', width: '100%', justifyContent: 'flex-end' }}>
          <Link to={`/platoons/new`} className="btn btn-primary" role="button">
          <i className="fas fa-plus" /> Add Platoon
          </Link>
          <Button color="primary" onClick={ this.getPlatoons }>
            <i className={`fas fa-redo-alt ${ !loading || 'fa-spin' }`}></i> Refresh
          </Button>
          { is.not.edge() && is.not.ie() && is.not.ios() &&
            <Button color="primary" onClick={ this.toCSV }>
              <i className="fas fa-file-download" /> Save Platoon List
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
