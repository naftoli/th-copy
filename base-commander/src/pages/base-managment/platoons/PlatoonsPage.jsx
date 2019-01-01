import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Button } from 'reactstrap';
import { Link } from 'react-router-dom';
import NewPlatoonModal from './newPlatoonModal';
import { 
  InlineSync, ButtonBar, Table, Callout, FontAwesome, NumberDisplay
} from 'components/ui';
// functions
import { isAdmin } from 'functions/login';
import { setTitle, canDownload } from 'functions/utils';
import { getPlatoons, createPlatoon } from 'store/base/platoons/operations';
// csv react component
import { CSVLink } from "react-csv";
import { dataToCSV } from 'functions/utils/csv';

export class PlatoonsPage extends Component {

  state = { showModal: false }

  // load the contents if we do not have any
  componentDidMount(){
    setTitle( 'View/Edit Platoons' );
    this.getPlatoons();
  }

  getPlatoons = () => { this.props.getPlatoons(); }
  toggle = () => this.setState({ showModal: !this.state.showModal });

  toCSV = () => {
    const headers = [ 'Grade', 'Subject', 'Teacher', 'Cell Phone', 'E-mail', '# of Soldiers', '# of Staff', 'Base' ];
    const rows = this.props.platoons.map( platoon => [
      platoon.class_grade, platoon.class_sub, platoon.teacher, platoon.cell,
      platoon.email, platoon.soldier_count, platoon.staff_count, platoon.school_name
    ]);
    //arrayToCSV( headers, rows, 'platoons' );
    return dataToCSV( headers, rows );
  }

  render() {
    const { platoons, loading, login, match } = this.props;
    const { showModal } = this.state;

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
      { Header: 'Miles Balance', accessor: 'miles_balance',
       Cell: props => <NumberDisplay value={props.value}/> },
    ];
    if ( isAdmin(login.code) ) {
      columns.push( { Header: 'Base', accessor: 'school_name' } );
    }

    return (
      <div id='PlatoonsPage'>
        <Callout title="Platoons">
          <p><strong>To connect a Staff member to a Platoon go to the edit page by clicking on the Grade, Sub or Teacher.</strong></p>
        </Callout>
        <ButtonBar style={{ margin: '10px 0px', width: '100%', justifyContent: 'flex-end' }}>
          <Button color="primary" onClick={ this.toggle }>
            <FontAwesome icon='plus' /> Add Platoon
          </Button>
          <Link to={`${match.path}/transition`} className="btn btn-primary" role="button">
            <FontAwesome icon='users' /> Platoon Transition
          </Link>
          <Button color="primary" onClick={ this.getPlatoons }>
            <InlineSync loading={ loading } /> Refresh
          </Button>
          { canDownload( platoons ) &&
            <CSVLink
              data = { this.toCSV() }
              filename = { "platoons.csv" }
              target = "_blank"
            >
              <Button color='primary'>
                <FontAwesome icon='file-download' /> Download Platoons (CSV/Excel)              
              </Button>
            </CSVLink>
          }
        </ButtonBar>

        <Table 
          data={ platoons } 
          columns={ columns } 
          loading={ loading && !platoons.length } 
          pageId='PlatoonsPage' />

        <NewPlatoonModal
          login={ login }
          isOpen={ showModal }
          toggle={ this.toggle }
          refresh={ this.getPlatoons }
          onSubmit={ this.props.createPlatoon } />

      </div>
    )
  }
}

const mapStateToProps = ( { base, login } ) => ({
  ...base.platoons,
  login: login.current_login
})

const mapDispatchToProps = {
  getPlatoons, createPlatoon
}

export default connect( mapStateToProps, mapDispatchToProps )( PlatoonsPage );
