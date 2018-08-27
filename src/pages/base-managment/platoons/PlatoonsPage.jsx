import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Table, Callout, FontAwesome } from 'components/ui';
import { Link } from 'react-router-dom';
import { Button, ButtonGroup } from 'reactstrap';
import { InlineSync } from 'components/ui/loading';
// functions
import { isAdmin } from 'functions/login';
import { getPlatoons } from 'store/platoons/operations';
import { arrayToCSV, setTitle, canDownload } from 'functions/utils';

export class PlatoonsPage extends Component {

  // load the contents if we do not have any
  componentDidMount(){
    setTitle( 'View/Edit Platoons' );
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
      { Header: 'Cell Phone', accessor: 'cell' },
      { Header: 'E-mail Address', accessor: 'email' },
      { Header: 'Soldiers', accessor: 'soldier_count' },
      { Header: 'Staff', accessor: 'staff_count' },
    ];
    if ( isAdmin(login.code) ) {
      columns.push( { Header: 'Base', accessor: 'school_name' } );
    }

    return (
      <div id='PlatoonsPage'>
        <Callout title="Platoons">
          <p><strong>To connect a Staff member to a Platoon go to the edit page by clicking on the Grade, Sub or Teacher.</strong></p>
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

        <Table 
          data={ platoons } 
          columns={ columns } 
          loading={ loading && !platoons.length } 
          pageId='PlatoonsPage' />

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
