import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Link } from 'react-router-dom';
import { Button, ButtonGroup } from 'reactstrap';
import CropperModal from 'components/modals/CropperModal';
import { Table, InlineSync, FontAwesome } from 'components/ui';
// functions
import { toast } from 'react-toastify';
import { getColumns } from './include/columns';
import { isAdmin } from 'functions/login';
import { arrayToCSV, setTitle, canDownload } from 'functions/utils';
// state
import { 
  getPrizes, updatePrize 
} from 'store/rewards/prizes/operations';


class PrizesPage extends Component {

  state = { 
    modal: { show: false, id: false, src: false } 
  };

  componentDidMount() { 
    setTitle( 'Store Prizes' );
    this.loadPrizes(); 
  }
  // Network
  loadPrizes = () => { this.props.getPrizes(); }

  // update base logos from master page
  toggle = () => this.setState({ modal: { ...this.state.modal, show: false } });
  editPicture = id => ({ target }) => this.setState({ modal: { show: true, id, src: target.src } });
  upload = formData => { 
    return this.props.updatePrize( this.state.modal.id, formData )
      .then( this.toggle )
      .catch( e => { toast.error( e.message ); });
  };
  // upload = formData => this.props.updateBase( this.state.modal.id, formData );

  updateToggle = ( key, id ) => e => {
    return this.props.updatePrize( id, { [key]: e.target.checked ? 1 : 0 } )
    .catch( e => toast.error( e.message ) );
  }

  toCSV = () => {
    const headers = [
      'Prize Name', 'Miles', 'In Stock', 'Active',
      'One Per Soldier', 'Last Updated', 'Base Number', 'Base'
    ];
    const rows = this.props.prizes.map( prize => [
      prize.prize_name, prize.points, prize.prize_count, 
      prize.is_active ? 'Yes' : 'No', prize.one_per_user ? 'Yes' : 'No', 
      prize.modified, prize.school.school_number, prize.school.school_name
    ]);
    arrayToCSV( headers, rows, 'store_prizes' );
  }

  render() {
    const { prizes, loading, match, login } = this.props;
    const { modal } = this.state;

    let columns = getColumns(
      this.editPicture, match.path, 
      this.updateToggle, isAdmin( login.code )
    );

    if ( isAdmin( login.code ) )
      columns.push(
        { Header: 'Base', id: 'base', accessor: prize => prize.school.school_name,
          Cell: props => <Link to={`/bm/base/${props.original.school.school_id}`}>{props.value}</Link>,
        }
      );

    return (
      <div id='PrizesPage'>
        <ButtonGroup>
          <Button className='btn btn-primary'>
            <FontAwesome icon='plus' /> Create Prize
          </Button>
          <Button color='primary' onClick={ this.loadPrizes }>
            <InlineSync loading={ loading } /> Refresh
          </Button>
          { canDownload( prizes ) &&
            <Button color='primary' onClick={ this.toCSV }>
              <FontAwesome icon='file-download' /> Download Prizes (CSV/Excel)
            </Button>
          }
        </ButtonGroup>

        <Table 
          data={ prizes } 
          columns={ columns } 
          loading={ loading && !prizes.length } 
          pageId='PrizesPage' />

        <CropperModal 
          fileName='image' isOpen={ modal.show }
          src={ modal.src } toggle={ this.toggle }
          uploadImage={ this.upload } />
      </div>
    );
  }
}

const mapStateToProps = ({ rewards, login }) => {
  const { prizes } = rewards;
  return {
    ...prizes,
    login: login.current_login
  }
};

const mapDispatchToProps = {
  getPrizes, updatePrize
};

export default connect( mapStateToProps, mapDispatchToProps )( PrizesPage );
