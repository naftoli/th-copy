import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Link } from 'react-router-dom';
import { Button, ButtonGroup } from 'reactstrap';
import PrizeModal from './PrizeModal';
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
    cropperModal: { show: false, id: false, src: false },
    prizeModal: { show: false, prize: {} }
  };

  componentDidMount() { 
    setTitle( 'Store Prizes' );
    this.loadPrizes(); 
  }
  // Network
  loadPrizes = () => {
    this.props.getPrizes()
    .catch( e => toast.error( e.message ) );
  }

  // update base logos from master page
  togglePrize = () => this.setState({
    prizeModal: { ...this.state.prizeModal, show: false }
  });
  editPrize = ( prize ) => () => {
    this.setState({
      prizeModal: { show: true, prize }
    })
  }

  toggleCropper = () => this.setState({
    cropperModal: { ...this.state.cropperModal, show: false }
  });
  editPicture = id => ({ target }) => this.setState({ cropperModal: {
    show: true, id, src: target.src }
  });
  
  upload = formData => {
    debugger;
    // return this.props.updatePrize( this.state.modal.id, formData )
    //   .then( this.toggleCropper )
    //   .catch( e => { toast.error( e.message ); });
  };

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
    const { prizeModal, cropperModal } = this.state;
    const { prizes, loading, match, login } = this.props;
    const { editPrize, editPicture, updateToggle } = this;

    let columns = getColumns({
      path: match.path,
      admin: isAdmin( login.code ),
      editPrize, editPicture, updateToggle
    });

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

        <PrizeModal
          prize = { prizeModal.prize }
          toggle={ this.togglePrize }
          isOpen = { prizeModal.show }
          />

        <CropperModal 
          fileName='image'
          src={ cropperModal.src }
          isOpen={ cropperModal.show }
          toggle={ this.toggleCropper }
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
